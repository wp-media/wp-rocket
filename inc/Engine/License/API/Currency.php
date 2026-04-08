<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\License\API;

/**
 * Currency helper for WP Rocket pricing.
 */
class Currency {
	/**
	 * Supported currency codes.
	 *
	 * @var array
	 */
	const SUPPORTED_CURRENCIES = [ 'USD', 'EUR' ];

	/**
	 * Default currency.
	 *
	 * @var string
	 */
	const DEFAULT_CURRENCY = 'USD';

	/**
	 * Currency symbols mapping.
	 *
	 * @var array
	 */
	const CURRENCY_SYMBOLS = [
		'USD' => '$',
		'EUR' => '€',
	];

	/**
	 * Get currency symbol from currency code.
	 *
	 * @param string $currency Currency code (EUR|USD).
	 * @return string Currency symbol (€|$).
	 */
	public static function get_symbol( string $currency ): string {
		$currency = strtoupper( trim( $currency ) );

		if ( ! isset( self::CURRENCY_SYMBOLS[ $currency ] ) ) {
			return self::CURRENCY_SYMBOLS[ self::DEFAULT_CURRENCY ];
		}

		return self::CURRENCY_SYMBOLS[ $currency ];
	}
}
