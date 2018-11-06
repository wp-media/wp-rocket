<?php
namespace WP_Rocket\Subscriber\Optimization;

use WP_Rocket\Optimization\CDN_Favicons;
use WP_Rocket\Subscriber\Optimization\Minify_Subscriber;

/**
 * Hook into WordPress to use the CDN for favicons and "touch" icons.
 *
 * @since  3.2
 * @author Grégory Viguier
 */
class CDN_Favicons_Subscriber extends Minify_Subscriber {
	/**
	 * CDN_Favicons instance.
	 *
	 * @var    CDN_Favicons
	 * @since  3.2
	 * @author Grégory Viguier
	 */
	protected $cdn_favicons;

	/**
	 * Tell if the filter applied to the attachment URL has been removed.
	 *
	 * @var    bool
	 * @since  3.2
	 * @author Grégory Viguier
	 */
	protected $attachment_url_filter_removed;

	/**
	 * Constructor.
	 *
	 * @since  3.2
	 * @author Grégory Viguier
	 *
	 * @param CDN_Favicons $cdn_favicons CDN_Favicons instance.
	 */
	public function __construct( CDN_Favicons $cdn_favicons ) {
		$this->cdn_favicons = $cdn_favicons;
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @since  3.2
	 * @author Grégory Viguier
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_buffer' => [ 'process', 18 ],
			'wp_head'       => [
				[ 'maybe_remove_attachment_url_filter', 98 ],
				[ 'maybe_restore_attachment_url_filter', 100 ],
			],
		];
	}

	/**
	 * Filter the HTML to fetch favicons and change their URL to use the CDN.
	 *
	 * @since  3.2
	 * @author Grégory Viguier
	 *
	 * @param  string $html HTML content.
	 * @return string
	 */
	public function process( $html ) {
		if ( ! $this->is_allowed() ) {
			return $html;
		}

		return $this->cdn_favicons->add_cdn( $html );
	}

	/**
	 * If the site uses the site icon feature, remove our filter that adds the CDN to images from the library.
	 * This allows the class CDN_Favicons to properly handle exclusions.
	 *
	 * @since  3.2
	 * @see    rocket_cdn_file()
	 * @author Grégory Viguier
	 */
	public function maybe_remove_attachment_url_filter() {
		if ( ! \has_site_icon() && ! \is_customize_preview() ) {
			return;
		}

		$this->attachment_url_filter_removed = remove_filter( 'wp_get_attachment_url', 'rocket_cdn_file', PHP_INT_MAX );
	}

	/**
	 * If our filter that adds the CDN to images from the library has been removed earlier, add it back.
	 *
	 * @since  3.2
	 * @see    rocket_cdn_file()
	 * @author Grégory Viguier
	 */
	public function maybe_restore_attachment_url_filter() {
		if ( ! $this->attachment_url_filter_removed ) {
			return;
		}

		add_filter( 'wp_get_attachment_url', 'rocket_cdn_file', PHP_INT_MAX );
	}

	/**
	 * Get the CDN zones.
	 *
	 * @since  3.2
	 * @author Grégory Viguier
	 *
	 * @return array
	 */
	public function get_zones() {
		return $this->cdn_favicons->get_zones();
	}

	/**
	 * Tell if the CDN can be used.
	 *
	 * @since  3.2
	 * @author Grégory Viguier
	 *
	 * @return bool
	 */
	protected function is_allowed() {
		return $this->cdn_favicons->is_allowed();
	}
}
