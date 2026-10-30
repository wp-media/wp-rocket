<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Support\ServiceProvider;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Dependencies\League\Container\Container;
use WP_Rocket\Engine\License\API\UserClient;
use WP_Rocket\Engine\Support\ServiceProvider;
use WP_Rocket\Engine\Support\Subscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Support\ServiceProvider::register
 *
 * @group Support
 */
class Test_Register extends TestCase {
	public function testShouldBuildTheSubscriberWithTheUserClient() {
		$container = new Container();
		$container->add( 'options', Mockery::mock( Options_Data::class ) );
		$container->add( 'user_client', Mockery::mock( UserClient::class ) );

		$provider = new ServiceProvider();
		$provider->setContainer( $container );
		$provider->register();

		$this->assertInstanceOf( Subscriber::class, $container->get( 'support_subscriber' ) );
	}
}
