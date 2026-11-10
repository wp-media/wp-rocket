<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Support\Subscriber;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Engine\Support\Meta;
use WP_Rocket\Engine\Support\Rest;
use WP_Rocket\Engine\Support\Subscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Support\Subscriber::revoke_app_passwords
 *
 * @group Support
 */
class TestRevokeAppPasswords extends TestCase {
	private $subscriber;

	public function set_up() {
		parent::set_up();

		$this->subscriber = new Subscriber(
			Mockery::mock( Rest::class ),
			Mockery::mock( Meta::class ),
			Mockery::mock( Options_Data::class ),
			Mockery::mock( User::class )
		);
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function testShouldRevokeTheWpRocketPasswords(): void {
		/** @var \Mockery\MockInterface $wap_client The class is aliased, so PHPStan cannot resolve it. */
		$wap_client = Mockery::mock( 'alias:WapClient' );
		$wap_client->shouldReceive( 'revoke_app_passwords' )->once()->with( 'wprocket' );

		$this->subscriber->revoke_app_passwords();
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function testShouldDoNothingWhenTheLibraryIsMissing(): void {
		$this->assertFalse( class_exists( 'WapClient', false ) );

		$this->subscriber->revoke_app_passwords();
	}
}
