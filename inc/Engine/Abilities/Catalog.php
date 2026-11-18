<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Abilities;

class Catalog {
	/**
	 * Vendor namespace prefix abilities belonging to this plugin are registered under.
	 */
	const VENDOR_PREFIX = 'wp-rocket/';

	/**
	 * Builds the manifest of every WP Rocket ability currently registered with WordPress core.
	 *
	 * @return array
	 */
	public function get_manifest(): array {
		if ( ! function_exists( 'wp_get_abilities' ) ) {
			return [];
		}

		$manifest = [];

		foreach ( wp_get_abilities() as $ability ) {
			if ( 0 !== strpos( $ability->get_name(), self::VENDOR_PREFIX ) ) {
				continue;
			}

			$manifest[] = [
				'name'          => $ability->get_name(),
				'label'         => $ability->get_label(),
				'description'   => $ability->get_description(),
				'category'      => $ability->get_category(),
				'input_schema'  => $ability->get_input_schema(),
				'output_schema' => $ability->get_output_schema(),
				'meta'          => $ability->get_meta(),
			];
		}

		return $manifest;
	}
}
