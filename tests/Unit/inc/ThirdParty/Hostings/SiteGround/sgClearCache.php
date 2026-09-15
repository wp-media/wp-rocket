<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\SiteGround;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\SiteGround;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\SiteGround::sg_clear_cache
 *
 * @group SiteGround
 * @group ThirdParty
 * @group Hostings
 */
class Test_SgClearCache extends TestCase {
	protected function tearDown(): void {
		unset( $_GET['_wpnonce'] );

		parent::tearDown();
	}

	public function testShouldNotCleanDomainWithoutNonce() {
		Functions\expect( 'rocket_clean_domain' )->never();

		( new SiteGround() )->sg_clear_cache();
	}

	public function testShouldNotCleanDomainWithInvalidNonce() {
		$_GET['_wpnonce'] = 'invalid';

		Functions\when( 'sanitize_key' )->returnArg();
		Functions\when( 'wp_verify_nonce' )->justReturn( false );
		Functions\expect( 'rocket_clean_domain' )->never();

		( new SiteGround() )->sg_clear_cache();
	}

	public function testShouldNotCleanDomainWithoutCapability() {
		$_GET['_wpnonce'] = 'valid';

		Functions\when( 'sanitize_key' )->returnArg();
		Functions\when( 'wp_verify_nonce' )->justReturn( true );
		Functions\when( 'current_user_can' )->justReturn( false );
		Functions\expect( 'rocket_clean_domain' )->never();

		( new SiteGround() )->sg_clear_cache();
	}

	public function testShouldCleanDomainWithValidNonceAndCapability() {
		$_GET['_wpnonce'] = 'valid';

		Functions\when( 'sanitize_key' )->returnArg();
		Functions\when( 'wp_verify_nonce' )->justReturn( true );
		Functions\when( 'current_user_can' )->justReturn( true );
		Functions\expect( 'rocket_clean_domain' )->once();

		( new SiteGround() )->sg_clear_cache();
	}
}
