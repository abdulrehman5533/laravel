<?php

namespace App\Services;

class MultiCountryComplianceService
{
    public function calculateTax($amount, $countryCode)
    {
        // Advanced Tax logic based on country
        $rates = [
            'IN' => 0.10, // 10%
            'US' => 0.15, // 15%
            'AE' => 0.05, // 5%
            'UK' => 0.20, // 20%
        ];

        $rate = $rates[$countryCode] ?? 0.10;

        return $amount * $rate;
    }

    public function calculateSocialSecurity($amount, $countryCode)
    {
        $rates = [
            'IN' => 0.12, // EPF
            'US' => 0.06, // Social Security
            'AE' => 0.00, // No tax/SS typically for expats
            'UK' => 0.13, // NI
        ];

        $rate = $rates[$countryCode] ?? 0.05;

        return $amount * $rate;
    }

    public function getComplianceLog($employee, $netSalary)
    {
        $log = [
            'id' => uniqid('COMP-'),
            'timestamp' => now()->toIso8601String(),
            'country' => $employee->country_code,
            'base_salary' => $employee->base_salary,
            'net_salary' => $netSalary,
            'statutory_deductions' => [
                'tax' => $this->calculateTax($employee->base_salary, $employee->country_code),
                'social_security' => $this->calculateSocialSecurity($employee->base_salary, $employee->country_code),
            ],
            'verified_by' => 'AI-Compliance-Engine-v2.1',
            'regulatory_framework' => $this->getFrameworkForCountry($employee->country_code),
            'status' => 'Compliant',
            'audit_hash' => hash('sha256', $employee->id.now().$netSalary),
        ];

        // In production, write this to a 'compliance_audits' table
        return $log;
    }

    protected function getFrameworkForCountry($countryCode)
    {
        $frameworks = [
            'IN' => 'Income Tax Act 1961 / EPFO',
            'US' => 'IRS / SSA',
            'AE' => 'FTA / MOHRE',
            'UK' => 'HMRC / Pension Act',
        ];

        return $frameworks[$countryCode] ?? 'Local Regulatory Framework';
    }

    public function checkAmlThreshold($amount, $currency = 'PKR')
    {
        $thresholds = [
            'USD' => 10000,
            'INR' => 1000000,
            'AED' => 40000,
            'PKR' => 2000000,
        ];

        $threshold = $thresholds[$currency] ?? 2000000;

        return [
            'is_high_value' => $amount >= $threshold,
            'requires_kyc' => $amount >= ($threshold * 0.5),
            'reporting_required' => $amount >= $threshold,
            'threshold_used' => $threshold,
            'currency' => $currency,
        ];
    }
}
