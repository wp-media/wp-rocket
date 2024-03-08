<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\AboveTheFold\Activation;

use WP_Rocket\Engine\Activation\ActivationInterface;
use WP_Rocket\Engine\Media\AboveTheFold\WarmUp\Controller;
use WP_Rocket\Engine\Common\JobManager\Managers\ManagerInterface;

class Activation implements ActivationInterface {
	/**
	 * WarmUp controller
	 *
	 * @var Controller
	 */
	private $controller;

	/**
	 * Above the fold Job Manager.
	 *
	 * @var ManagerInterface
	 */
	private $manager;

	/**
	 * Instantiate class.
	 *
	 * @param Controller       $controller Controller instance.
	 * @param ManagerInterface $manager Above the fold Job Manager.
	 */
	public function __construct( Controller $controller, ManagerInterface $manager ) {
		$this->controller = $controller;
		$this->manager    = $manager;
	}

	/**
	 * Add actions on activation.
	 */
	public function activate() {
		add_action( 'rocket_after_activation', [ $this, 'fetch_links' ] );
	}

	/**
	 * Process links fetched from homepage.
	 *
	 * @return void
	 */
	public function process_links() {
		if ( ! $this->manager->is_allowed() ) {
			return;
		}

		$this->controller->process_links();
	}
}
