<?php
namespace WP_Rocket\Tests\Unit\Subscriber\CDN\RocketCDN;

use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Subscriber\CDN\RocketCDN\NoticesSubscriber;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Subscriber\CDN\RocketCDN\NoticesSubscriber::dismiss_notice
 * @group RocketCDN
 */
class Test_DismissNotice extends TestCase {
	public function testShouldUpdateUserMetaWhenValid() {
		Functions\when('check_ajax_referer')->justReturn(true);
		Functions\when('get_current_user_id')->justReturn(1);
		Functions\expect('update_user_meta')
			->once()
			->with( 1, 'rocketcdn_dismiss_notice', true );

		$_POST['action'] = 'rocketcdn_dismiss_notice';

		$notices = new NoticesSubscriber( $this->createMock( 'WP_Rocket\CDN\RocketCDN\APIClient' ), 'views/settings/rocketcdn');
		$notices->dismiss_notice();
	}
}
