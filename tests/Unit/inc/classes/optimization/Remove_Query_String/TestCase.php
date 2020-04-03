<?php

namespace WP_Rocket\Tests\Unit\inc\optimization\Remove_Query_String;

use Brain\Monkey\Functions;
use WP_Rocket\Optimization\Remove_Query_String;
use WP_Rocket\Tests\Unit\inc\Engine\Optimization\TestCase as OptimizationTestCase;

abstract class TestCase extends OptimizationTestCase {
	protected $rqs;

	public function setUp() {
		parent::setUp();

		$this->rqs = new Remove_Query_String(
			$this->options,
			$this->filesystem->getUrl( 'wordpress/wp-content/cache/busting/' ),
			'http://example.org/wp-content/cache/busting/'
		);

        Functions\when( 'get_bloginfo' )->justReturn( '5.3' );
    }
}
