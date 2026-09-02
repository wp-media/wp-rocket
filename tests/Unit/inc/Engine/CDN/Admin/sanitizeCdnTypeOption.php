<?php

declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\Admin;

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
	public function testShouldPreserveCdnFieldsFromDb( array $config, array $expected ): void {
		$db = $config['options'];

		$this->options->shouldReceive( 'get' )
			->with( 'cdn_type', 'rocketcdn' )
			->andReturn( $db['cdn_type'] );

		$this->options->shouldReceive( 'get' )
			->with( 'cdn_state', 'nothing' )
			->andReturn( $db['cdn_state'] );

		$result = $this->subscriber->sanitize_cdn_type_option( $config['input'] );

		$this->assertSame( $expected['cdn_type'], $result['cdn_type'] );
		$this->assertSame( $expected['cdn_state'], $result['cdn_state'] );
	}

	public function testShouldNotModifyOtherInputFields(): void {
		$this->options->shouldReceive( 'get' )
			->with( 'cdn_type', 'rocketcdn' )
			->andReturn( 'byocdn' );

		$this->options->shouldReceive( 'get' )
			->with( 'cdn_state', 'nothing' )
			->andReturn( 'byocdn' );

		$input  = [
			'some_setting' => 'some_value',
			'cdn_type'     => 'rocketcdn',
			'cdn_state'    => 'nothing',
		];
		$result = $this->subscriber->sanitize_cdn_type_option( $input );

		$this->assertSame( 'some_value', $result['some_setting'] );
	}
}
