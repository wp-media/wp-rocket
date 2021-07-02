<?php
declare(strict_types=1);

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
	 * Array of excluded patterns from delay JS
	 *
	 * @since 3.9
	 *
	 * @var array
	 */
	protected $excluded = [
		'nowprocket',
		'/wp-includes/js/wp-embed.min.js',
		'lazyLoadOptions',
		'lazyLoadThumb',
		'wp-rocket/assets/js/lazyload/(.*)',
		'et_core_page_resource_fallback',
		'window.\$us === undefined',
		'js-extra',
		'fusionNavIsCollapsed',
		'/assets/js/smush-lazy-load', // Smush & Smush Pro.
		'eio_lazy_vars',
		'/ewww-image-optimizer/includes/lazysizes.min.js',
		'/ewww-image-optimizer-cloud/includes/lazysizes.min.js',
		'document\.body\.classList\.remove\("no-js"\)',
		'document\.documentElement\.className\.replace\( \'no-js\', \'js\' \)',
		'et_animation_data',
		'wpforms_settings',
		'var nfForms',
		'//stats.wp.com', // Jetpack Stats.
		'_stq.push', // Jetpack Stats.
		'fluent_form_ff_form_instance_', // Fluent Forms.
		'cpLoadCSS', // Convert Pro.
		'ninja_column_', // Ninja Tables.
		'var rbs_gallery_', // Robo Gallery.
		'var lepopup_', // Green Popup.
		'var billing_additional_field', // Woo Autocomplete Nish.
		'var gtm4wp',
		'var dataLayer_content',
	];

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

		$this->excluded = array_merge( $this->excluded, $this->options->get( 'delay_js_exclusions', [] ) );

		/**
		 * Filters the delay JS exclusions array
		 *
		 * @since 3.9
		 *
		 * @param array $excluded Array of excluded patterns.
		 */
		$this->excluded = apply_filters( 'rocket_delay_js_exclusions', $this->excluded );
		$this->excluded = array_map(
			function( $value ) {
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
		$replaced_html = preg_replace_callback( '/<\s*script\s*(?<attr>[^>]*?)?>(?<content>.*?)?<\s*\/\s*script\s*>/ims', [ $this, 'replace_scripts' ], $html );

		if ( empty( $replaced_html ) ) {
			return $html;
		}

		return $replaced_html;
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
			if ( false !== strpos( $matches['attr'], 'application/ld+json' ) ) {
				return $matches[0];
			}

			$delay_attr = preg_replace( '/type=(["\'])(.*?)\1/i', 'data-rocket-$0', $matches['attr'], 1 );

			if ( null !== $delay_attr ) {
				$delay_js = preg_replace( '#' . preg_quote( $matches['attr'], '#' ) . '#i', $delay_attr, $matches[0], 1 );
			}
		}

		return preg_replace( '/<script/i', '<script type="rocketlazyloadscript"', $delay_js, 1 );
	}
}
