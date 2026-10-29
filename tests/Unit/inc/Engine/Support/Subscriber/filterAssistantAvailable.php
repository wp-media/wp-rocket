<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Support\Subscriber;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\License\API\UserClient;
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
	private $user_client;
	private $subscriber;

	public function set_up() {
		parent::set_up();

		$this->user_client = Mockery::mock( UserClient::class );
		$this->subscriber  = new Subscriber(
			Mockery::mock( Rest::class ),
			Mockery::mock( Meta::class ),
			Mockery::mock( Options_Data::class ),
			$this->user_client
		);
	}

	public function testShouldListenToTheAssistantEvents() {
		$events = Subscriber::get_subscribed_events();

		$this->assertSame( 'filter_assistant_available', $events['rocket_ai_assistant_available'] );
		$this->assertSame( 'add_localize_script_data', $events['rocket_localize_admin_script'] );
	}

	public function testShouldReturnTrueWhenTheKeyIsValidAndTheUserCanUseTheAssistant() {
		Functions\when( 'rocket_valid_key' )->justReturn( true );
		$this->user_client->shouldReceive( 'get_user_data' )->andReturn( (object) [ 'can_use_ai' => true ] );

		$this->assertTrue( $this->subscriber->filter_assistant_available( true ) );
	}

	public function testShouldReturnTrueWhenTheFlagIsMissing() {
		Functions\when( 'rocket_valid_key' )->justReturn( true );
		$this->user_client->shouldReceive( 'get_user_data' )->andReturn( (object) [] );

		$this->assertTrue( $this->subscriber->filter_assistant_available( true ) );
	}

	public function testShouldReturnFalseWhenTheFlagIsNotTrue() {
		Functions\when( 'rocket_valid_key' )->justReturn( true );
		$this->user_client->shouldReceive( 'get_user_data' )->andReturn( (object) [ 'can_use_ai' => false ] );

		$this->assertFalse( $this->subscriber->filter_assistant_available( true ) );
	}

	public function testShouldReturnFalseWithoutReadingTheUserDataWhenTheKeyIsNotValid() {
		Functions\when( 'rocket_valid_key' )->justReturn( false );
		$this->user_client->shouldNotReceive( 'get_user_data' );

		$this->assertFalse( $this->subscriber->filter_assistant_available( true ) );
	}

	public function testShouldKeepAnEarlierFalse() {
		Functions\when( 'rocket_valid_key' )->justReturn( true );
		$this->user_client->shouldReceive( 'get_user_data' )->andReturn( (object) [ 'can_use_ai' => true ] );

		$this->assertFalse( $this->subscriber->filter_assistant_available( false ) );
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
}
