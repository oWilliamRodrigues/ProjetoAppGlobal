<?php

namespace App\Services;

use App\Models\Enums\Status;
use App\Models\Order;
use App\Models\Enums\PaymentEvent;
use Illuminate\Support\Facades\Log;

class OrderStatusTranslateService
{
    private function isContradictory(Status $currentStatus, PaymentEvent $event): bool
    {
        return match (true) {
            $currentStatus === Status::APROVADO && $event === PaymentEvent::REJECTED => true,
            $currentStatus === Status::DESCARTADO && $event === PaymentEvent::APPROVED => true,
            default => false,
        };
    }
    public function nextStatus(Status $currentstatus, PaymentEvent $event): ?Status
    {
        return match (true) {
            $currentstatus === Status::AGUARDANDO_PAGAMENTO && $event === PaymentEvent::APPROVED => Status::AGUARDANDO,
            $currentstatus === Status::AGUARDANDO_PAGAMENTO && $event === PaymentEvent::REJECTED => Status::DESCARTADO,
            default => null,
        };
    }

    public function apply(Order $order, string $mpPaymentStatus): void
    {
        $event = match ($mpPaymentStatus) {
            'approved' => PaymentEvent::APPROVED,
            'pending' => PaymentEvent::PENDING,
            'in_process' => PaymentEvent::PENDING,
            'rejected' => PaymentEvent::REJECTED,
            'cancelled' => PaymentEvent::REJECTED,
            default => null,
        };

        if ($event === null) {
            Log::info('Webhook MP: status de pagamento desconhecido', [
                'order_id'          => $order->id,
                'mp_payment_status' => $mpPaymentStatus,
            ]);
            return;
        }

        $nextStatus = $this->nextStatus($order->status, $event);

        if ($nextStatus !== null) {
            $order->status = $nextStatus;
        }
        elseif ($this->isContradictory($order->status, $event)) {
            Log::warning('Webhook MP: transição contraditória detectada', [
                'order_id'          => $order->id,
                'current_status'    => $order->status->value,
                'mp_payment_status' => $mpPaymentStatus,
                'event'             => $event->value,
            ]);
        }
    }
}