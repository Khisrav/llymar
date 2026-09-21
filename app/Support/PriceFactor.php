<?php

namespace App\Support;

class PriceFactor
{
    public const LABELS = [
        'pz' => 'ЗЦ',
        'p1' => 'Р1',
        'p2' => 'Р2',
        'p3' => 'Р3',
        'p4' => 'РЦ',
    ];

    public const AUTO_FACTORS = ['p3', 'p2', 'p1'];

    public static function normalize(mixed $value): array
    {
        if (is_array($value) && $value !== []) {
            return array_values(array_filter($value, fn ($factor) => is_string($factor) && $factor !== ''));
        }

        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);
            if (is_array($decoded) && $decoded !== []) {
                return self::normalize($decoded);
            }

            return [$value];
        }

        return ['p3'];
    }

    public static function pick(float $p3Total, array $allowed): string
    {
        $allowed = self::normalize($allowed);
        $auto = array_values(array_intersect($allowed, self::AUTO_FACTORS));

        if ($auto === []) {
            return $allowed[0] ?? 'p3';
        }

        $wanted = $p3Total >= 500000 ? 'p1' : ($p3Total >= 200000 ? 'p2' : 'p3');
        $order = match ($wanted) {
            'p1' => ['p1', 'p2', 'p3'],
            'p2' => ['p2', 'p3', 'p1'],
            default => ['p3', 'p2', 'p1'],
        };

        foreach ($order as $factor) {
            if (in_array($factor, $auto, true)) {
                return $factor;
            }
        }

        return $auto[0];
    }
}
