<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Warmup;

use WP_Rocket\Admin\Options;
use WP_Rocket\Engine\Optimization\ContentTrait;

class Scanner {
	use ContentTrait;

	/**
	 * Background Process instance.
	 *
	 * @var ScannerProcess
	 */
	public $process;

	/**
	 * Options API instance.
	 *
	 * @var Options
	 */
	private $options_api;

	/**
	 * Items for which we get the resources to warmup.
	 *
	 * @var array
	 */
	public $items = [];

	/**
	 * Instantiate the class
	 *
	 * @param ScannerProcess $process Background process instance.
	 * @param Options        $options_api Options API instance.
	 */
	public function __construct( ScannerProcess $process, Options $options_api ) {
		$this->process     = $process;
		$this->options_api = $options_api;
	}

	/**
	 * Launches the scanner when activating the RUCSS option
	 *
	 * @since 3.9
	 *
	 * @param array $old_value Previous values for WP Rocket settings.
	 * @param array $value     New values for WP Rocket settings.
	 *
	 * @return void
	 */
	public function start_scanner( $old_value, $value ) {
		if (
			! isset( $value['remove_unused_css'] )
			||
			( $old_value['remove_unused_css'] === $value['remove_unused_css'] )
			||
			1 !== (int) $value['remove_unused_css']
		) {
			return;
		}

		$this->dispatcher();
	}

	/**
	 * Gets the items to scan and dispatch them to the scanner
	 *
	 * @since 3.9
	 *
	 * @return void
	 */
	private function dispatcher() {
		$this->set_items();

		array_map( [ $this->process, 'push_to_queue' ], $this->items );

		$this->options_api->set( 'scanner_start_time', current_time( 'timestamp' ) );

		$this->process->save()->dispatch();
	}

	/**
	 * Sets the items for which we get the resources to warmup.
	 *
	 * @since 3.9
	 *
	 * @return void
	 */
	private function set_items() {
		$this->items['front_page'] = [
			'type'       => 'front_page',
			'url'        => home_url( '/' ),
			'is_scanned' => false,
		];

		$page_for_posts = get_option( 'page_for_posts' );

		if (
			'page' === get_option( 'show_on_front' )
			&&
			! empty( $page_for_posts )
		) {
			$this->items['home'] = [
				'type'       => 'home',
				'url'        => get_permalink( $page_for_posts ),
				'is_scanned' => false,
			];
		}

		$post_types = $this->get_public_post_types();

		foreach ( $post_types as $post_type ) {
			$this->items[ $post_type->post_type ] = [
				'type'       => $post_type->post_type,
				'url'        => get_permalink( $post_type->ID ),
				'is_scanned' => false,
			];
		}

		$taxonomies = $this->get_public_taxonomies();

		foreach ( $taxonomies as $taxonomy ) {
			$this->items[ $taxonomy->taxonomy ] = [
				'type'       => $taxonomy->taxonomy,
				'url'        => get_term_link( (int) $taxonomy->ID, $taxonomy->taxonomy ),
				'is_scanned' => false,
			];
		}
	}
}
