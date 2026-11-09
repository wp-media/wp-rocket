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
	 * Drops the recommendations an override removes, without applying any other override.
	 *
	 * @param array $recommendations Recommendations, each needing at least an option_slug.
	 * @return array
	 */
	public function remove_hidden( array $recommendations ): array {
		foreach ( $recommendations as $index => $recommendation ) {
			foreach ( $this->overrides[ $recommendation['option_slug'] ?? '' ] ?? [] as $override ) {
				if ( $override->should_remove( $recommendation ) ) {
					unset( $recommendations[ $index ] );
					break;
				}
			}
		}

		return array_values( $recommendations );
	}

	/**
	 * Applies the registered overrides to the formatted recommendations, dropping the ones an override removes.
	 *
	 * @param array $recommendations Formatted recommendations.
	 * @return array
	 */
	public function apply_all( array $recommendations ): array {
		$applied = [];

		foreach ( $this->remove_hidden( $recommendations ) as $recommendation ) {
			foreach ( $this->overrides[ $recommendation['option_slug'] ?? '' ] ?? [] as $override ) {
				$recommendation = $override->apply( $recommendation );
			}

			$applied[] = $recommendation;
		}

		return $applied;
	}
}
