<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_com_credenciais_validas_retorna_token(): void
    {
        $user = User::factory()->create([
            'email' => 'teste@globalsys.com.br',
            'password' => 'teste', 
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'teste@globalsys.com.br',
            'password' => 'teste',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'user' => ['id', 'name', 'email'],
                     'token',
                 ]);
    }

    public function test_login_com_senha_errada_retorna_422(): void
    {
        $user = User::factory()->create([
            'email' => 'teste@globalsys.com.br',
            'password' => 'senha-certa',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'teste@globalsys.com.br',
            'password' => 'senhaerrada',
        ]);

        $response->assertStatus(422); 
    }

    public function test_logout_deleta_o_token_atual(): void
    {
        $user = User::factory()->create();

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/logout');

        $response->assertStatus(200);

        $user->refresh();
        $this->assertCount(0, $user->tokens);
    }

    public function test_me_autenticado_retorna_dados_do_usuario(): void
    {
        $user = User::factory()->create([
            'email' => 'teste@globalsys.com.br',
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/me');

        $response->assertStatus(200)
                ->assertJsonStructure(['user' => ['id', 'name', 'email']])
                ->assertJsonPath('user.email', 'teste@globalsys.com.br'); 
    }

    public function test_me_sem_token_retorna_401(): void
    {
        $response = $this->getJson('/api/me');

        $response->assertStatus(401);
    }
}