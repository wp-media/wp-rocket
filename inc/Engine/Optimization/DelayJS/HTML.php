<?php

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\AbstractOptimization;

class HTML extends AbstractOptimization {

	/**
	 * Plugin options instance.
	 *
	 * @since  3.7
	 *
	 * @var Options_Data
	 */
	protected $options;

	/**
	* Creates an instance of HTML.
	*
	* @since  3.7
	*
	* @param Options_Data $options Plugin options instance.
	*/
	public function __construct( Options_Data $options ) {
		$this->options      = $options;
	}

	public function delay_js( $html ) {
		if ( ! $this->is_allowed() ) {
			return $html;
		}

		//find inline scripts

		//
	}

	/**
	 * Checks if is allowed to Delay JS.
	 *
	 * @since 3.7
	 *
	 * @return bool
	 */
	private function is_allowed() {
		if ( rocket_get_constant( 'DONOTROCKETOPTIMIZE' ) ) {
			return false;
		}

		if ( rocket_get_constant( 'DONOTDELAYJS' ) ) {
			return false;
		}

		if ( ! $this->options->get( 'delay_js' ) ) {
			return false;
		}

		return true;
	}

	private function find_scripts( $html ) {
		$regex_pattern = '<script\s*(?<attr>[^>]*)?>(?<content>.*)?<\/script>';
		preg_replace_callback( '/' . $regex_pattern . '/si', [ $this, 'replace_scripts' ], $html );


	}

	public function replace_scripts( $matches ) {

	}

}
