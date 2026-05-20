<?php

namespace App\Models\Enums;

enum Status : string {
    case AGUARDANDO = "aguardando";
    case APROVADO = "aprovado";
    case DESCARTADO = "descartado";
}