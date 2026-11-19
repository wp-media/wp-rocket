<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\License\API;

/**
 * Currency helper for WP Rocket pricing.
 */
class Currency {
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

	/**
	 * Format price with currency symbol.
	 *
	 * @param float|string $price Price.
	 * @param string       $currency Currency string.
	 * @param string       $wrap_span Wraps price components in span tags for styling:
	 *       - Empty string: not to wrap.
	 *       - price: wrap price only.
	 *       - currency: wrap currency symbol only.
	 *       - both: wrap both price and currency symbol individually.
	 * @param array        $span_classes Span classes.
	 * @param bool         $with_space With space between currency and price or not, default with no space.
	 *
	 * @return string
	 */
	public static function format_price_with_currency_symbol( $price, string $currency, string $wrap_span = '', array $span_classes = [], $with_space = false ): string {
		$currency_symbol = self::get_symbol( $currency );
		switch ( $wrap_span ) {
			case 'both':
				$price           = self::wrap_span( $price, $span_classes['price'] ?? '' );
				$currency_symbol = self::wrap_span( $currency_symbol, $span_classes['currency'] ?? '' );
				break;
			case 'price':
				$price = self::wrap_span( $price, $span_classes['price'] ?? '' );
				break;
			case 'currency':
				$currency_symbol = self::wrap_span( $currency_symbol, $span_classes['currency'] ?? '' );
		}

		if ( self::is_euro( $currency ) ) {
			return $price . ( $with_space ? ' ' : '' ) . $currency_symbol;
		}
		return $currency_symbol . ( $with_space ? ' ' : '' ) . $price;
	}

	/**
	 * Check if currency is euro or not.
	 *
	 * @param string $currency Currency to be tested.
	 * @return bool
	 */
	public static function is_euro( $currency ): bool {
		return 'EUR' === $currency;
	}

	/**
	 * Wrap a string in a span tag.
	 *
	 * @param int|float|string $item Item can be string, float, or integer.
	 * @param string           $classnames Class attribute.
	 * @return string
	 */
	private static function wrap_span( $item, $classnames = '' ): string {
		return sprintf( '<span class="%s">%s</span>', $classnames, $item );
	}
}
