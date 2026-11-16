<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\Settings\Subscriber;

use Mockery;
use ReflectionClass;
use WP_Rocket\Dependencies\WPMedia\PluginFamily\Controller\PluginFamily;
use WP_Rocket\Engine\Admin\Settings\Page;
use WP_Rocket\Engine\Admin\Settings\Render;
use WP_Rocket\Engine\Admin\Settings\Subscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Admin\Settings\Subscriber::add_imagify_addon_card
 * @covers \WP_Rocket\Engine\Admin\Settings\Page::add_imagify_addon_card
 *
 * @group SettingsPage
 */
class Test_AddImagifyAddonCard extends TestCase {
	public function testShouldBeHookedOnTheAddonsFilterAtDefaultPriority() {
		$events = Subscriber::get_subscribed_events();

		$this->assertSame( 'add_imagify_addon_card', $events['rocket_dashboard_sidebar_addons'] );
	}

	public function testSubscriberShouldProxyToThePage() {
		$page = Mockery::mock( Page::class );
		$page->shouldReceive( 'add_imagify_addon_card' )->once()->with( [ [ 'id' => 'a' ] ] )->andReturn( [ [ 'id' => 'a' ], [ 'id' => 'imagify' ] ] );

		$subscriber = new Subscriber( $page, Mockery::mock( PluginFamily::class ) );

		$this->assertSame( [ [ 'id' => 'a' ], [ 'id' => 'imagify' ] ], $subscriber->add_imagify_addon_card( [ [ 'id' => 'a' ] ] ) );
	}

	public function cases() {
		return [
			'cardAppended' => [ [ 'id' => 'imagify' ], [ [ 'id' => 'a' ], [ 'id' => 'imagify' ] ] ],
			'noCard'       => [ [], [ [ 'id' => 'a' ] ] ],
		];
	}

	/**
	 * @dataProvider cases
	 */
	public function testPageShouldAppendTheCardWhenThereIsOne( $card, $expected ) {
		$render = Mockery::mock( Render::class );
		$render->shouldReceive( 'get_imagify_addon_card_data' )->once()->andReturn( $card );

		$reflection = new ReflectionClass( Page::class );
		$page       = $reflection->newInstanceWithoutConstructor();
		$property   = $reflection->getProperty( 'render' );
		$property->setAccessible( true );
		$property->setValue( $page, $render );

		$this->assertSame( $expected, $page->add_imagify_addon_card( [ [ 'id' => 'a' ] ] ) );
	}
}
