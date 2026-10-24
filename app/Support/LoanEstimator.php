<?php

namespace App\Support;

/**
 * The same amortisation formula the browser runs in resources/js/app.js, so the
 * JS-free form and the live estimator always agree. It is an estimate only:
 * FDC sets the real rate, fees and terms when it assesses an application.
 */
final class LoanEstimator
{
    /**
     * Monthly payment for a fixed-rate loan, rounded to cents.
     *
     * @param  float  $principal  amount borrowed (price minus deposit)
     * @param  float  $annualRate  percent per year, e.g. 9.5
     */
    public static function monthly(float $principal, float $annualRate, int $months): float
    {
        if ($principal <= 0 || $months <= 0) {
            return 0.0;
        }
        $r = $annualRate / 100 / 12;
        if ($r <= 0) {
            return round($principal / $months, 2);
        }

        return round($principal * $r / (1 - (1 + $r) ** -$months), 2);
    }

    /**
     * @return array{price: int, deposit: int, principal: int, months: int, rate: float, monthly: float, total_interest: float}|null
     */
    public static function estimate(int $price, int $deposit, int $months, ?float $rate): ?array
    {
        if ($rate === null || $price <= 0 || $months <= 0) {
            return null;
        }
        $deposit = max(0, min($deposit, $price));
        $principal = $price - $deposit;
        $monthly = self::monthly($principal, $rate, $months);

        return [
            'price' => $price,
            'deposit' => $deposit,
            'principal' => $principal,
            'months' => $months,
            'rate' => $rate,
            'monthly' => $monthly,
            'total_interest' => round(max(0, $monthly * $months - $principal), 2),
        ];
    }
}
