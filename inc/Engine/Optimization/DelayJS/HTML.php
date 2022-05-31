<?php
declare( strict_types=1 );

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
		'\/lazysizes(\.min|-pre|-post)?\.js', // lazyload library (used in EWWW, Autoptimize, Avada).
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
		'/ewww-image-optimizer/includes/load[_-]webp(\.min)?.js', // EWWW WebP rewrite external script.
		'/ewww-image-optimizer/includes/check-webp(\.min)?.js', // EWWW WebP check external script.
		'ewww_webp_supported', // EWWW WebP inline scripts.
		'/dist/js/browser-redirect/app.js', // WPML browser redirect script.
		'/perfmatters/js/lazyload.min.js',
		'lazyLoadInstance',
		'scripts.mediavine.com/tags/', // allows mediavine-video schema to be accessible by search engines.
		'initCubePortfolio', // Cube Portfolio show images.
		'simpli.fi', // simpli.fi Advertising Platform scripts.
		'gforms_recaptcha_', // Gravity Forms recaptcha.
		'/jetpack-boost/vendor/automattic/jetpack-lazy-images/(.*)', // Jetpack Boost plugin lazyload.
		'jetpack-lazy-images-js-enabled',  // Jetpack Boost plugin lazyload.
		'jetpack-boost-critical-css', // Jetpack Boost plugin critical CSS.
		'wpformsRecaptchaCallback', // WPForms reCAPTCHA v2.
		'booking-suedtirol-js', // bookingsuedtirol.com widgets.
		'/gravityforms/js/conditional_logic.min.js', // Gravity forms conditions.
		'statcounter.com/counter/counter.js', // StatsCounter.
		'var sc_project', // Statscounter.
		'/jetpack/jetpack_vendor/automattic/jetpack-lazy-images/(.*)', // Jetpack plugin lazyload.
		'/themify-builder/themify/js/modules/fallback(\.min)?.js',
		'handlePixMessage',
		'var corner_video',
		'cdn.pixfuture.com/hb_v2.js',
		'cdn.pixfuture.com/pbix.js',
		'served-by.pixfuture.com/www/delivery/ads.js',
		'served-by.pixfuture.com/www/delivery/headerbid_sticky_refresh.js',
		'serv-vdo.pixfuture.com/vpaid/ads.js',
		'wprRemoveCPCSS',
		'window.jdgmSettings', // Judge.me plugin.
		'/photonic/include/js/front-end/nomodule/photonic-baguettebox.min.js', // Photonic plugin.
		'/photonic/include/ext/baguettebox/baguettebox.min.js', // Photonic plugin.
		'window.wsf_form_json_config', // WSF Form plugin.
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
			function ( $value ) {
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
		$replaced_html = preg_replace_callback(
			'/<\s*script\s*(?<attr>[^>]*?)?>(?<content>.*?)?<\s*\/\s*script\s*>/ims',
			[
				$this,
				'replace_scripts',
			],
			$html
		);

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
}
