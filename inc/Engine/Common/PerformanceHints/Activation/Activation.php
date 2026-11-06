<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Common\PerformanceHints\Activation;

use WP_Rocket\Engine\Activation\ActivationInterface;
use WP_Rocket\Engine\Common\PerformanceHints\WarmUp\Controller;

class Activation implements ActivationInterface {
	/**
	 * WarmUp controller
	 *
	 * @var Controller
	 */
	private $controller;

	/**
	 * Array of factories.
	 *
	 * @var array
	 */
	private $factories;

	/**
	 * Instantiate class.
	 *
	 * @param Controller $controller Controller instance.
	 * @param array      $factories Array of factories.
	 */
	public function __construct( Controller $controller, array $factories ) {
		$this->controller = $controller;
		$this->factories  = $factories;
	}

	/**
	 * Add actions on activation.
	 */
	public function activate() {
		add_action( 'rocket_after_activation', [ $this, 'warm_up' ] );
	}

	/**
	 * Start warm up.
	 *
	 * @return void
	 */
	public function warm_up() {
		if ( empty( $this->factories ) ) {
			return;
		}

		$this->controller->warm_up_home();
	}
}
