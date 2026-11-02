<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\License\Revoked;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Engine\License\Revoked;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\License\Revoked::get_revoked_banner
 *
 * @group License
 */
class Test_GetRevokedBanner extends TestCase {
	private $user;
	private $revoked;

	protected function setUp(): void {
		parent::setUp();

		$this->user    = Mockery::mock( User::class );
		$this->revoked = Mockery::mock( Revoked::class . '[generate]', [ $this->user, 'views' ] );

		$this->stubEscapeFunctions();
		$this->stubTranslationFunctions();
	}

	public function testShouldReturnEmptyStringWhenWhiteLabel() {
		Functions\when( 'rocket_get_constant' )->justReturn( true );

		$this->revoked->shouldReceive( 'generate' )->never();

		$this->assertSame( '', $this->revoked->get_revoked_banner() );
	}

	public function testShouldReturnEmptyStringWhenLicenseIsNotRevoked() {
		Functions\when( 'rocket_get_constant' )->justReturn( false );
		$this->user->shouldReceive( 'is_revoked' )->andReturn( false );

		$this->revoked->shouldReceive( 'generate' )->never();

		$this->assertSame( '', $this->revoked->get_revoked_banner() );
	}

	public function testShouldReturnEmptyStringWhenUserCannotManageOptions() {
		Functions\when( 'rocket_get_constant' )->justReturn( false );
		Functions\when( 'current_user_can' )->justReturn( false );
		$this->user->shouldReceive( 'is_revoked' )->andReturn( true );

		$this->revoked->shouldReceive( 'generate' )->never();

		$this->assertSame( '', $this->revoked->get_revoked_banner() );
	}

	public function testShouldReturnBannerWhenRevokedWithoutCheckingTheScreen() {
		Functions\when( 'rocket_get_constant' )->justReturn( false );
		Functions\when( 'current_user_can' )->justReturn( true );
		Functions\expect( 'get_current_screen' )->never();
		$this->user->shouldReceive( 'is_revoked' )->andReturn( true );

		$this->revoked->shouldReceive( 'generate' )
			->once()
			->with( 'revoked-website-banner', Mockery::type( 'array' ) )
			->andReturn( '<div>revoked</div>' );

		$this->assertSame( '<div>revoked</div>', $this->revoked->get_revoked_banner() );
	}

	public function testShouldReturnEmptyStringWhenGenerateReturnsNull() {
		Functions\when( 'rocket_get_constant' )->justReturn( false );
		Functions\when( 'current_user_can' )->justReturn( true );
		$this->user->shouldReceive( 'is_revoked' )->andReturn( true );

		$this->revoked->shouldReceive( 'generate' )->once()->andReturn( null );

		$this->assertSame( '', $this->revoked->get_revoked_banner() );
	}
}
