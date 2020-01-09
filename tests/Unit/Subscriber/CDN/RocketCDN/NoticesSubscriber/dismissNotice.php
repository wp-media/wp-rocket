<?php
namespace WP_Rocket\Tests\Unit\Subscriber\CDN\RocketCDN;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Subscriber\CDN\RocketCDN\NoticesSubscriber;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Subscriber\CDN\RocketCDN\NoticesSubscriber::dismiss_notice
 * @group RocketCDN
 */
class Test_DismissNotice extends TestCase {
    private $api_client;

	public function setUp() {
		parent::setUp();

        $this->api_client = $this->createMock( 'WP_Rocket\CDN\RocketCDN\APIClient' );
    }

    /**
	 * @covers ::dismiss_notice
	 */
    public function testShouldReturnNullWhenPOSTActionNotSet() {
        Functions\when('check_ajax_referer')->justReturn(true);

        $page = new NoticesSubscriber( $this->api_client, 'views/settings/rocketcdn');
        $this->assertNull($page->dismiss_notice());
    }

    /**
	 * @covers ::dismiss_notice
	 */
    public function testShouldReturnNullWhenIncorrectPOSTAction() {
        Functions\when('check_ajax_referer')->justReturn(true);

        $_POST['action'] = 'wrong_action';

        $page = new NoticesSubscriber( $this->api_client, 'views/settings/rocketcdn');
        $this->assertNull($page->dismiss_notice());
    }

    /**
	 * @covers ::dismiss_notice
	 */
    public function testShouldUpdateUserMetaWhenValid() {
        Functions\when('check_ajax_referer')->justReturn(true);
        Functions\when('get_current_user_id')->justReturn(1);
        Functions\expect('update_user_meta')->once();

        $_POST['action'] = 'rocketcdn_dismiss_notice';

        $page = new NoticesSubscriber( $this->api_client, 'views/settings/rocketcdn');
        $page->dismiss_notice();
    }
}