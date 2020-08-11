<?php

namespace WP_Rocket\Engine\Optimization\DelayJS;

use WP_Rocket\Admin\Options_Data;

class HTML {

	/**
	 * Plugin options instance.
	 *
	 * @since  3.7
	 *
	 * @var Options_Data
	 */
	protected $options;

	/**
	 * Creates an instance of HTML.
	 *
	 * @since  3.7
	 *
	 * @param Options_Data $options Plugin options instance.
	 */
	public function __construct( Options_Data $options ) {
		$this->options = $options;
	}

	/**
	 * Adjust HTML to have delay js structure.
	 *
	 * @param string $html Buffer html for the page.
	 *
	 * @return string
	 */
	public function delay_js( $html ) {

		if ( ! $this->is_allowed() ) {
			return $html;
		}

		return $this->parse( $html );
	}

	/**
	 * Checks if is allowed to Delay JS.
	 *
	 * @since 3.7
	 *
	 * @return bool
	 */
	public function is_allowed() {
		if ( rocket_get_constant( 'DONOTROCKETOPTIMIZE' ) ) {
			return false;
		}

		if ( rocket_get_constant( 'DONOTDELAYJS' ) ) {
			return false;
		}

		if ( ! $this->options->get( 'delay_js' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Parse the html and add/remove attributes from specific scripts.
	 *
	 * @param string $html Buffer html for the page.
	 *
	 * @return string
	 */
	private function parse( $html ) {
		$regex_pattern = '<script\s*(?<attr>[^>]*)?>(?<content>.*)?<\/script>';
		return preg_replace_callback( '/' . $regex_pattern . '/Uis', [ $this, 'replace_scripts' ], $html );
	}

	/**
	 * Callback method for preg_replace_callback that is used to adjust attributes for specific scripts.
	 *
	 * @param array $matches Matches array for scripts regex.
	 *
	 * @return string
	 */
	public function replace_scripts( $matches ) {
		$allowed_scripts = $this->prepare_allowed_scripts_regex();
		if (
			empty( $allowed_scripts )
			||
			(
				! empty( $allowed_scripts )
				&&
				! preg_match( '#(' . $allowed_scripts . ')#', $matches[0] )
			)
		) {
			return $matches[0];
		}

		$src = '';

		if ( ! empty( $matches['attr'] ) ) {
			// Search on src attribute on it.
			$src_count = preg_match( '/src=(["\'])(.*?)\1/', $matches['attr'], $src_matches );
			if ( false !== $src_count ) {
				$src = $src_matches[2];

				// Remove the src attribute.
				$matches[0] = str_replace( $src_matches[0], '', $matches[0] );
			}
		}else {
			// Get the JS content.
			if ( ! empty( $matches['content'] ) ) {
				$src = 'data:text/javascript;base64,' . base64_encode( $matches['content'] );// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode

				// Remove the JS content.
				$matches[0] = str_replace( $matches['content'], '', $matches[0] );
			}
		}

		if ( empty( $src ) ) {
			return $matches[0];
		}

		return str_replace( '<script', "<script data-rocketlazyloadscript='{$src}'", $matches[0] );
	}

	/**
	 * Prepare allowed scripts to be used as regex.
	 *
	 * @return string
	 */
	private function prepare_allowed_scripts_regex() {
		$delay_js_scripts = $this->options->get( 'delay_js_scripts', [] );

		/**
		 * Filters JS files to included into delay JS.
		 *
		 * @since 3.7
		 *
		 * @param array $delay_js_scripts List of allowed JS files.
		 */
		$delay_js_scripts = (array) apply_filters( 'rocket_delay_js_scripts', $delay_js_scripts );

		if ( empty( $delay_js_scripts ) ) {
			return '';
		}

		foreach ( $delay_js_scripts as $i => $delay_js_script ) {
			$delay_js_scripts[ $i ] = preg_quote( str_replace( '#', '\#', $delay_js_script ), '#' );
		}

		return implode( '|', $delay_js_scripts );

	}

}
