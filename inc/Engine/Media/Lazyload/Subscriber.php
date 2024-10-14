<?php

namespace WP_Rocket\Engine\Media\Lazyload;

use WP_Rocket\Dependencies\Minify\JS;
use WP_Rocket\Dependencies\RocketLazyload\Assets;
use WP_Rocket\Dependencies\RocketLazyload\Image;
use WP_Rocket\Dependencies\RocketLazyload\Iframe;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\RegexTrait;
use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Lazyload Subscriber
 *
 * @since 3.3
 */
class Subscriber implements Subscriber_Interface {
	use RegexTrait;
	use CanLazyloadTrait;

	const SCRIPT_VERSION = '17.8.3';

	/**
	 * Options_Data instance
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Assets instance
	 *
	 * @var Assets
	 */
	private $assets;

	/**
	 * Image instance
	 *
	 * @var Image
	 */
	private $image;

	/**
	 * Iframe instance
	 *
	 * @var Iframe
	 */
	private $iframe;

	/**
	 * Constructor
	 *
	 * @since 3.3
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
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @since  3.3
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'wp_footer'                                 => [
				[ 'insert_lazyload_script', PHP_INT_MAX ],
				[ 'insert_youtube_thumbnail_script', PHP_INT_MAX ],
			],
			'wp_head'                                   => [ 'insert_nojs_style', PHP_INT_MAX ],
			'wp_enqueue_scripts'                        => [ 'insert_youtube_thumbnail_style', PHP_INT_MAX ],
			'rocket_buffer'                             => [ 'lazyload', 18 ],
			'rocket_lazyload_html'                      => 'lazyload_responsive',
			'init'                                      => 'lazyload_smilies',
			'wp'                                        => 'deactivate_lazyload_on_specific_posts',
			'wp_lazy_loading_enabled'                   => [ 'maybe_disable_core_lazyload', 10, 2 ],
			'rocket_lazyload_excluded_attributes'       => 'add_exclusions',
			'rocket_lazyload_excluded_src'              => 'add_exclusions',
			'rocket_lazyload_iframe_excluded_patterns'  => 'add_exclusions',
			'rocket_lazyload_exclude_youtube_thumbnail' => 'add_exclusions',
		];
	}

	/**
	 * Inserts the lazyload script in the footer
	 *
	 * @since 3.3
	 *
	 * @return void
	 */
	public function insert_lazyload_script() {
		if ( ! $this->can_lazyload_images() && ! $this->can_lazyload_iframes() ) {
			return;
		}

		if ( ! $this->should_lazyload() ) {
			return;
		}

		$script_args = [
			'base_url' => rocket_get_constant( 'WP_ROCKET_ASSETS_JS_URL' ) . 'lazyload/',
			'version'  => self::SCRIPT_VERSION,
		];

		$this->add_inline_script();
		$this->assets->insertLazyloadScript( $script_args );
	}

	/**
	 * Adds the inline lazyload script
	 *
	 * @since 3.6
	 *
	 * @return void
	 */
	private function add_inline_script() {
		$inline_script = $this->assets->getInlineLazyloadScript( $this->set_inline_script_args() );

		if ( ! rocket_get_constant( 'SCRIPT_DEBUG' ) ) {
			$inline_script = $this->minify_script( $inline_script );
		}

		echo '<script>' . $inline_script . '</script>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}

	/**
	 * Sets the arguments array for the inline lazyload script
	 *
	 * @since 3.6
	 *
	 * @return array
	 */
	private function set_inline_script_args() {
		/**
		 * Filters the threshold at which lazyload is triggered
		 *
		 * @since 1.2
		 *
		 * @param int $threshold Threshold value.
		 */
		$threshold = (int) apply_filters( 'rocket_lazyload_threshold', 300 );

		$inline_args = [
			'threshold' => $threshold,
		];

		/**
		 * Filters the use of native lazyload
		 *
		 * @since 3.4
		 *
		 * @param bool $use_native True to use native lazyload, false otherwise.
		 */
		if ( (bool) apply_filters( 'rocket_use_native_lazyload', false ) ) {
			$inline_args['options']['use_native'] = true;
			$inline_args['elements']['loading']   = '[loading=lazy]';
		}

		if ( $this->options->get( 'lazyload', 0 ) ) {
			if ( ! $this->is_native_images() ) {
				$inline_args['elements']['image'] = 'img[data-lazy-src]';
			}

			$inline_args['elements']['background_image'] = '.rocket-lazyload';
		}

		if ( (bool) $this->options->get( 'lazyload_iframes', 0 ) ) {
			$inline_args['elements']['iframe'] = 'iframe[data-lazy-src]';
		}

		/**
		 * Filters the arguments array for the lazyload script options
		 *
		 * @since 3.3
		 *
		 * @param array $inline_args Arguments used for the lazyload script options.
		 */
		return (array) apply_filters( 'rocket_lazyload_script_args', $inline_args );
	}

	/**
	 * Minifies the inline script
	 *
	 * @since 3.6
	 *
	 * @param string $script Inline script to minify.
	 * @return string
	 */
	private function minify_script( $script ) {
		$minify = new JS( $script );

		return $minify->minify();
	}

	/**
	 * Inserts the Youtube thumbnail script in the footer
	 *
	 * @since 3.3
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
		 * @deprecated 3.3
		 *
		 * @param string $thumbnail_resolution The resolution of the thumbnail. Accepted values: default, mqdefault, hqdefault, sddefault, maxresdefault
		 */
		$thumbnail_resolution = apply_filters_deprecated( 'rocket_youtube_thumbnail_resolution', [ 'hqdefault' ], '3.3', 'rocket_lazyload_youtube_thumbnail_resolution' );

		/**
		 * Filters the resolution of the YouTube thumbnail
		 *
		 * @since 1.4.8
		 *
		 * @param string $thumbnail_resolution The resolution of the thumbnail. Accepted values: default, mqdefault, hqdefault, sddefault, maxresdefault
		 */
		$thumbnail_resolution = apply_filters( 'rocket_lazyload_youtube_thumbnail_resolution', $thumbnail_resolution );

		/**
		 * Extension from the thumbnail from Youtube video.
		 *
		 * @param string $extension extension from the thumbnail from Youtube video.
		 * @returns string
		 */
		$extension = wpm_apply_filters_typed( 'string', 'rocket_lazyload_youtube_thumbnail_extension', 'jpg' );

		if ( ! in_array( $extension, [ 'jpg', 'webp' ], true ) ) {
			$extension = 'jpg';
		}

		$this->assets->insertYoutubeThumbnailScript(
			[
				'resolution' => $thumbnail_resolution,
				'lazy_image' => (bool) $this->options->get( 'lazyload' ),
				'native'     => $this->is_native_images(),
				'extension'  => $extension,
			]
		);
	}

	/**
	 * Inserts the no JS CSS compatibility in the header
	 *
	 * @since 3.3
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

		if ( ! $this->options->get( 'lazyload' ) && ! $this->options->get( 'lazyload_iframes' ) ) {
			return;
		}

		$this->assets->insertNoJSCSS();
	}

	/**
	 * Inserts the Youtube thumbnail CSS in the header
	 *
	 * @since 3.3
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
				'base_url'          => WP_ROCKET_ASSETS_URL,
				'responsive_embeds' => current_theme_supports( 'responsive-embeds' ),
			]
		);
	}

	/**
	 * Applies lazyload on the provided content
	 *
	 * @since 3.3
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	public function lazyload( $html ) {
		if ( ! $this->should_lazyload() ) {
			return $html;
		}

		$buffer = $this->hide_scripts( $html );
		$buffer = $this->hide_noscripts( $buffer );

		if ( $this->can_lazyload_iframes() ) {
			$args = [
				'youtube' => $this->options->get( 'lazyload_youtube' ),
			];

			$html = $this->iframe->lazyloadIframes( $html, $buffer, $args );
		}

		if ( $this->can_lazyload_images() ) {
			if ( ! $this->is_native_images() ) {
				$html = $this->image->lazyloadPictures( $html, $buffer );

				$buffer = $this->hide_scripts( $html );
				$buffer = $this->hide_noscripts( $buffer );
			}

			$html = $this->image->lazyloadImages( $html, $buffer, $this->is_native_images() );

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
	 *
	 * @param string $html Image HTML.
	 * @return string
	 */
	public function lazyload_responsive( $html ) {
		if ( $this->is_native_images() ) {
			return $html;
		}

		return $this->image->lazyloadResponsiveAttributes( $html );
	}

	/**
	 * Applies lazyload on WordPress smilies
	 *
	 * @since 3.3
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
	 *
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
	 * Disable WP core lazyload if our images lazyload is active
	 *
	 * @since 3.5
	 *
	 * @param bool   $value Current value for the enabling variable.
	 * @param string $tag_name The tag name.
	 *
	 * @return bool
	 */
	public function maybe_disable_core_lazyload( $value, $tag_name ) {
		if ( false === $value || rocket_bypass() ) {
			return $value;
		}

		if ( 'img' === $tag_name ) {
			return ! (bool) $this->can_lazyload_images();
		}

		if ( 'iframe' === $tag_name ) {
			return ! (bool) $this->can_lazyload_iframes();
		}

		return $value;
	}

	/**
	 * Adds the exclusions from the options to the exclusions arrays
	 *
	 * @since 3.8
	 *
	 * @param array $exclusions Array of excluded patterns.
	 * @return array
	 */
	public function add_exclusions( array $exclusions = [] ): array {
		$exclude_lazyload = $this->options->get( 'exclude_lazyload', [] );

		if ( empty( $exclude_lazyload ) ) {
			return $exclusions;
		}

		return array_unique( array_merge( $exclusions, $exclude_lazyload ) );
	}

	/**
	 * Checks if we can lazyload images.
	 *
	 * @since 3.3
	 *
	 * @return boolean
	 */
	private function can_lazyload_images() {
		if ( ! $this->options->get( 'lazyload', 0 ) ) {
			return false;
		}

		/**
		 * Filters the lazyload application on images
		 *
		 * @since 2.0
		 *
		 * @param bool $do_rocket_lazyload True to apply lazyload, false otherwise.
		 */
		return apply_filters( 'do_rocket_lazyload', true ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	}

	/**
	 * Checks if we can lazyload iframes
	 *
	 * @since 3.3
	 *
	 * @return boolean
	 */
	private function can_lazyload_iframes() {
		if ( ! $this->options->get( 'lazyload_iframes', 0 ) ) {
			return false;
		}

		/**
		 * Filters the lazyload application on iframes
		 *
		 * @since 2.0
		 *
		 * @param bool $do_rocket_lazyload_iframes True to apply lazyload, false otherwise.
		 */
		return apply_filters( 'do_rocket_lazyload_iframes', true ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	}

	/**
	 * Checks if native lazyload is enabled for images
	 *
	 * @since 3.10.2
	 *
	 * @return bool
	 */
	private function is_native_images(): bool {
		/**
		 * Filters the use of native lazyload for images
		 *
		 * @since 3.10.2
		 *
		 * @param bool $use_native True to use native lazyload for images, false otherwise.
		 */
		return (bool) apply_filters( 'rocket_use_native_lazyload_images', false );
	}
}
