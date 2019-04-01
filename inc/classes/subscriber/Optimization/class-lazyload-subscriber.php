<?php
/**
 * Lazyload subscriber
 *
 * @package WP_Rocket
 */

namespace WP_Rocket\Subscriber\Optimization;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Admin\Options_Data;
use RocketLazyload\Assets;
use RocketLazyload\Image;
use RocketLazyload\Iframe;

/**
 * Lazyload Subscriber
 *
 * @since 3.3
 * @author Remy Perona
 */
class Lazyload_Subscriber implements Subscriber_Interface {
	/**
	 * Options_Data instance
	 *
	 * @since 2.0
	 * @author Remy Perona
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Assets instance
	 *
	 * @since 2.0
	 * @author Remy Perona
	 *
	 * @var Assets
	 */
	private $assets;

	/**
	 * Image instance
	 *
	 * @since 2.0
	 * @author Remy Perona
	 *
	 * @var Image
	 */
	private $image;

	/**
	 * Iframe instance
	 *
	 * @since 2.0
	 * @author Remy Perona
	 *
	 * @var Iframe
	 */
	private $iframe;

	/**
	 * Constructor
	 *
	 * @since 2.0
	 * @author Remy Perona
	 *
	 * @param Options_Data $options Options_Data instance.
	 * @param Assets       $assets Assets instance.
	 * @param Image        $image Image instance.
	 * @param Iframe       $iframe Iframe instance.
	 */
	public function __construct( Options_Data $options, Assets $assets, Image $image, Iframe $iframe ) {
		$this->options = $options;
		$this->assets  = $assets;
		$this->image   = $image;
		$this->iframe  = $iframe;
	}

	/**
	 * @inheritDoc
	 */
	public static function get_subscribed_events() {
		return [
			'wp_footer'            => [
				[ 'insert_lazyload_script', PHP_INT_MAX ],
				[ 'insert_youtube_thumbnail_script', PHP_INT_MAX ],
			],
			'wp_head'              => [ 'insert_nojs_style', PHP_INT_MAX ],
			'wp_enqueue_scripts'   => [ 'insert_youtube_thumbnail_style', PHP_INT_MAX ],
			'rocket_buffer'        => [ 'lazyload', 25 ],
			'rocket_lazyload_html' => 'lazyload_responsive',
			'init'                 => 'lazyload_smilies',
			'wp'                   => 'deactivate_lazyload_on_specific_posts',
		];
	}

	/**
	 * Inserts the lazyload script in the footer
	 *
	 * @since 3.3
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function insert_lazyload_script() {
		if ( ! $this->options->get( 'lazyload' ) && ! $this->options->get( 'lazyload_iframes' ) ) {
			return;
		}

		if ( ! $this->can_lazyload_images() && ! $this->can_lazyload_iframes() ) {
			return;
		}

		if ( ! $this->should_lazyload() ) {
			return;
		}

		/**
		 * Filters the threshold at which lazyload is triggered
		 *
		 * @since 1.2
		 * @author Remy Perona
		 *
		 * @param int $threshold Threshold value.
		 */
		$threshold = apply_filters( 'rocket_lazyload_threshold', 300 );

		/**
		 * Filters the use of the polyfill for intersectionObserver
		 *
		 * @since 3.3
		 * @author Remy Perona
		 *
		 * @param bool $polyfill True to use the polyfill, false otherwise.
		 */
		$polyfill = apply_filters( 'rocket_lazyload_polyfill', false );

		$args = [
			'base_url'  => get_rocket_cdn_url( WP_ROCKET_ASSETS_JS_URL . 'lazyload/' ),
			'threshold' => $threshold,
			'version'   => '11.0.3',
			'polyfill'  => $polyfill,
		];

		if ( $this->options->get( 'lazyload' ) ) {
			$args['elements']['image']            = 'img[data-lazy-src]';
			$args['elements']['background_image'] = '.rocket-lazyload';
		}

		if ( $this->options->get( 'lazyload_iframes' ) ) {
			$args['elements']['iframe'] = 'iframe[data-lazy-src]';
		}

		/**
		 * Filters the arguments array for the lazyload script options
		 *
		 * @since 3.3
		 * @author Remy Perona
		 *
		 * @param array $args Arguments used for the lazyload script options.
		 */
		$args = apply_filters( 'rocket_lazyload_script_args', $args );

		$this->assets->insertLazyloadScript( $args );
	}

	/**
	 * Inserts the Youtube thumbnail script in the footer
	 *
	 * @since 2.0
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function insert_youtube_thumbnail_script() {
		if ( ! $this->options->get( 'lazyload_youtube' ) || ! $this->can_lazyload_iframes() ) {
			return;
		}

		if ( ! $this->should_lazyload() ) {
			return;
		}

		/**
		 * Filters the resolution of the YouTube thumbnail
		 *
		 * @since 1.4.8
		 * @author Arun Basil Lal
		 *
		 * @param string $thumbnail_resolution The resolution of the thumbnail. Accepted values: default, mqdefault, hqdefault, sddefault, maxresdefault
		 */
		$thumbnail_resolution = apply_filters( 'rocket_lazyload_youtube_thumbnail_resolution', 'hqdefault' );

		$this->assets->insertYoutubeThumbnailScript(
			[
				'resolution' => $thumbnail_resolution,
				'lazy_image' => (bool) $this->options->get( 'lazyload' ),
			]
		);
	}

	/**
	 * Inserts the no JS CSS compatibility in the header
	 *
	 * @since 3.3
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function insert_nojs_style() {
		if ( ! $this->should_lazyload() ) {
			return;
		}

		if ( ! $this->can_lazyload_images() && ! $this->can_lazyload_iframes() ) {
			return;
		}

		$this->assets->insertNoJSCSS();
	}

	/**
	 * Inserts the Youtube thumbnail CSS in the header
	 *
	 * @since 3.3
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function insert_youtube_thumbnail_style() {
		if ( ! $this->options->get( 'lazyload_youtube' ) || ! $this->can_lazyload_iframes() ) {
			return;
		}

		if ( ! $this->should_lazyload() ) {
			return;
		}

		$this->assets->insertYoutubeThumbnailCSS(
			[
				'base_url' => get_rocket_cdn_url( WP_ROCKET_ASSETS_URL ),
			]
		);
	}

	/**
	 * Checks if lazyload should be applied
	 *
	 * @since 2.0
	 * @author Remy Perona
	 *
	 * @return bool
	 */
	private function should_lazyload() {
		if ( is_admin() || is_feed() || is_preview() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || ( defined( 'DONOTLAZYLOAD' ) && DONOTLAZYLOAD ) ) {
			return false;
		}

		if ( defined( 'DONOTROCKETOPTIMIZE' ) && DONOTROCKETOPTIMIZE ) {
			return false;
		}

		// Exclude Page Builders editors.
		$excluded_parameters = [
			'fl_builder',
			'et_fb',
			'ct_builder',
		];

		foreach ( $excluded_parameters as $excluded ) {
			if ( isset( $_GET[ $excluded ] ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Applies lazyload on the provided content
	 *
	 * @since 3.3
	 * @author Remy Perona
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	public function lazyload( $html ) {
		if ( ! $this->should_lazyload() ) {
			return $html;
		}

		$buffer = $this->ignore_scripts( $html );
		$buffer = $this->ignore_noscripts( $buffer );

		if ( $this->options->get( 'lazyload_iframes' ) && $this->can_lazyload_iframes() ) {
			$args = [
				'youtube' => $this->options->get( 'lazyload_youtube' ),
			];

			$html = $this->iframe->lazyloadIframes( $html, $buffer, $args );
		}

		if ( $this->options->get( 'lazyload' ) && $this->can_lazyload_images() ) {
			$html = $this->image->lazyloadImages( $html, $buffer );
			$html = $this->image->lazyloadPictures( $html, $buffer );

			/**
			 * Filters the application of lazyload on background images
			 *
			 * @since 3.3
			 *
			 * @param bool $lazyload True to apply, false otherwise.
			 */
			if ( apply_filters( 'rocket_lazyload_background_images', true ) ) {
				$html = $this->image->lazyloadBackgroundImages( $html, $buffer );
			}
		}

		return $html;
	}

	/**
	 * Applies lazyload on responsive images attributes srcset and sizes
	 *
	 * @since 3.3
	 * @author Remy Perona
	 *
	 * @param string $html Image HTML.
	 * @return string
	 */
	public function lazyload_responsive( $html ) {
		return $this->image->lazyloadResponsiveAttributes( $html );
	}

	/**
	 * Applies lazyload on WordPress smilies
	 *
	 * @since 3.3
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function lazyload_smilies() {
		if ( ! $this->should_lazyload() ) {
			return;
		}

		if ( ! $this->options->get( 'lazyload' ) ) {
			return;
		}

		$filters = [
			'the_content'  => 10,
			'the_excerpt'  => 10,
			'comment_text' => 20,
		];

		foreach ( $filters as $filter => $prio ) {
			if ( ! has_filter( $filter ) ) {
				continue;
			}

			remove_filter( $filter, 'convert_smilies', $prio );
			add_filter( $filter, [ $this->image, 'convertSmilies' ], $prio );
		}
	}

	/**
	 * Prevents lazyload if the option is unchecked on the WP Rocket options metabox for a post
	 *
	 * @since 3.3
	 * @return void
	 */
	public function deactivate_lazyload_on_specific_posts() {
		if ( is_rocket_post_excluded_option( 'lazyload' ) ) {
			add_filter( 'do_rocket_lazyload', '__return_false' );
		}

		if ( is_rocket_post_excluded_option( 'lazyload_iframes' ) ) {
			add_filter( 'do_rocket_lazyload_iframes', '__return_false' );
		}
	}

	/**
	 * Remove inline scripts from the HTML to parse
	 *
	 * @since 3.3
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	private function ignore_scripts( $html ) {
		return preg_replace( '/<script\b(?:[^>]*)>(?:.+)?<\/script>/Umsi', '', $html );
	}

	/**
	 * Checks if we can lazyload images.
	 *
	 * @since 3.3
	 *
	 * @return boolean
	 */
	private function can_lazyload_images() {
		/**
		 * Filters the lazyload application on images
		 *
		 * @since 2.0
		 * @author Remy Perona
		 *
		 * @param bool $do_rocket_lazyload True to apply lazyload, false otherwise.
		 */
		return apply_filters( 'do_rocket_lazyload', true ); // WPCS: prefix ok.
	}

	/**
	 * Checks if we can lazyload iframes
	 *
	 * @since 3.3
	 *
	 * @return boolean
	 */
	private function can_lazyload_iframes() {
		/**
		 * Filters the lazyload application on iframes
		 *
		 * @since 2.0
		 * @author Remy Perona
		 *
		 * @param bool $do_rocket_lazyload_iframes True to apply lazyload, false otherwise.
		 */
		return apply_filters( 'do_rocket_lazyload_iframes', true ); // WPCS: prefix ok.
	}

	/**
	 * Remove noscript tags from the HTML to parse
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	private function ignore_noscripts( $html ) {
		return preg_replace( '#<noscript>(?:.+)</noscript>#Umsi', '', $html );
	}
}
