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
 * Test class covering \WP_Rocket\Engine\Support\Subscriber::has_assistant
 * and \WP_Rocket\Engine\Support\Subscriber::is_chat_registered
 *
 * @group Support
 */
class TestHasAssistant extends TestCase {
	private $user;
	private $subscriber;

	public function set_up() {
		parent::set_up();

		$this->user       = Mockery::mock( User::class );
		$this->subscriber = new Subscriber(
			Mockery::mock( Rest::class ),
			Mockery::mock( Meta::class ),
			Mockery::mock( Options_Data::class ),
			$this->user
		);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ): void {
		Functions\when( 'rocket_valid_key' )->justReturn( $config['valid_key'] );

		if ( $config['valid_key'] ) {
			$this->user->shouldReceive( 'can_use_ai' )->once()->andReturn( $config['can_use_ai'] );
		} else {
			$this->user->shouldNotReceive( 'can_use_ai' );
		}

		if ( isset( $config['ai_filter'] ) ) {
			Filters\expectApplied( 'rocket_ai_assistant_available' )->once()->with( true )->andReturn( $config['ai_filter'] );
		} else {
			Filters\expectApplied( 'rocket_ai_assistant_available' )->never();
		}

		if ( isset( $config['chat_filter'] ) ) {
			Filters\expectApplied( 'rocket_support_chat_available' )->once()->with( true )->andReturn( $config['chat_filter'] );
		} else {
			Filters\expectApplied( 'rocket_support_chat_available' )->never();
		}

		$this->assertSame( $expected['result'], $this->subscriber->has_assistant() );
	}

	public function testShouldBeFalseWhenTheLicenceServerAnsweredAnError() {
		Functions\when( 'rocket_valid_key' )->justReturn( true );
		Filters\expectApplied( 'rocket_ai_assistant_available' )->never();
		Filters\expectApplied( 'rocket_support_chat_available' )->never();

		$user       = new User( (object) [ 'code' => 404, 'detail' => 'User not found.', 'success' => false ] );
		$subscriber = new Subscriber( Mockery::mock( Rest::class ), Mockery::mock( Meta::class ), Mockery::mock( Options_Data::class ), $user );

		$this->assertFalse( $subscriber->has_assistant() );
	}

	public function testChatIsNotRegisteredBeforeAdminInit() {
		$this->assertFalse( $this->subscriber->is_chat_registered() );
	}
}
