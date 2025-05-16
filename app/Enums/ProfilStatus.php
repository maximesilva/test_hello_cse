<?php

namespace App\Enums;

enum ProfilStatus: string
{
    case INACTIVE = 'inactif';
    case PENDING = 'en attente';
    case ACTIVE = 'actif';

    public function label(): string
    {
        return match($this) {
            self::INACTIVE => 'Inactif',
            self::PENDING => 'En attente',
            self::ACTIVE => 'Actif',
        };
    }
} 