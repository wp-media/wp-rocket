<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Warmup;

use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\ContentTrait;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Tables\Resources;

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
	 * Resources table instance.
	 *
	 * @var Resources
	 */
	private $resources_table;

	/**
	 * Plugin options instance.
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Instantiate the class
	 *
	 * @param ScannerProcess $process Background process instance.
	 * @param Options        $options_api Options API instance.
	 * @param Resources      $resources_table Resources table instance.
	 * @param Options_Data   $options Options instance.
	 */
	public function __construct( ScannerProcess $process, Options $options_api, Resources $resources_table, Options_Data $options ) {
		$this->process         = $process;
		$this->options_api     = $options_api;
		$this->resources_table = $resources_table;
		$this->options         = $options;
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
		if ( ! isset( $value['remove_unused_css'] ) ) {
			return;
		}

		if (
			! isset( $old_value['remove_unused_css'] )
			&&
			1 === (int) $value['remove_unused_css']
		) {
			$this->dispatcher();
			return;
		}

		if (
			! isset( $old_value['remove_unused_css'] )
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
	 * Launches the scanner.
	 *
	 * @since 3.9
	 *
	 * @return void
	 */
	public function auto_start_scanner() {
		if ( ! $this->options->get( 'remove_unused_css' ) ) {
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

		$this->options_api->set( 'resources_scanner', $this->items );
		$this->options_api->set( 'resources_scanner_scanned', [] );
		$this->options_api->set( 'resources_scanner_fetched', [] );
		$this->resources_table->reset_prewarmup_fields();

		array_map( [ $this->process, 'push_to_queue' ], $this->items );

		$prewarmup_stats = [
			'scan_start_time'         => time(),
			'fetch_finish_time'       => null,
			'resources_scanner_count' => count( $this->items ),
			'allow_optimization'      => false,
		];
		$this->options_api->set( 'prewarmup_stats', $prewarmup_stats );

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
		$home_url = home_url( '/' );

		$this->items[ $home_url ] = [
			'type' => 'front_page',
			'url'  => $home_url,
		];

		$page_for_posts = get_option( 'page_for_posts' );

		if (
			'page' === get_option( 'show_on_front' )
			&&
			! empty( $page_for_posts )
		) {
			$blog_url = get_permalink( $page_for_posts );

			$this->items[ $blog_url ] = [
				'type' => 'home',
				'url'  => $blog_url,
			];
		}

		$post_types = $this->get_public_post_types();

		foreach ( $post_types as $post_type ) {
			$post_type_url = get_permalink( $post_type->ID );

			$this->items[ $post_type_url ] = [
				'type' => $post_type->post_type,
				'url'  => $post_type_url,
			];
		}

		$taxonomies = $this->get_public_taxonomies();

		foreach ( $taxonomies as $taxonomy ) {
			$term_url = get_term_link( (int) $taxonomy->ID, $taxonomy->taxonomy );

			$this->items[ $term_url ] = [
				'type' => $taxonomy->taxonomy,
				'url'  => $term_url,
			];
		}
	}
}
