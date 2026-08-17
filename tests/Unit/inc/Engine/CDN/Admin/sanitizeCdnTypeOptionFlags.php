<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\Admin;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\CDN\Admin\Subscriber;
use WP_Rocket\Tests\Unit\TestCase;

class Test_SanitizeCdnTypeOptionFlags extends TestCase {
	/**
	 * @var Subscriber
	 */
	private $subscriber;

	public function set_up() {
		parent::set_up();

		Functions\when( 'sanitize_text_field' )->returnArg();

		$this->subscriber = new Subscriber();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldSanitizeTheDecoupledCdnFlags( array $config, array $expected ) {
		$this->assertSame( $expected, $this->subscriber->sanitize_cdn_type_option( $config['input'] ) );
	}
}
