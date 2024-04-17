<?php

namespace WP_Rocket\Tests\Integration\Inc\ThirdParty\Plugins\Optimization\Ezoic;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\Optimization\Ezoic::add_explanations
 *
 * @group Ezoic
 * @group ThirdParty
 */
class Test_Explanations extends TestCase {
	public function testShouldReturnExpected() {
		$plugins_explanations = [];
		$expected             = [
			'ezoic' => 'This plugin blocks WP Rocket\'s caching and optimizations. Deactivate it and use <a href="https://support.ezoic.com/kb/article/how-can-i-integrate-my-site-with-ezoic" target="_blank" rel="noopener noreferrer">Ezoic\'s nameserver integration</a> instead.',
		];

		$this->assertSame(
			$expected,
			apply_filters( 'rocket_plugins_to_deactivate_explanations', $plugins_explanations )
		);
	}
}
