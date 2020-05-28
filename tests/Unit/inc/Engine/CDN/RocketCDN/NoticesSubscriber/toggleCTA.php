<?php
namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\RocketCDN\NoticesSubscriber;

use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Engine\CDN\RocketCDN\NoticesSubscriber;
use Brain\Monkey\Functions;
use Mockery;

/**
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\NoticesSubscriber::toggle_cta
 *
 * @group RocketCDN
 */
class Test_ToggleCTA extends TestCase {
	private $notices;

	public function setUp() {
		parent::setUp();

		$this->notices = new NoticesSubscriber(
			Mockery::mock( 'WP_Rocket\Engine\CDN\RocketCDN\APIClient' ),
			'views/settings/rocketcdn'
		);
	}

	public function testShouldReturnNullWhenNoCapacity() {
		Functions\when('check_ajax_referer')->justReturn(true);
		Functions\when( 'current_user_can' )->justReturn( false );

		$this->assertNull( $this->notices->toggle_cta() );
	}

	/**
	 * Test should return null when the $_POST values are not set
	 */
	public function testShouldReturnNullWhenPOSTNotSet() {
		Functions\when('check_ajax_referer')->justReturn(true);
		Functions\when( 'current_user_can' )->justReturn( true );

		$this->assertNull( $this->notices->toggle_cta() );
	}

	/**
	 * Test should call delete_user_meta once when status value is big
	 */
	public function testShouldDeleteUserMetaWhenStatusIsBig() {
		Functions\when('check_ajax_referer')->justReturn(true);
		Functions\when( 'current_user_can' )->justReturn( true );
		Functions\when('get_current_user_id')->justReturn(1);
		Functions\expect('delete_user_meta')
			->once()
			->with( 1, 'rocket_rocketcdn_cta_hidden' );

		$_POST['status'] = 'big';
		$_POST['action'] = 'toggle_rocketcdn_cta';

		$this->notices->toggle_cta();
	}

	/**
	 * Test should call update_user_meta once when status value is small
	 */
	public function testShouldUpdateUserMetaWhenStatusIsSmall() {
		Functions\when('check_ajax_referer')->justReturn(true);
		Functions\when( 'current_user_can' )->justReturn( true );
		Functions\when('get_current_user_id')->justReturn(1);
		Functions\expect('update_user_meta')
			->once()
			->with( 1, 'rocket_rocketcdn_cta_hidden', true );

		$_POST['status'] = 'small';
		$_POST['action'] = 'toggle_rocketcdn_cta';

		$this->notices->toggle_cta();
	}
}
