<?php

class PLL_Frontend {
	public $links = '';
	public $model;
	public $options = [];

	public function __construct( $options ) {
		require_once __DIR__ . '/PLL_Model.php';
		$this->options = $options;
		$this->model   = new PLL_Model( $options['model'] );
	}
}
