<?php
namespace WP_Rocket\Tests\Unit\Subscriber\CDN\RocketCDN;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Subscriber\CDN\RocketCDN\NoticesSubscriber;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Subscriber\CDN\RocketCDN\NoticesSubscriber::toggle_cta
 * @group RocketCDN
 */
class Test_ToggleCTA extends TestCase {
    private $api_client;

	public function setUp() {
		parent::setUp();

        $this->api_client = $this->createMock( 'WP_Rocket\CDN\RocketCDN\APIClient' );
    }

    /**
     * Test should return null when the $_POST values are not set
     */
    public function testShouldReturnNullWhenPOSTNotSet() {
        Functions\when('check_ajax_referer')->justReturn(true);

        $page = new NoticesSubscriber( $this->api_client, 'views/settings/rocketcdn');
        $this->assertNull( $page->toggle_cta() );
    }

    /**
     * Test should return null when $_POST action key is invalid
     */
    public function testShouldReturnNullWhenInvalidPOSTAction() {
        Functions\when('check_ajax_referer')->justReturn(true);

        $_POST['status'] = 'big';
        $_POST['action'] = 'invalid';

        $page = new NoticesSubscriber( $this->api_client, 'views/settings/rocketcdn');
        $this->assertNull( $page->toggle_cta() );
    }

    /**
     * Test should call delete_user_meta once when status value is big
     */
    public function testShouldReturnDeleteUserMetaWhenStatusIsBig() {
        Functions\when('check_ajax_referer')->justReturn(true);
        Functions\when('get_current_user_id')->justReturn(1);
        Functions\expect('delete_user_meta')->once();

        $_POST['status'] = 'big';
        $_POST['action'] = 'toggle_rocketcdn_cta';

        $page = new NoticesSubscriber( $this->api_client, 'views/settings/rocketcdn');
        $page->toggle_cta();
    }

    /**
     * Test should call update_user_meta once when status value is small
     */
    public function testShouldReturnUpdateUserMetaWhenStatusIsSmall() {
        Functions\when('check_ajax_referer')->justReturn(true);
        Functions\when('get_current_user_id')->justReturn(1);
        Functions\expect('update_user_meta')->once();

        $_POST['status'] = 'small';
        $_POST['action'] = 'toggle_rocketcdn_cta';

        $page = new NoticesSubscriber( $this->api_client, 'views/settings/rocketcdn');
        $page->toggle_cta();
    }
}