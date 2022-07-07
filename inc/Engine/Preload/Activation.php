<?php

namespace WP_Rocket\Engine\Preload;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Activation\ActivationInterface;
use WP_Rocket\Engine\Preload\Controller\LoadInitialSitemap;

class Activation implements ActivationInterface {


	/**
	 * Controller to load initial tasks.
	 *
	 * @var LoadInitialSitemap
	 */
	protected $controller;

	/**
	 * Options.
	 *
	 * @var Options_Data
	 */
	protected $options;

	/**
	 * Instantiate class.
	 *
	 * @param LoadInitialSitemap $controller Controller to load initial tasks.
	 * @param Options_Data       $options Options.
	 */
	public function __construct( LoadInitialSitemap $controller, Options_Data $options ) {
		$this->controller = $controller;
		$this->options    = $options;
	}

	/**
	 * Launch preload on activation.
	 */
	public function activate() {
		if ( ! $this->options->get( 'manual_preload', false ) ) {
			return;
		}
		$this->controller->load_initial_sitemap();
	}
}
