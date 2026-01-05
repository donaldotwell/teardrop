<?php

/**
 * Convert the specified usd amount to the specified cryptocurrency value.
 *
 * @param float $amount
 * @param string $currency
 * @return float
 */
if (!function_exists('convert_usd_to_crypto')) {
    function convert_usd_to_crypto($amount, $currency)
    {
        // Validate the currency parameter (must be either 'btc' or 'xmr').
        if (!in_array($currency, ['btc', 'xmr'])) {
            return 0;
        }

        // Get exchange rate from database
        $rate = \App\Models\ExchangeRate::getRate($currency);

        // Fallback to hardcoded rates if database is not available
        if ($rate === null) {
            $rate = ($currency === 'btc') ? 100000 : 230.08;
        }

        // Convert the USD amount to the crypto amount.
        return round($amount / $rate, 8);
    }
}

/**
 * Convert the specified cryptocurrency amount to the specified USD value.
 *
 * @param float $amount
 * @param string $currency
 * @return float
 */
if (!function_exists('convert_crypto_to_usd')) {
    function convert_crypto_to_usd($amount, $currency)
    {
        // Validate the currency parameter (must be either 'btc' or 'xmr').
        if (!in_array($currency, ['btc', 'xmr'])) {
            return 0;
        }

        // Get exchange rate from database
        $rate = \App\Models\ExchangeRate::getRate($currency);

        // Fallback to hardcoded rates if database is not available
        if ($rate === null) {
            $rate = ($currency === 'btc') ? 100000 : 230.08;
        }

        // Convert the crypto amount to the USD value.
        return round($amount * $rate, 2);
    }
}

if (!function_exists('can_moderate_forum')) {
    /**
     * Check if the current user can moderate the forum
     */
    function can_moderate_forum()
    {
        return auth()->check() && auth()->user()->hasAnyRole(['admin', 'moderator']);
    }
}

if (!function_exists('user_can_post')) {
    /**
     * Check if the current user can create posts/comments
     */
    function user_can_post()
    {
        return auth()->check() && auth()->user()->status === 'active';
    }
}

if (!function_exists('estimate_btc_transaction_fee')) {
    /**
     * Estimate Bitcoin transaction fee based on amount.
     * Returns estimated fee in BTC based on fee tiers.
     *
     * @param float $amount Transaction amount in BTC
     * @return float Estimated fee in BTC
     */
    function estimate_btc_transaction_fee(float $amount): float
    {
        // Get fee rate for this amount (sat/vB)
        $feeRate = \App\Repositories\BitcoinRepository::getFeeRateForAmount($amount);

        // Estimate transaction size in vBytes
        // Average transaction: 1 input (~148 vB) + 2 outputs (~68 vB) = ~226 vB
        // Conservative estimate: 250 vBytes
        $estimatedVBytes = 250;

        // Calculate fee in satoshis
        $feeSatoshis = $feeRate * $estimatedVBytes;

        // Convert to BTC (1 BTC = 100,000,000 satoshis)
        $feeBtc = $feeSatoshis / 100000000;

        return round($feeBtc, 8);
    }
}
