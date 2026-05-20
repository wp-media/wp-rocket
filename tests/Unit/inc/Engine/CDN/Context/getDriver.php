<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\Context;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CDN\Context;
use WP_Rocket\Tests\Unit\TestCase;

class Test_GetDriver extends TestCase {
	/**
	 * @var Options_Data
	 */
	private $options;

	/**
	 * @var Context
	 */
	private $context;

	public function set_up() {
		parent::set_up();

		$this->options    = Mockery::mock( Options_Data::class );
		$this->context    = new Context( $this->options );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpectedDriver( array $config, string $expected ) {
        /* @phpstan-ignore-next-line */
		$this->options->shouldReceive( 'get' )
			->with( 'cdn_type', 'rocketcdn' )
			->andReturn( $config['cdn_type'] );

		$this->assertSame( $expected, $this->context->get_driver() );
	}
}
