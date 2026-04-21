<?php

namespace App\Helpers;

class CurrencyHelper
{
    /**
     * Format currency based on currency code
     */
    public static function format($amount, $currency = 'PKR')
    {
        $currencySymbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'PKR' => 'Rs.',
            'AED' => 'د.إ',
        ];

        $symbol = $currencySymbols[$currency] ?? 'Rs.';

        // PKR format with commas differently (often no decimals for small denominations but here we use 0)
        if ($currency === 'PKR') {
            return $symbol.' '.number_format($amount, 0);
        }

        return $symbol.number_format($amount, 2);
    }

    /**
     * Get currency symbol
     */
    public static function symbol($currency = 'PKR')
    {
        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'PKR' => 'Rs.',
            'AED' => 'د.إ',
        ];

        return $symbols[$currency] ?? 'Rs.';
    }

    /**
     * Get currency name
     */
    public static function name($currency = 'PKR')
    {
        $names = [
            'USD' => 'US Dollar',
            'EUR' => 'Euro',
            'GBP' => 'British Pound',
            'PKR' => 'Pakistani Rupee',
            'AED' => 'UAE Dirham',
        ];

        return $names[$currency] ?? 'Unknown';
    }

    /**
     * Convert number to words
     */
    public static function spellOut($number)
    {
        if (class_exists('\NumberFormatter')) {
            $f = new \NumberFormatter('en', \NumberFormatter::SPELLOUT);

            return ucwords($f->format($number));
        }

        return self::manualSpellOut($number);
    }

    /**
     * Manual implementation of number to words (Fallback)
     */
    private static function manualSpellOut($number)
    {
        $hyphen = '-';
        $conjunction = ' and ';
        $separator = ', ';
        $negative = 'negative ';
        $decimal = ' point ';
        $dictionary = [
            0 => 'zero',
            1 => 'one',
            2 => 'two',
            3 => 'three',
            4 => 'four',
            5 => 'five',
            6 => 'six',
            7 => 'seven',
            8 => 'eight',
            9 => 'nine',
            10 => 'ten',
            11 => 'eleven',
            12 => 'twelve',
            13 => 'thirteen',
            14 => 'fourteen',
            15 => 'fifteen',
            16 => 'sixteen',
            17 => 'seventeen',
            18 => 'eighteen',
            19 => 'nineteen',
            20 => 'twenty',
            30 => 'thirty',
            40 => 'fourty',
            50 => 'fifty',
            60 => 'sixty',
            70 => 'seventy',
            80 => 'eighty',
            90 => 'ninety',
            100 => 'hundred',
            1000 => 'thousand',
            1000000 => 'million',
            1000000000 => 'billion',
            1000000000000 => 'trillion',
            1000000000000000 => 'quadrillion',
            1000000000000000000 => 'quintillion',
        ];

        if (! is_numeric($number)) {
            return false;
        }

        if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
            // overflow
            trigger_error(
                'manualSpellOut only accepts numbers between -'.PHP_INT_MAX.' and '.PHP_INT_MAX,
                E_USER_WARNING
            );

            return false;
        }

        if ($number < 0) {
            return $negative.self::manualSpellOut(abs($number));
        }

        $string = $fraction = null;

        if (strpos($number, '.') !== false) {
            [$number, $fraction] = explode('.', $number);
        }

        switch (true) {
            case $number < 21:
                $string = $dictionary[$number];
                break;
            case $number < 100:
                $tens = ((int) ($number / 10)) * 10;
                $units = $number % 10;
                $string = $dictionary[$tens];
                if ($units) {
                    $string .= $hyphen.$dictionary[$units];
                }
                break;
            case $number < 1000:
                $hundreds = $number / 100;
                $remainder = $number % 100;
                $string = $dictionary[(int) $hundreds].' '.$dictionary[100];
                if ($remainder) {
                    $string .= $conjunction.self::manualSpellOut($remainder);
                }
                break;
            default:
                $baseUnit = pow(1000, floor(log($number, 1000)));
                $numBaseUnits = (int) ($number / $baseUnit);
                $remainder = $number % $baseUnit;
                $string = self::manualSpellOut($numBaseUnits).' '.$dictionary[$baseUnit];
                if ($remainder) {
                    $string .= $remainder < 100 ? $conjunction : $separator;
                    $string .= self::manualSpellOut($remainder);
                }
                break;
        }

        if ($fraction !== null && is_numeric($fraction)) {
            $string .= $decimal;
            $words = [];
            foreach (str_split((string) $fraction) as $number) {
                $words[] = $dictionary[$number];
            }
            $string .= implode(' ', $words);
        }

        return ucwords($string);
    }
}
