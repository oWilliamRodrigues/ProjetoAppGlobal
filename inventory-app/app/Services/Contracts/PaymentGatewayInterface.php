<?php

namespace App\Services\Contracts;

use App\Models\Order;

interface PaymentGatewayInterface
{
    public function createPreference(Order $order): array;
}