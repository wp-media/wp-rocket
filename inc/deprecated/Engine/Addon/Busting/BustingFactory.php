<?php
namespace WP_Rocket\Addon\Busting;

use WP_Rocket\deprecated\DeprecatedClassTrait;
use WP_Rocket\Addon\GoogleTracking\GoogleAnalytics;
use WP_Rocket\Addon\GoogleTracking\GoogleTagManager;
use WP_Rocket\Busting\Facebook_Pickles;
use WP_Rocket\Busting\Facebook_SDK;

/**
 * Busting classes Factory
 *
 * @since 3.9 deprecated.
 * @since 3.6.2
 */
class BustingFactory {
	use DeprecatedClassTrait;

	/**
	 * Base cache busting filepath.
	 *
	 * @var string
	 */
	private $busting_path;

	/**
	 * Base cache busting URL.
	 *
	 * @var string
	 */
	private $busting_url;

	/**
	 * Constructor
	 *
	 * @param string $busting_path Base cache busting filepath.
	 * @param string $busting_url Base cache busting URL.
	 */
	public function __construct( $busting_path, $busting_url ) {
		self::deprecated_class( '3.9' );
		$this->busting_path = $busting_path;
		$this->busting_url  = $busting_url;
	}

	/**
	 * Creator method
	 *
	 * @param string $type Type of busting class to create.
	 * @return Busting_Interface
	 */
	public function type( $type ) {
		switch ( $type ) {
			case 'fbpix':
				return new Facebook_Pickles( $this->busting_path, $this->busting_url );
			case 'fbsdk':
				return new Facebook_SDK( $this->busting_path, $this->busting_url );
			case 'ga':
				return new GoogleAnalytics( $this->busting_path, $this->busting_url );
			case 'gtm':
				return new GoogleTagManager( $this->busting_path, $this->busting_url, new GoogleAnalytics( $this->busting_path, $this->busting_url ) );
		}
	}
}
