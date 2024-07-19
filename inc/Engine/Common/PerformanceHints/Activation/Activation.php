<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Common\PerformanceHints\Activation;

use WP_Rocket\Engine\Activation\ActivationInterface;
use WP_Rocket\Engine\Common\Context\ContextInterface;
use WP_Rocket\Engine\Media\AboveTheFold\WarmUp\Controller;

class Activation implements ActivationInterface {
	/**
	 * WarmUp controller
	 *
	 * @var Controller
	 */
	private $controller;

	/**
	 * ATF context.
	 *
	 * @var ContextInterface
	 */
	private $context;

	/**
	 * Instantiate class.
	 *
	 * @param Controller       $controller Controller instance.
	 * @param ContextInterface $context ATF Context.
	 */
	public function __construct( Controller $controller, ContextInterface $context ) {
		$this->controller = $controller;
		$this->context    = $context;
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
		if ( ! $this->context->is_allowed() ) {
			return;
		}

		$this->controller->warm_up_home();
	}
}
