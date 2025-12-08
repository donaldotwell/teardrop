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

        // Define the conversion rate for USD to crypto.
        // For BTC: 1 BTC = 100,000 USD
        // For XMR: 1 XMR = 230.08 USD
        $conversionRate = ($currency === 'btc') ? 100000 : 230.08;

        // Convert the USD amount to the crypto amount.
        return round($amount / $conversionRate, 8);
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

        // Define the conversion rate for crypto to USD.
        // For BTC: 1 BTC = 100,000 USD
        // For XMR: 1 XMR = 230.08 USD
        $conversionRate = ($currency === 'btc') ? 100000 : 230.08;

        // Convert the crypto amount to the USD value.
        return round($amount * $conversionRate, 2);
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
