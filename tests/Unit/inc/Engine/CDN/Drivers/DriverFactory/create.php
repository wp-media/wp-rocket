<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\Drivers\DriverFactory;

use Mockery;
use WP_Rocket\Engine\CDN\Context;
use WP_Rocket\Engine\CDN\Drivers\DriverFactory;
use WP_Rocket\Engine\CDN\Drivers\DriverInterface;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\CDN\Drivers\DriverFactory::create
 * @group  CDN
 */
class Test_Create extends TestCase {

	/**
	 * Anonymous container mock with a get() method.
	 *
	 * @var \Mockery\MockInterface
	 */
	private $container;

	/**
	 * @var Context|\Mockery\MockInterface
	 */
	private $context;

	/**
	 * @var DriverFactory
	 */
	private $factory;

	public function setUp(): void {
		parent::setUp();

		$this->container = Mockery::mock( 'container' );
		$this->context   = Mockery::mock( Context::class );
		$this->factory   = new DriverFactory( $this->container, $this->context );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldCreateCorrectDriver( array $config, ?string $expected ) {
		$this->context->shouldReceive( 'get_driver' )
			->once()
			->andReturn( $config['active_driver'] );

		if ( null !== $expected ) {
			$mock_driver = Mockery::mock( DriverInterface::class );

			$this->container->shouldReceive( 'get' )
				->once()
				->with( $expected )
				->andReturn( $mock_driver );

			$this->assertSame( $mock_driver, $this->factory->create() );
		} else {
			$this->container->shouldNotReceive( 'get' );
			$this->assertNull( $this->factory->create() );
		}
	}
}