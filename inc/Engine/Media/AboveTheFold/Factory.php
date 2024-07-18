<?php

declare(strict_types=1);

namespace WP_Rocket\Engine\Media\AboveTheFold;

use WP_Rocket\Engine\Common\PerformanceHints\FactoryInterface;
use WP_Rocket\Engine\Common\PerformanceHints\AJAX\ControllerInterface as AjaxControllerInterface;

class Factory implements FactoryInterface {

    /**
     * Ajax Controller.
     * 
     * @var AjaxControllerInterface
     */
    protected $ajax_controller;

	/**
	 * Instatiate the class.
	 */
	public function __construct( AjaxControllerInterface $ajax_controller ) {
		$this->ajax_controller = $ajax_controller;
	}

	/**
	 * Provides an Ajax controller object.
     * 
     * @return AjaxControllerInterface
	 */
	public function ajax(): AjaxControllerInterface {
		return $this->ajax_controller;
	}

	/**
	 * Provides a Frontend object.
	 */
	public function frontend() {
		// Return Fontend object.
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
}
