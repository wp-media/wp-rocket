<?php

class PLL_Frontend {
	public $model;

	public function __construct( $options ) {
		require_once __DIR__ . '/PLL_Model.php';
		$this->model = new PLL_Model( $options );
	}
}
