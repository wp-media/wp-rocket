<?php

declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\Admin;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CDN\Admin\Subscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\CDN\Admin\Subscriber::sanitize_cdn_type_option
 *
 * @group CDN
 */
class Test_SanitizeCdnTypeOption extends TestCase {
	/**
	 * @var Mockery\MockInterface|Options_Data
	 */
	private $options;

	/**
	 * @var Subscriber
	 */
	private $subscriber;

	public function set_up() {
		parent::set_up();

		$this->options    = Mockery::mock( Options_Data::class );
		$this->subscriber = new Subscriber( $this->options );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldSanitizeAndValidateCdnFields( array $input, array $expected ): void {
		Functions\when( 'sanitize_text_field' )->returnArg();

		$result = $this->subscriber->sanitize_cdn_type_option( $input );

		$this->assertSame( $expected['cdn_type'], $result['cdn_type'] );

		if ( array_key_exists( 'cdn_state', $expected ) ) {
			$this->assertSame( $expected['cdn_state'], $result['cdn_state'] );
		}
	}

	public function testShouldNotModifyOtherInputFields(): void {
		Functions\when( 'sanitize_text_field' )->returnArg();

		$input  = [
			'some_setting' => 'some_value',
			'cdn_type'     => 'byocdn',
			'cdn_state'    => 'byocdn',
		];
		$result = $this->subscriber->sanitize_cdn_type_option( $input );

		$this->assertSame( 'some_value', $result['some_setting'] );
	}
}
