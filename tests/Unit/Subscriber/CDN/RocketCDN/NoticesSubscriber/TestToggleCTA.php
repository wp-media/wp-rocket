<?php
namespace WP_Rocket\Tests\Unit\Subscriber\CDN\RocketCDN;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Subscriber\CDN\RocketCDN\NoticesSubscriber;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Subscriber\CDN\RocketCDN\NoticesSubscriber::toggle_cta
 * @group RocketCDN
 */
class TestToggleCTA extends TestCase {
    private $api_client;

	public function setUp() {
		parent::setUp();

        $this->api_client = $this->createMock( 'WP_Rocket\CDN\RocketCDN\APIClient' );
    }

    /**
     * @covers ::toggle_cta
     */
    public function testShouldReturnNullWhenPOSTNotSet() {
        Functions\when('check_ajax_referer')->justReturn(true);

        $page = new NoticesSubscriber( $this->api_client, 'views/settings/rocketcdn');
        $this->assertNull( $page->toggle_cta() );
    }

    /**
     * @covers ::toggle_cta
     */
    public function testShouldReturnNullWhenInvalidPOSTAction() {
        Functions\when('check_ajax_referer')->justReturn(true);

        $_POST['status'] = 'big';
        $_POST['action'] = 'invalid';

        $page = new NoticesSubscriber( $this->api_client, 'views/settings/rocketcdn');
        $this->assertNull( $page->toggle_cta() );
    }

    /**
     * @covers ::toggle_cta
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
     * @covers ::toggle_cta
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