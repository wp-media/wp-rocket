<?php

namespace WP_Rocket\Engine\CriticalPath;

use FilesystemIterator;
use UnexpectedValueException;
use WP_Filesystem_Direct;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\ContentTrait;

/**
 * Handles the critical CSS generation process.
 *
 * @since 2.11
 */
class CriticalCSS {
	use ContentTrait;

	/**
	 * Background Process instance.
	 *
	 * @var CriticalCSSGeneration
	 */
	public $process;

	/**
	 * WP Rocket options instance.
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Items for which we generate a critical CSS.
	 *
	 * @var array
	 */
	public $items = [];

	/**
	 * Path to the critical CSS directory.
	 *
	 * @var string
	 */
	private $critical_css_path;

	/**
	 * Instance of the filesystem handler.
	 *
	 * @var WP_Filesystem_Direct
	 */
	private $filesystem;

	/**
	 * Creates an instance of CriticalCSS.
	 *
	 * @param CriticalCSSGeneration $process    Background process instance.
	 * @param Options_Data          $options    Instance of options data handler.
	 * @param WP_Filesystem_Direct  $filesystem Instance of the filesystem handler.
	 */
	public function __construct( CriticalCSSGeneration $process, Options_Data $options, $filesystem ) {
		$this->process             = $process;
		$this->options             = $options;
		$this->critical_css_path   = rocket_get_constant( 'WP_ROCKET_CRITICAL_CSS_PATH' ) . get_current_blog_id() . '/';
		$this->filesystem          = $filesystem;
		$this->items['front_page'] = [
			'type'  => 'front_page',
			'url'   => home_url( '/' ),
			'path'  => 'front_page.css',
			'check' => 0,
		];
	}

	/**
	 * Returns the current site critical CSS path.
	 *
	 * @since 3.3.5
	 *
	 * @return string
	 */
	public function get_critical_css_path() {
		return $this->critical_css_path;
	}

	/**
	 * Performs the critical CSS generation.
	 *
	 * @since 3.13.2 Always clear all CPCSS files.
	 * @since 3.6 Added the $version parameter.
	 * @since 2.11
	 *
	 * @param string $version Optional. Version of the CPCSS files to generate. Possible values: default, mobile, all.
	 * @param string $clean_version Optional: Version of the CPCSS files to clean. Possible values: default, mobile, all.
	 */
	public function process_handler( $version = 'default', $clean_version = '' ) {
		/**
		 * Filters the critical CSS generation process.
		 *
		 * Use this filter to prevent the automatic critical CSS generation.
		 *
		 * @since 2.11.5
		 *
		 * @param bool $do_rocket_critical_css_generation True to activate the automatic generation, false to prevent it.
		 */
		if ( ! apply_filters( 'do_rocket_critical_css_generation', true ) ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
			return;
		}

		if ( get_transient( 'rocket_critical_css_generation_process_running' ) ) {
			return;
		}

		if ( empty( $clean_version ) ) {
			$clean_version = $version;
		}

		$this->clean_critical_css( $clean_version );

		$this->stop_generation();

		$this->set_items( $version );

		array_map( [ $this->process, 'push_to_queue' ], $this->items );

		$this->update_process_running_transient();

		$this->process->save()->dispatch();
	}

	/**
	 * Stop the critical CSS generation process.
	 *
	 * @since 3.3
	 */
	public function stop_generation() {
		// @phpstan-ignore-next-line
		if ( method_exists( $this->process, 'cancel_process' ) ) {
			$this->process->cancel_process();
		}
	}

	/**
	 * Launches when the CPCSS generation is complete.
	 *
	 * @since 3.6
	 */
	protected function generation_complete() {
		/**
		 * Fires when the critical CSS generation process is complete.
		 *
		 * @since 2.11
		 */
		do_action( 'rocket_critical_css_generation_process_complete' );

		set_transient( 'rocket_critical_css_generation_process_complete', get_transient( 'rocket_critical_css_generation_process_running' ), HOUR_IN_SECONDS );
		delete_transient( 'rocket_critical_css_generation_process_running' );
	}

	/**
	 * Deletes critical CSS files.
	 *
	 * @since 3.6 Replaced glob().
	 * @since 3.6 Added $version parameter.
	 * @since 2.11
	 *
	 * @param string $version Optional. Version of the CPCSS files to delete. Possible values: default, mobile, all.
	 */
	public function clean_critical_css( $version = 'default' ) {
		foreach ( $this->get_critical_css_iterator() as $file ) {
			if ( ! $this->filesystem->is_file( $file ) ) {
				continue;
			}

			if (
				'mobile' === $version
				&&
				false === strpos( $file, '-mobile' )
			) {
				continue;
			} elseif (
				'default' === $version
				&&
				false !== strpos( $file, '-mobile' )
			) {
				continue;
			}

			$this->filesystem->delete( $file );
		}
	}

	/**
	 * Gets the Critical CSS Filesystem Iterator.
	 *
	 * @since 3.6
	 *
	 * @return FilesystemIterator|array Returns iterator on success; else an empty array.
	 */
	private function get_critical_css_iterator() {
		try {
			return new FilesystemIterator( $this->critical_css_path );
		} catch ( UnexpectedValueException $e ) {
			// No logging yet.
			return [];
		}
	}

	/**
	 * Sets the items for which we generate critical CSS.
	 *
	 * @since  2.11
	 *
	 * @param string $version Optional. Version of the CPCSS files to generate. Possible values: default, mobile, all.
	 */
	private function set_items( $version = 'default' ) {
		$page_for_posts = get_option( 'page_for_posts' );

		if ( 'page' === get_option( 'show_on_front' ) && ! empty( $page_for_posts ) ) {
			$this->items['home'] = [
				'type'  => 'home',
				'url'   => get_permalink( get_option( 'page_for_posts' ) ),
				'path'  => 'home.css',
				'check' => 0,
			];
		}

		$post_types = $this->get_public_post_types();

		foreach ( $post_types as $post_type ) {
			$this->items[ $post_type->post_type ] = [
				'type'  => $post_type->post_type,
				'url'   => get_permalink( $post_type->ID ),
				'path'  => "{$post_type->post_type}.css",
				'check' => 0,
			];
		}

		$taxonomies = $this->get_public_taxonomies();
		foreach ( $taxonomies as $taxonomy ) {

			$this->items[ $taxonomy->taxonomy ] = [
				'type'  => $taxonomy->taxonomy,
				'url'   => get_term_link( (int) $taxonomy->ID, $taxonomy->taxonomy ),
				'path'  => "{$taxonomy->taxonomy}.css",
				'check' => 0,
			];
		}

		if ( in_array( $version, [ 'all', 'mobile' ], true ) ) {
			$mobile_items = [];

			foreach ( $this->items as $key => $value ) {
				$value['mobile']                 = 1;
				$value['path']                   = str_replace( '.css', '-mobile.css', $value['path'] );
				$mobile_items[ "{$key}-mobile" ] = $value;
			}

			if ( 'mobile' === $version ) {
				$this->items = $mobile_items;
			} elseif ( 'all' === $version ) {
				$this->items = array_merge( $this->items, $mobile_items );
			}
		}

		/**
		 * Filters the array containing the items to send to the critical CSS generator.
		 *
		 * @since  2.11.4
		 *
		 * @param array $items Array containing the type/url pair for each item to send.
		 */
		$this->items = (array) apply_filters( 'rocket_cpcss_items', $this->items );
	}

	/**
	 * Updates the "rocket_critical_css_generation_process_running" transient.
	 *
	 * @since 3.6
	 */
	private function update_process_running_transient() {
		$total = 0;

		foreach ( $this->items as $item ) {
			if ( ! isset( $item['mobile'] ) ) {
				++$total;
				continue;
			}

			if ( 1 === $item['mobile'] ) {
				continue;
			}

			++$total;
		}

		$transient = [
			'total' => $total,
			'items' => [],
		];

		set_transient( 'rocket_critical_css_generation_process_running', $transient, HOUR_IN_SECONDS );
	}

	/**
	 * Gets the CPCSS content to use on the current page.
	 *
	 * @since 3.6
	 *
	 * @return bool|string
	 */
	public function get_critical_css_content() {
		$filename = $this->get_current_page_critical_css();

		if ( empty( $filename ) ) {
			return $this->options->get( 'critical_css', '' );
		}

		return $this->filesystem->get_contents( $filename );
	}

	/**
	 * Gets the CPCSS filepath for the current page.
	 *
	 * @since  2.11
	 *
	 * @return string Filepath if the file exists, empty string otherwise.
	 */
	public function get_current_page_critical_css() {
		$files = $this->get_critical_css_filenames();

		if (
			$this->is_async_css_mobile()
			&&
			wp_is_mobile()
			&&
			$this->filesystem->is_readable( $this->critical_css_path . $files['mobile'] )
		) {
			return $this->critical_css_path . $files['mobile'];
		}

		if ( $this->filesystem->is_readable( $this->critical_css_path . $files['default'] ) ) {
			return $this->critical_css_path . $files['default'];
		}

		return '';
	}

	/**
	 * Gets the CPCSS filenames for the current URL type.
	 *
	 * @since 3.6
	 *
	 * @return array
	 */
	private function get_critical_css_filenames() {
		$default = [
			'default' => 'front_page.css',
			'mobile'  => 'front_page-mobile.css',
		];

		if ( is_home() && 'page' === get_option( 'show_on_front' ) ) {
			return [
				'default' => 'home.css',
				'mobile'  => 'home-mobile.css',
			];
		}

		if ( is_front_page() ) {
			return $default;
		}

		if ( is_category() ) {
			return [
				'default' => 'category.css',
				'mobile'  => 'category-mobile.css',
			];
		}

		if ( is_tag() ) {
			return [
				'default' => 'post_tag.css',
				'mobile'  => 'post_tag-mobile.css',
			];
		}

		if ( is_tax() ) {
			$taxonomy = get_queried_object()->taxonomy;

			return [
				'default' => "{$taxonomy}.css",
				'mobile'  => "{$taxonomy}-mobile.css",
			];
		}

		if ( is_singular() ) {
			return $this->get_singular_cpcss_filenames();
		}

		return $default;
	}

	/**
	 * Gets the filenames for a singular content.
	 *
	 * @since 3.6
	 *
	 * @return array
	 */
	private function get_singular_cpcss_filenames() {
		$post_type  = get_post_type();
		$post_id    = get_the_ID();
		$post_cpcss = [
			'default' => "posts/{$post_type}-{$post_id}.css",
			'mobile'  => "posts/{$post_type}-{$post_id}-mobile.css",
		];

		if (
			$this->is_async_css_mobile()
			&&
			! $this->filesystem->exists( $this->critical_css_path . $post_cpcss['mobile'] )
		) {
			$post_cpcss['mobile'] = $post_cpcss['default'];
		}

		if ( $this->filesystem->exists( $this->critical_css_path . $post_cpcss['default'] ) ) {
			return $post_cpcss;
		}

		return [
			'default' => "{$post_type}.css",
			'mobile'  => "{$post_type}-mobile.css",
		];
	}

	/**
	 * Checks if we are in a situation where we need the mobile CPCSS.
	 *
	 * @since 3.6
	 *
	 * @return bool
	 */
	public function is_async_css_mobile() {
		if ( ! (bool) $this->options->get( 'do_caching_mobile_files', 0 ) ) {
			return false;
		}

		return (bool) $this->options->get( 'async_css_mobile', 0 );
	}

	/**
	 * Get list of CSS files to be excluded from async CSS.
	 *
	 * @since 3.6.2
	 *
	 * @return array An array of URLs for the CSS files to be excluded.
	 */
	public function get_exclude_async_css() {
		/**
		 * Filter list of async CSS files
		 *
		 * @since 2.10
		 *
		 * @param array $exclude_async_css An array of URLs for the CSS files to be excluded.
		 */
		$exclude_async_css = (array) apply_filters( 'rocket_exclude_async_css', [] );
		if ( empty( $exclude_async_css ) ) {
			return $exclude_async_css;
		}
		$exclude_async_css = array_filter( $exclude_async_css );

		return array_flip( array_flip( $exclude_async_css ) );
	}
}
