<?php

namespace App\Support;

use App\Models\LandingPageOption;

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

    public const DEFAULT_P2_FROM = 200000.0;

    public const DEFAULT_P1_FROM = 500000.0;

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

    public static function ranges(?array $overrides = null): array
    {
        $source = $overrides ?? [];
        $p2 = self::numericValue(
            $source['p2_from'] ?? LandingPageOption::getValue('factor_p2_from', self::DEFAULT_P2_FROM),
            self::DEFAULT_P2_FROM
        );
        $p1 = self::numericValue(
            $source['p1_from'] ?? LandingPageOption::getValue('factor_p1_from', self::DEFAULT_P1_FROM),
            self::DEFAULT_P1_FROM
        );

        if ($p2 < 0) {
            $p2 = self::DEFAULT_P2_FROM;
        }

        if ($p1 <= $p2) {
            $p1 = $p2 >= self::DEFAULT_P1_FROM ? $p2 + 1 : self::DEFAULT_P1_FROM;
            if ($p1 <= $p2) {
                $p1 = $p2 + 1;
            }
        }

        return [
            'p2_from' => $p2,
            'p1_from' => $p1,
        ];
    }

    public static function pick(float $p3Total, array $allowed, ?array $ranges = null): string
    {
        $allowed = self::normalize($allowed);
        $auto = array_values(array_intersect($allowed, self::AUTO_FACTORS));

        if ($auto === []) {
            return $allowed[0] ?? 'p3';
        }

        $ranges = self::ranges($ranges);
        $wanted = $p3Total >= $ranges['p1_from'] ? 'p1' : ($p3Total >= $ranges['p2_from'] ? 'p2' : 'p3');
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

    private static function numericValue(mixed $value, float $fallback): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        return $fallback;
    }
}
