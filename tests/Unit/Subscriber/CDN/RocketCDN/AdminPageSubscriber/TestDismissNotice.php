<?php
namespace WP_Rocket\Tests\Unit\Subscriber\CDN\RocketCDN;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Subscriber\CDN\RocketCDN\AdminPageSubscriber;
use Brain\Monkey\Functions;

/**
 * @coversDefaultClass \WP_Rocket\Subscriber\CDN\RocketCDN\AdminPageSubscriber
 * @group RocketCDN
 */
class TestDismissNotice extends TestCase {
    private $options;
	private $beacon;

	public function setUp() {
		parent::setUp();

		$this->options = $this->createMock('WP_Rocket\Admin\Options_Data');
		$this->beacon  = $this->createMock('WP_Rocket\Admin\Settings\Beacon');
    }

    /**
	 * @covers ::dismiss_notice
	 */
    public function testShouldReturnNullWhenPOSTActionNotSet() {
        Functions\when('check_ajax_referer')->justReturn(true);

        $page = new AdminPageSubscriber( $this->options, $this->beacon, 'views/settings/rocketcdn');
        $this->assertNull($page->dismiss_notice());
    }

    /**
	 * @covers ::dismiss_notice
	 */
    public function testShouldReturnNullWhenIncorrectPOSTAction() {
        Functions\when('check_ajax_referer')->justReturn(true);

        $_POST['action'] = 'wrong_action';

        $page = new AdminPageSubscriber( $this->options, $this->beacon, 'views/settings/rocketcdn');
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

        $page = new AdminPageSubscriber( $this->options, $this->beacon, 'views/settings/rocketcdn');
        $page->dismiss_notice();
    }
}