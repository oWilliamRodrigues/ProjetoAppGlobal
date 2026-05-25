<?php

namespace App\Models\Enums;

enum Status : string {
    case AGUARDANDO = "aguardando";
    case AGUARDANDO_PAGAMENTO = "aguardando_pagamento";
    case APROVADO = "aprovado";
    case DESCARTADO = "descartado";
}