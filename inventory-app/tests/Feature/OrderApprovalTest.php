<?php

namespace Tests\Feature;

use App\Models\Enums\Status;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class OrderApprovalTest extends TestCase
{
    use RefreshDatabase;

    private function authHeaderFor(User $user): array
    {
        $token = JWTAuth::fromUser($user);

        return ['Authorization' => 'Bearer ' . $token];
    }

    public function test_aprovar_pedido_com_estoque_suficiente_desconta_estoque_e_atualiza_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::factory()->create(['stock_quantity' => 10]);
        $order = Order::factory()->create(['status' => Status::AGUARDANDO]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $response = $this
            ->withHeaders($this->authHeaderFor($admin))
            ->postJson("/api/orders/{$order->id}/approve");

        $response->assertOk();
        $this->assertSame(7, $product->fresh()->stock_quantity);
        $this->assertSame(Status::APROVADO, $order->fresh()->status);
    }

    public function test_aprovar_falha_com_422_quando_estoque_insuficiente_e_nao_altera_nada(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::factory()->create(['stock_quantity' => 1]);
        $order = Order::factory()->create(['status' => Status::AGUARDANDO]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 5,
        ]);

        $response = $this
            ->withHeaders($this->authHeaderFor($admin))
            ->postJson("/api/orders/{$order->id}/approve");

        $response->assertStatus(422);
        $this->assertSame(1, $product->fresh()->stock_quantity, 'Rollback: estoque não pode mudar.');
        $this->assertSame(Status::AGUARDANDO, $order->fresh()->status, 'Pedido deve continuar aguardando.');
    }

    public function test_descartar_pedido_muda_status_sem_alterar_estoque(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::factory()->create(['stock_quantity' => 5]);
        $order = Order::factory()->create(['status' => Status::AGUARDANDO]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $response = $this
            ->withHeaders($this->authHeaderFor($admin))
            ->postJson("/api/orders/{$order->id}/discard");

        $response->assertOk();
        $this->assertSame(5, $product->fresh()->stock_quantity, 'Descarte não toca no estoque.');
        $this->assertSame(Status::DESCARTADO, $order->fresh()->status);
    }

    /**
     * Requisito: dois admins aprovando pedidos do mesmo produto
     * simultaneamente NUNCA podem deixar o estoque negativo.
     *
     * Limitações deste teste:
     *  - As duas aprovações rodam em sequência (não em paralelo real).
     *  - O ambiente de teste usa SQLite em memória, onde lockForUpdate é
     *    no-op. Por isso este teste valida apenas a regra de estoque
     *    (segunda aprovação deve falhar quando o estoque já foi consumido).
     *
     * Para validar a corrida de verdade seria preciso:
     *  - Trocar o DB de teste para MySQL/PostgreSQL no phpunit.xml.
     *  - Disparar as duas chamadas em paralelo via pcntl_fork ou múltiplos
     *    workers HTTP (ex: Guzzle pool).
     */
    public function test_duas_aprovacoes_do_mesmo_produto_nao_deixam_estoque_negativo(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::factory()->create(['stock_quantity' => 2]);

        $order1 = Order::factory()->create(['status' => Status::AGUARDANDO]);
        OrderItem::factory()->create([
            'order_id' => $order1->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $order2 = Order::factory()->create(['status' => Status::AGUARDANDO]);
        OrderItem::factory()->create([
            'order_id' => $order2->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $headers = $this->authHeaderFor($admin);

        $r1 = $this->withHeaders($headers)->postJson("/api/orders/{$order1->id}/approve");
        $r2 = $this->withHeaders($headers)->postJson("/api/orders/{$order2->id}/approve");

        $r1->assertOk();
        $r2->assertStatus(422);

        $finalStock = $product->fresh()->stock_quantity;
        $this->assertSame(0, $finalStock);
        $this->assertGreaterThanOrEqual(0, $finalStock, 'Estoque jamais pode ser negativo.');

        $this->assertSame(Status::APROVADO, $order1->fresh()->status);
        $this->assertSame(Status::AGUARDANDO, $order2->fresh()->status);
    }
}
