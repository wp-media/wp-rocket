<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\QueryString\Remove;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\Optimization\QueryString\Remove;
use WP_Rocket\Tests\Unit\inc\Engine\Optimization\TestCase as OptimizationTestCase;

abstract class TestCase extends OptimizationTestCase {
	protected $rqs;

	public function setUp() {
		parent::setUp();

		$this->rqs = new Remove(
			$this->options,
			$this->filesystem->getUrl( 'wordpress/wp-content/cache/busting/' ),
			'http://example.org/wp-content/cache/busting/'
		);

		Functions\when( 'get_bloginfo' )->justReturn( '5.3' );
	}
}
