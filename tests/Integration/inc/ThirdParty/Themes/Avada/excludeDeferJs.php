<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Themes\Avada;

use WP_Rocket\ThirdParty\Themes\Avada;

/**
 * @covers \WP_Rocket\ThirdParty\Themes\Avada::clean_domain
 *
 * @group  Themes
 */
class Test_ExcludeDeferJs extends TestCase {
	protected $path_to_test_data = '/inc/ThirdParty/Themes/Avada/excludeDeferJs.php';

	public function testShouldExcludeFromDeferJSMaps() {
		$this->subscriber = new Avada( $this->container->get( 'options' ) );

		$this->event->add_subscriber( $this->subscriber );

		$expected_defer_js = [
			'/jquery-?[0-9.]*(.min|.slim|.slim.min)?.js',
			'maps.googleapis.com',
		];

		$this->assertSame(
			$expected_defer_js,
			apply_filters( 'rocket_exclude_defer_js', [] )
		);
	}
}
