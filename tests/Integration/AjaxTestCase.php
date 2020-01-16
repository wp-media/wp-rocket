<?php

namespace WP_Rocket\Tests\Integration;

use Brain\Monkey;
use WP_Rocket\Tests\TestCaseTrait;
use WPAjaxDieContinueException;
use WPAjaxDieStopException;
use WP_Ajax_UnitTestCase;

abstract class AjaxTestCase extends WP_Ajax_UnitTestCase {
	use TestCaseTrait;

	/**
	 * AJAX Action. Change this value in each test class.
	 *
	 * @var string
	 */
	protected $action;

	/**
	 * Prepares the test environment before each test.
	 */
	public function setUp() {
		parent::setUp();
		Monkey\setUp();
	}

	/**
	 * Cleans up the test environment after each test.
	 */
	public function tearDown() {
		Monkey\tearDown();
		parent::tearDown();
	}

	protected function callAjaxAction() {
		try {
			$this->_handleAjax( $this->action );
		} catch ( \WPAjaxDieContinueException $e ) {}

		return $this->getResponse();
	}

	/**
	 * Get the AJAX Response.
	 */
	protected function getResponse() {
		return json_decode( $this->_last_response );
	}
}
