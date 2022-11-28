<?php
declare( strict_types=1 );

namespace WP_Rocket\Engine\Optimization\DelayJS;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\DynamicLists\DataManager;
use WP_Rocket\Engine\Optimization\RegexTrait;

class HTML {
	use RegexTrait;
	/**
	 * Plugin options instance.
	 *
	 * @var Options_Data
	 */
	protected $options;

	/**
	 * DataManager instance
	 *
	 * @var DataManager
	 */
	private $data_manager;

	/**
	 * Array of excluded patterns from delay JS
	 *
	 * @since 3.9
	 *
	 * @var array
	 */
	protected $excluded = [];

	/**
	 * Creates an instance of HTML.
	 *
	 * @param Options_Data $options Plugin options instance.
	 * @param DataManager  $data_manager DataManager instance.
	 */
	public function __construct( Options_Data $options, DataManager $data_manager ) {
		$this->options      = $options;
		$this->data_manager = $data_manager;
	}

	/**
	 * Adjust HTML to have delay js structure.
	 *
	 * @since 3.9 Updated to use exclusions list instead of inclusions list.
	 * @since 3.7
	 *
	 * @param string $html Buffer html for the page.
	 *
	 * @return string
	 */
	public function delay_js( $html ): string {
		if ( ! $this->is_allowed() ) {
			return $html;
		}

		$this->set_exclusions();

		$this->excluded = array_merge( $this->excluded, $this->options->get( 'delay_js_exclusions', [] ) );

		/**
		 * Filters the delay JS exclusions array
		 *
		 * @since 3.9
		 *
		 * @param array $excluded Array of excluded patterns.
		 */
		$this->excluded = (array) apply_filters( 'rocket_delay_js_exclusions', $this->excluded );
		$this->excluded = array_map(
			function ( $value ) {
				if ( ! is_string( $value ) ) {
					$value = (string) $value;
				}

				return str_replace(
					[ '+', '?ver', '#' ],
					[ '\+', '\?ver', '\#' ],
					$value
				);
			},
			$this->excluded
		);

		return $this->parse( $html );
	}

	/**
	 * Checks if is allowed to Delay JS.
	 *
	 * @since 3.7
	 *
	 * @return bool
	 */
	public function is_allowed(): bool {

		if ( rocket_bypass() ) {
			return false;
		}

		if ( rocket_get_constant( 'DONOTROCKETOPTIMIZE' ) ) {
			return false;
		}

		if ( is_rocket_post_excluded_option( 'delay_js' ) ) {
			return false;
		}

		return (bool) $this->options->get( 'delay_js', 0 );
	}

	/**
	 * Gets Javascript to redirect IE visitors to the uncached page
	 *
	 * @since 3.9
	 *
	 * @return string
	 */
	public function get_ie_fallback(): string {
		return 'if(navigator.userAgent.match(/MSIE|Internet Explorer/i)||navigator.userAgent.match(/Trident\/7\..*?rv:11/i)){var href=document.location.href;if(!href.match(/[?&]nowprocket/)){if(href.indexOf("?")==-1){if(href.indexOf("#")==-1){document.location.href=href+"?nowprocket=1"}else{document.location.href=href.replace("#","?nowprocket=1#")}}else{if(href.indexOf("#")==-1){document.location.href=href+"&nowprocket=1"}else{document.location.href=href.replace("#","&nowprocket=1#")}}}}';
	}

	/**
	 * Parse the html and add/remove attributes from specific scripts.
	 *
	 * @since 3.7
	 *
	 * @param string $html Buffer html for the page.
	 *
	 * @return string
	 */
	private function parse( $html ): string {

		if ( empty( $html ) ) {
			return $html;
		}

		$result = $this->replace_xmp_tags( $html );

		$replaced_html = preg_replace_callback(
			'/<\s*script\s*(?<attr>[^>]*?)?>(?<content>.*?)?<\s*\/\s*script\s*>/ims',
			[
				$this,
				'replace_scripts',
			],
			$result
		);
		if ( empty( $replaced_html ) ) {
			return $html;
		}
		return $this->restore_xmp_tags( $replaced_html );
	}

	/**
	 * Callback method for preg_replace_callback that is used to adjust attributes for scripts.
	 *
	 * @since 3.9 Use exclusions list & fake type attribute.
	 * @since 3.7
	 *
	 * @param array $matches Matches array for scripts regex.
	 *
	 * @return string
	 */
	public function replace_scripts( $matches ): string {
		foreach ( $this->excluded as $pattern ) {
			if ( preg_match( "#{$pattern}#i", $matches[0] ) ) {
				return $matches[0];
			}
		}

		$matches['attr'] = trim( $matches['attr'] );
		$delay_js        = $matches[0];

		if ( ! empty( $matches['attr'] ) ) {

			if (
				strpos( $matches['attr'], 'type=' ) !== false
				&&
				! preg_match( '/type\s*=\s*["\'](?:text|application)\/(?:(?:x\-)?javascript|ecmascript|jscript)["\']|type\s*=\s*["\'](?:module)[ "\']/i', $matches['attr'] )
			) {
				return $matches[0];
			}

			$delay_attr = preg_replace( '/type=(["\'])(.*?)\1/i', 'data-rocket-$0', $matches['attr'], 1 );

			if ( null !== $delay_attr ) {
				$delay_js = preg_replace( '#' . preg_quote( $matches['attr'], '#' ) . '#i', $delay_attr, $matches[0], 1 );
			}
		}

		return preg_replace( '/<script/i', '<script type="rocketlazyloadscript"', $delay_js, 1 );
	}


	/**
	 * Move meta charset to head if not found to the top of page content.
	 *
	 * @since 3.9.4
	 *
	 * @param string $html Html content.
	 *
	 * @return string
	 */
	public function move_meta_charset_to_head( $html ): string {
		$meta_pattern = "#<meta[^h]*(http-equiv[^=]*=[^\'\"]*[\'\" ]Content-Type[\'\"][ ]*[^>]*|)(charset[^=]*=[ ]*[\'\" ]*[^\'\"> ][^\'\">]+[^\'\"> ][\'\" ]*|charset[^=]*=*[^\'\"> ][^\'\">]+[^\'\"> ])([^>]*|)>(?=.*</head>)#Usmi";

		if ( ! preg_match( $meta_pattern, $html, $matches ) ) {
			return $html;
		}

		$replaced_html = preg_replace( "$meta_pattern", '', $html );

		if ( empty( $replaced_html ) ) {
			return $html;
		}

		if ( preg_match( '/<head\b/i', $replaced_html ) ) {
			$replaced_html = preg_replace( '/(<head\b[^>]*?>)/i', "\${1}${matches[0]}", $replaced_html, 1 );

			if ( empty( $replaced_html ) ) {
				return $html;
			}

			return $replaced_html;
		}

		if ( preg_match( '/<html\b/i', $replaced_html ) ) {
			$replaced_html = preg_replace( '/(<html\b[^>]*?>)/i', "\${1}${matches[0]}", $replaced_html, 1 );

			if ( empty( $replaced_html ) ) {
				return $html;
			}

			return $replaced_html;
		}

		$replaced_html = preg_replace( '/(<\w+)/', "${matches[0]}\${1}", $replaced_html, 1 );

		if ( empty( $replaced_html ) ) {
			return $html;
		}

		return $replaced_html;
	}

	/**
	 * Get exclusions
	 *
	 * @return void
	 */
	private function set_exclusions() {
		$lists = $this->data_manager->get_lists();

		$this->excluded = isset( $lists->delay_js_exclusions ) ? $lists->delay_js_exclusions : [];
	}
}
