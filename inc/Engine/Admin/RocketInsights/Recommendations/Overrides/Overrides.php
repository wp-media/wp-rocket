<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\RocketInsights\Recommendations\Overrides;

/**
 * Registry applying the registered overrides to the formatted recommendations.
 */
class Overrides {

	/**
	 * Registered overrides, grouped by recommendation slug.
	 *
	 * @var Override_Interface[][]
	 */
	private $overrides = [];

	/**
	 * Registers an override.
	 *
	 * @param Override_Interface $override Override to register.
	 * @return void
	 */
	public function add( Override_Interface $override ): void {
		$this->overrides[ $override->get_option_slug() ][] = $override;
	}

	/**
	 * Applies the registered overrides to the formatted recommendations, dropping the ones an override removes.
	 *
	 * @param array $recommendations Formatted recommendations.
	 * @return array
	 */
	public function apply_all( array $recommendations ): array {
		foreach ( $recommendations as $index => $recommendation ) {
			$overrides = $this->overrides[ $recommendation['option_slug'] ?? '' ] ?? [];

			foreach ( $overrides as $override ) {
				if ( $override->should_remove( $recommendation ) ) {
					unset( $recommendations[ $index ] );
					continue 2;
				}
			}

			foreach ( $overrides as $override ) {
				$recommendation = $override->apply( $recommendation );
			}

			$recommendations[ $index ] = $recommendation;
		}

		return array_values( $recommendations );
	}
}
