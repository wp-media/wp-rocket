<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\DeferJS;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\DynamicLists\DataManager;

class DeferJS {
	/**
	 * Options instance
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * DataManager instance
	 *
	 * @var DataManager
	 */
	private $data_manager;

	/**
	 * Instantiate the class
	 *
	 * @param Options_Data $options Options instance.
	 * @param DataManager  $data_manager DataManager instance.
	 */
	public function __construct( Options_Data $options, DataManager $data_manager ) {
		$this->options      = $options;
		$this->data_manager = $data_manager;
	}

	/**
	 * Add the exclude defer JS option in WP Rocket options array
	 *
	 * @since 3.8
	 *
	 * @param array $options WP Rocket options array.
	 * @return array
	 */
	public function add_option( array $options ) : array {
		$options['exclude_defer_js'] = [];

		return $options;
	}

	/**
	 * Defer all JS files.
	 *
	 * @since 3.8
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	public function defer_js( string $html ) : string {
		if ( ! $this->can_defer_js() ) {
			return $html;
		}

		$buffer_nocomments = preg_replace( '/<!--(.*)-->/Uis', '', $html );

		preg_match_all( '#<script\s+(?:[^>]+[\s\'"])?src\s*=\s*[\'"]\s*?(?<url>[^\'"]+)\s*?[\'"](?:[^>]+)?\/?>#i', $buffer_nocomments, $matches, PREG_SET_ORDER );

		if ( empty( $matches ) ) {
			return $html;
		}

		$exclude_defer_js = implode( '|', $this->get_excluded() );

		if ( ! @preg_replace( '#(' . $exclude_defer_js . ')#i', '', 'dummy-string' ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			return $html;
		}

		foreach ( $matches as $tag ) {
			if ( preg_match( '#(' . $exclude_defer_js . ')#i', $tag['url'] ) ) {
				continue;
			}

			if ( preg_match( '/\s+(?:async|defer)/i', $tag[0] ) ) {
				continue;
			}

			$deferred_tag = str_replace( '>', ' defer>', $tag[0] );
			$html         = str_replace( $tag[0], $deferred_tag, $html );
		}

		return $html;
	}

	/**
	 * Defers inline JS containing jQuery calls
	 *
	 * @since 3.8
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	public function defer_inline_js( string $html ) : string {
		if ( ! $this->can_defer_js() ) {
			return $html;
		}

		$exclude_defer_js = implode( '|', $this->get_excluded() );

		if ( preg_match( '/(jquery(?:.*)?\.js)/i', $exclude_defer_js ) ) {
			return $html;
		}

		$buffer_nocomments = preg_replace( '/<!--(.*)-->/Uis', '', $html );

		preg_match_all( '#<script(?:[^>]*)>(?<content>[\s\S]*?)</script>#msi', $buffer_nocomments, $matches, PREG_SET_ORDER );

		if ( empty( $matches ) ) {
			return $html;
		}

		/**
		 * Filters the patterns used to find jQuery in inline JS
		 *
		 * @since 3.8
		 *
		 * @param string $jquery_patterns A RegEx pattern to find jQuery inline JS.
		 */
		$jquery_patterns = apply_filters( 'rocket_defer_jquery_patterns', 'jQuery|\$\.\(|\$\(' );

		$inline_exclusions = $this->get_inline_exclusions_list_pattern();

		foreach ( $matches as $inline_js ) {
			if ( empty( $inline_js['content'] ) ) {
				continue;
			}

			if ( preg_match( '/(application\/ld\+json)/i', $inline_js[0] ) ) {
				continue;
			}

			if ( empty( $inline_exclusions ) || preg_match( "/({$inline_exclusions})/msi", $inline_js['content'] ) ) {
				continue;
			}

			if ( ! empty( $jquery_patterns ) && ! preg_match( "/({$jquery_patterns})/msi", $inline_js['content'] ) ) {
				continue;
			}

			$tag  = str_replace( $inline_js['content'], $this->inline_js_wrapper( $inline_js['content'] ), $inline_js[0] );
			$html = str_replace( $inline_js[0], $tag, $html );
		}

		return $html;
	}

	/**
	 * Adds the JS wrapper for inline jQuery code
	 *
	 * @since 3.8
	 *
	 * @param string $content Inline JS content.
	 * @return string
	 */
	private function inline_js_wrapper( string $content ) : string {
		return "window.addEventListener('DOMContentLoaded', function() {" . $content . '});';
	}

	/**
	 * Checks if we can defer JS
	 *
	 * @since 3.8
	 *
	 * @return boolean
	 */
	private function can_defer_js() : bool {
		if ( rocket_get_constant( 'DONOTROCKETOPTIMIZE' ) ) {
			return false;
		}

		if ( ! $this->options->get( 'defer_all_js', 0 ) ) {
			return false;
		}

		return ! is_rocket_post_excluded_option( 'defer_all_js' );
	}

	/**
	 * Get list of JS files to be excluded from defer JS.
	 *
	 * @since 3.8
	 *
	 * @return array
	 */
	public function get_excluded() : array {
		if ( ! $this->can_defer_js() ) {
			return [];
		}

		$exclude_defer_js = array_unique( array_merge( $this->get_external_exclusions(), $this->options->get( 'exclude_defer_js', [] ) ) );

		/**
		 * Filter list of Deferred JavaScript files
		 *
		 * @since 2.10
		 *
		 * @param array $exclude_defer_js An array of URLs for the JS files to be excluded.
		 */
		$exclude_defer_js = apply_filters( 'rocket_exclude_defer_js', $exclude_defer_js );

		foreach ( $exclude_defer_js as $i => $exclude ) {
			$exclude_defer_js[ $i ] = str_replace( '#', '\#', $exclude );
		}

		return $exclude_defer_js;
	}

	/**
	 * Excludes jQuery from combine JS when defer and combine are enabled
	 *
	 * @since 3.8
	 *
	 * @param array $excluded_files Array of excluded files from combine JS.
	 * @return array
	 */
	public function exclude_jquery_combine( array $excluded_files ) : array {
		if ( ! $this->can_defer_js() ) {
			return $excluded_files;
		}

		if ( ! (bool) $this->options->get( 'minify_concatenate_js', 0 ) ) {
			return $excluded_files;
		}

		$excluded_files[] = '/jquery-?[0-9.]*(.min|.slim|.slim.min)?.js';

		return $excluded_files;
	}

	/**
	 * Adds jQuery to defer JS exclusion field if safe mode was enabled before 3.8
	 *
	 * @since 3.8
	 *
	 * @return void
	 */
	public function exclude_jquery_upgrade() {
		$options = get_option( 'wp_rocket_settings' );

		if ( ! isset( $options['defer_all_js_safe'] ) ) {
			return;
		}

		if ( ! (bool) $options['defer_all_js_safe'] ) {
			return;
		}

		$options['exclude_defer_js'][] = '/jquery-?[0-9.]*(.min|.slim|.slim.min)?.js';

		update_option( 'wp_rocket_settings', $options );
	}

	/**
	 * Get exclusion list pattern.
	 *
	 * @return string
	 */
	private function get_inline_exclusions_list_pattern() {
		$inline_exclusions_list = $this->get_inline_exclusions();

		/**
		 * Filters the patterns used to find inline JS that should not be deferred
		 *
		 * @since 3.8
		 *
		 * @param array $inline_exclusions_list Array of inline JS that should not be deferred.
		 */
		$additional_inline_exclusions_list = apply_filters( 'rocket_defer_inline_exclusions', null );

		$inline_exclusions = '';

		// Check if filter return is string so convert it to array for backward compatibility.
		if ( is_string( $additional_inline_exclusions_list ) ) {
			$additional_inline_exclusions_list = explode( '|', $additional_inline_exclusions_list );
		}

		// Cast filter return to array.
		$inline_exclusions_list = array_merge( $inline_exclusions_list, (array) $additional_inline_exclusions_list );

		foreach ( $inline_exclusions_list as $inline_exclusions_item ) {
			$inline_exclusions .= preg_quote( (string) $inline_exclusions_item, '#' ) . '|';
		}

		return rtrim( $inline_exclusions, '|' );
	}

	/**
	 * Get inline exclusions list
	 *
	 * @return array
	 */
	private function get_inline_exclusions(): array {
		$lists = $this->data_manager->get_lists();

		return isset( $lists->defer_js_inline_exclusions ) ? $lists->defer_js_inline_exclusions : [];
	}

	/**
	 * Get external exclusions list
	 *
	 * @return array
	 */
	private function get_external_exclusions(): array {
		$lists = $this->data_manager->get_lists();

		return isset( $lists->defer_js_external_exclusions ) ? $lists->defer_js_external_exclusions : [];
	}
}
