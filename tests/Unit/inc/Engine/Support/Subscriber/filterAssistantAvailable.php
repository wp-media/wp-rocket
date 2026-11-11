<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Support\Subscriber;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Engine\Support\Meta;
use WP_Rocket\Engine\Support\Rest;
use WP_Rocket\Engine\Support\Subscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Support\Subscriber::filter_assistant_available
 * and \WP_Rocket\Engine\Support\Subscriber::add_localize_script_data
 *
 * @group Support
 */
class TestFilterAssistantAvailable extends TestCase {
	private $user;
	private $subscriber;

	public function set_up() {
		parent::set_up();

		$this->user       = Mockery::mock( User::class );
		$this->subscriber  = new Subscriber(
			Mockery::mock( Rest::class ),
			Mockery::mock( Meta::class ),
			Mockery::mock( Options_Data::class ),
			$this->user
		);
	}

	public function testShouldListenToTheAssistantEvents() {
		$events = Subscriber::get_subscribed_events();

		$this->assertSame( 'filter_assistant_available', $events['rocket_ai_assistant_available'] );
		$this->assertSame( 'add_localize_script_data', $events['rocket_localize_admin_script'] );
	}

	public function testShouldAddTheCurrentValueToTheScriptData() {
		Filters\expectApplied( 'rocket_ai_assistant_available' )
			->once()
			->with( true )
			->andReturn( false );

		$this->assertSame(
			[
				'nonce'               => 'abc',
				'assistant_available' => false,
			],
			$this->subscriber->add_localize_script_data( [ 'nonce' => 'abc' ] )
		);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ): void {
		Functions\when( 'rocket_valid_key' )->justReturn( $config['valid_key'] );

		if ( $config['call_can_use_ai'] ) {
			$this->user->shouldReceive( 'can_use_ai' )->once()->andReturn( $config['can_use_ai'] );
		} else {
			$this->user->shouldNotReceive( 'can_use_ai' );
		}

		$this->assertSame( $expected['result'], $this->subscriber->filter_assistant_available( $config['input'] ) );
	}
}
