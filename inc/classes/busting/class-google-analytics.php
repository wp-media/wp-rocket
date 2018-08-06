<?php
namespace WP_Rocket\Busting;

/**
 * Manages the cache busting of the Google Analytics file
 *
 * @since 3.1
 * @author Remy Perona
 */
class Google_Analytics extends Abstract_Busting {
	/**
	 * Google Analytics URL
	 *
	 * @var string;
	 */
	protected $url;

	/**
	 * Flag to track the replacement
	 *
	 * @var bool
	 */
	protected $is_replaced;

	/**
	 * {@inheritdoc}
	 */
	public function __construct( $busting_path, $busting_url ) {
		$this->busting_path = $busting_path . 'google-tracking/';
		$this->busting_url  = $busting_url . 'google-tracking/';
		$this->is_replaced  = false;
		$this->filename     = 'ga-local.js';
		$this->url          = 'https://www.google-analytics.com/analytics.js';
	}

	/**
	 * {@inheritdoc}
	 */
	public function replace_url( $html ) {
		$tag = $this->find( '<script[^>]*?>(.*)<\/script>', $html );

		if ( ! $tag ) {
			return $html;
		}

		if ( ! $this->save( $this->url ) ) {
			return $html;
		}

		$replace_tag = preg_replace( '/(?:https?:)?\/\/www\.google-analytics\.com\/analytics\.js/i', $this->get_busting_url(), $tag );
		$html        = str_replace( $tag, $replace_tag, $html );

		$this->is_replaced = true;

		return $html;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function find( $pattern, $html ) {
		preg_match_all( '/' . $pattern . '/Umsi', $html, $matches, PREG_SET_ORDER );

		if ( empty( $matches ) ) {
			return false;
		}

		$matches = array_map( function( $match ) {
			if ( false === \strpos( $match[1], 'GoogleAnalyticsObject' ) ) {
				return;
			}

			return $match[0];
		}, $matches );

		$matches = array_values( array_filter( $matches ) );

		if ( empty( $matches ) ) {
			return false;
		}

		return $matches[0];
	}

	/**
	 * Returns if the replacement was sucessful or not
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @return boolean
	 */
	public function is_replaced() {
		return $this->is_replaced;
	}

	/**
	 * Deletes the GA busting file
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @return bool
	 */
	public function delete() {
		$file = $this->busting_path . $this->filename;

		return \rocket_direct_filesystem()->delete( $file, false, 'f' );
	}

	/**
	 * Gets the Google Analytics URL
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @return string
	 */
	public function get_url() {
		return $this->url;
	}
}
