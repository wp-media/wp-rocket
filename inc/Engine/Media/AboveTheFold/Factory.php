<?php

declare(strict_types=1);

namespace WP_Rocket\Engine\Media\AboveTheFold;

use WP_Rocket\Engine\Common\PerformanceHints\FactoryInterface;
use WP_Rocket\Engine\Common\PerformanceHints\AJAX\ControllerInterface as AjaxControllerInterface;
use WP_Rocket\Engine\Common\PerformanceHints\Frontend\ControllerInterface as FrontendControllerInterface;
use WP_Rocket\Engine\Common\Context\ContextInterface;

class Factory implements FactoryInterface {

	/**
	 * Ajax Controller instance.
	 *
	 * @var AjaxControllerInterface
	 */
	protected $ajax_controller;

	/**
	 * Frontend Controller instance.
	 *
	 * @var FrontendControllerInterface
	 */
	protected $frontend_controller;

	/**
	 * Context instance.
	 *
	 * @var ContextInterface
	 */
	protected $context;

	/**
	 * Instatiate the class.
	 *
	 * @param AjaxControllerInterface     $ajax_controller ATF AJAX Controller instance.
	 * @param FrontendControllerInterface $frontend_controller ATF Frontend Controller instance.
	 * @param ContextInterface            $context ATF Context instance.
	 */
	public function __construct( AjaxControllerInterface $ajax_controller, FrontendControllerInterface $frontend_controller, ContextInterface $context ) {
		$this->ajax_controller     = $ajax_controller;
		$this->frontend_controller = $frontend_controller;
		$this->context             = $context;
	}

	/**
	 * Provides an Ajax controller object.
	 *
	 * @return AjaxControllerInterface
	 */
	public function get_ajax_controller(): AjaxControllerInterface {
		return $this->ajax_controller;
	}

	/**
	 * Provides a Frontend object.
	 *
	 * @return FrontendControllerInterface
	 */
	public function get_frontend_controller(): FrontendControllerInterface {
		return $this->frontend_controller;
	}

	/**
	 * Provides a Table object.
	 */
	public function table() {
		// Return Table object.
	}

	/**
	 * Provides a Queries object.
	 */
	public function queries() {
		// Return Queries object.
	}

	/**
	 * Provides a Context object.
	 *
	 * @return ContextInterface
	 */
	public function get_context(): ContextInterface {
		return $this->context;
	}
}
