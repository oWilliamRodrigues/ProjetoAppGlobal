<?php

namespace App\Models\Enums;

enum PaymentEvent : string {
    case APPROVED = "approved";
    case PENDING = "pending";
    case REJECTED = "rejected";
}