<?php

namespace WP_Rocket\Engine\Optimization\Minify\JS;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\AbstractOptimization;
use WP_Rocket\Engine\Optimization\AssetsLocalCache;

/**
 * Abstract class for JS optimization
 *
 * @since  3.1
 */
abstract class AbstractJSOptimization extends AbstractOptimization {
	const FILE_TYPE = 'js';

	/**
	 * Assets local cache instance
	 *
	 * @since 3.1
	 *
	 * @var AssetsLocalCache
	 */
	protected $local_cache;

	/**
	 * Creates an instance of inheriting class.
	 *
	 * @since  3.1
	 *
	 * @param Options_Data     $options            Options instance.
	 * @param AssetsLocalCache $local_cache Assets local cache instance.
	 */
	public function __construct( Options_Data $options, AssetsLocalCache $local_cache ) {
		$this->options        = $options;
		$this->local_cache    = $local_cache;
		$this->minify_key     = $this->options->get( 'minify_js_key', create_rocket_uniqid() );
		$this->excluded_files = $this->get_excluded_files();
		$this->init_base_path_and_url();
	}

	/**
	 * Get all files to exclude from minification/concatenation.
	 *
	 * @since  2.11
	 *
	 * @return string A list of files to exclude, ready to be used in a regex pattern.
	 */
	protected function get_excluded_files() {
		$excluded_files   = $this->options->get( 'exclude_js', [] );
		$excluded_files[] = '/wp-includes/js/dist/i18n.min.js';
		$excluded_files[] = '/interactive-3d-flipbook-powered-physics-engine/assets/js/html2canvas.min.js';
		$excluded_files[] = '/interactive-3d-flipbook-powered-physics-engine/assets/js/pdf.min.js';
		$excluded_files[] = '/interactive-3d-flipbook-powered-physics-engine/assets/js/three.min.js';
		$excluded_files[] = '/interactive-3d-flipbook-powered-physics-engine/assets/js/3d-flip-book.min.js';

		/**
		 * Filter JS files to exclude from minification/concatenation.
		 *
		 * @since 2.6
		 *
		 * @param array $js_files List of excluded JS files.
		 */
		$excluded_files = (array) apply_filters( 'rocket_exclude_js', $excluded_files );

		if ( empty( $excluded_files ) ) {
			return '';
		}

		foreach ( $excluded_files as $i => $excluded_file ) {
			// Escape characters for future use in regex pattern.
			$excluded_files[ $i ] = str_replace( '#', '\#', $excluded_file );
		}

		return implode( '|', $excluded_files );
	}

	/**
	 * Returns the CDN zones.
	 *
	 * @since  3.1
	 *
	 * @return array
	 */
	public function get_zones() {
		return [ 'all', 'css_and_js', self::FILE_TYPE ];
	}

	/**
	 * Determines if it is a file excluded from minification.
	 *
	 * @since  2.11
	 *
	 * @param array $tag Tag corresponding to a JS file.
	 *
	 * @return bool True if it is a file excluded, false otherwise
	 */
	protected function is_minify_excluded_file( array $tag ) {
		if ( ! isset( $tag[0], $tag['url'] ) ) {
			return true;
		}

		// File should not be minified.
		if (
			false !== strpos( $tag[0], 'data-minify=' )
			||
			false !== strpos( $tag[0], 'data-no-minify=' )
		) {
			return true;
		}

		$file_path = wp_parse_url( $tag['url'], PHP_URL_PATH );

		// File extension is not js.
		if ( pathinfo( $file_path, PATHINFO_EXTENSION ) !== self::FILE_TYPE ) {
			return true;
		}

		if ( ! empty( $this->excluded_files ) ) {
			// File is excluded from minification/concatenation.
			if ( preg_match( '#(' . $this->excluded_files . ')#', $file_path ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Gets the minify URL.
	 *
	 * @since  3.1
	 *
	 * @param string $filename     Minified filename.
	 * @param string $original_url Original URL for this file. Optional.
	 *
	 * @return string
	 */
	protected function get_minify_url( $filename, $original_url = '' ) {
		$minify_url = $this->minify_base_url . $filename;

		/**
		 * Filters JS file URL with CDN hostname
		 *
		 * @since 2.1
		 *
		 * @param string $minify_url   Minified file URL.
		 * @param string $original_url Original URL for this file.
		 */
		return apply_filters( 'rocket_js_url', $minify_url, $original_url );
	}

	/**
	 * Patterns in URL excluded from being combined
	 *
	 * @since 3.1
	 *
	 * @return array
	 */
	protected function get_excluded_external_file_path() {
		$defaults = [
			'html5.js',
			'show_ads.js',
			'histats.com/js',
			'ws.amazon.com/widgets',
			'/ads/',
			'intensedebate.com',
			'scripts.chitika.net/',
			'jotform.com/',
			'gist.github.com',
			'forms.aweber.com',
			'video.unrulymedia.com',
			'stats.wp.com',
			'stats.wordpress.com',
			'widget.rafflecopter.com',
			'widget-prime.rafflecopter.com',
			'releases.flowplayer.org',
			'c.ad6media.fr',
			'cdn.stickyadstv.com',
			'www.smava.de',
			'contextual.media.net',
			'app.getresponse.com',
			'adserver.reklamstore.com',
			's0.wp.com',
			'wprp.zemanta.com',
			'files.bannersnack.com',
			'smarticon.geotrust.com',
			'js.gleam.io',
			'ir-na.amazon-adsystem.com',
			'web.ventunotech.com',
			'verify.authorize.net',
			'ads.themoneytizer.com',
			'embed.finanzcheck.de',
			'imagesrv.adition.com',
			'js.juicyads.com',
			'form.jotformeu.com',
			'speakerdeck.com',
			'content.jwplatform.com',
			'ads.investingchannel.com',
			'app.ecwid.com',
			'www.industriejobs.de',
			's.gravatar.com',
			'googlesyndication.com',
			'a.optmstr.com',
			'a.optmnstr.com',
			'a.opmnstr.com',
			'adthrive.com',
			'mediavine.com',
			'js.hsforms.net',
			'googleadservices.com',
			'f.convertkit.com',
			'recaptcha/api.js',
			'mailmunch.co',
			'apps.shareaholic.com',
			'dsms0mj1bbhn4.cloudfront.net',
			'nutrifox.com',
			'code.tidio.co',
			'www.uplaunch.com',
			'widget.reviewability.com',
			'embed-cdn.gettyimages.com/widgets.js',
			'app.mailerlite.com',
			'ck.page',
			'cdn.jsdelivr.net/gh/AmauriC/',
			'static.klaviyo.com/onsite/js/klaviyo.js',
			'a.omappapi.com/app/js/api.min.js',
			'static.zdassets.com',
			'feedbackcompany.com/widgets/feedback-company-widget.min.js',
			'widget.gleamjs.io',
			'phonewagon.com',
			'simplybook.asia/v2/widget/widget.js',
			'simplybook.it/v2/widget/widget.js',
			'simplybook.me/v2/widget/widget.js',
			'static.botsrv.com/website/js/widget2.36cf1446.js',
			'static.mailerlite.com/data/',
			'cdn.voxpow.com',
			'loader.knack.com',
			'embed.lpcontent.net/leadboxes/current/embed.js',
			'cc.cdn.civiccomputing.com/9/cookieControl-9.x.min.js',
			'cse.google.com/cse.js',
			'kit.fontawesome.com',
			'cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js',
			'static.leadpages.net/leadbars/current/embed.js',
			'booqable.com/v2/booqable.js',
			'googleoptimize.com',
			'cdna.hubpeople.com/js/widget_standalone_two_modes.js',
		];

		$excluded_external = array_merge( $defaults, $this->options->get( 'exclude_js', [] ) );

		/**
		 * Filters JS externals files to exclude from the combine process
		 *
		 * @since 2.2
		 *
		 * @param array $pattern Patterns to match.
		 */
		return apply_filters( 'rocket_minify_excluded_external_js', $excluded_external );
	}
}
