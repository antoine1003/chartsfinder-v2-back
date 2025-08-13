<?php

namespace App\Entity\Enum;

class FeatureStatusEnum
{
    const PENDING = 'pending';
    const TO_DO = 'to_do';
    const IN_PROGRESS = 'in_progress';
    const COMPLETED = 'completed';
    const ABANDONED = 'abandoned';

    public static function getValues(): array
    {
        return [
            self::PENDING,
            self::TO_DO,
            self::IN_PROGRESS,
            self::COMPLETED,
            self::ABANDONED,
        ];
    }
}
