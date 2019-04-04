<?php
namespace WP_Rocket\Busting;

/**
 * Busting classes Factory
 *
 * @since 3.1
 * @author Remy Perona
 */
class Busting_Factory {
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
				return new Google_Analytics( $this->busting_path, $this->busting_url );
			case 'gtm':
				return new Google_Tag_Manager( $this->busting_path, $this->busting_url, new Google_Analytics( $this->busting_path, $this->busting_url ) );
		}
	}
}
