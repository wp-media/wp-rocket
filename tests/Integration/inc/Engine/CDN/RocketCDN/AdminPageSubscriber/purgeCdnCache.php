<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\AdminPageSubscriber;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\CapTrait;
use WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\TestCase;
use WPDieException;

class Test_PurgeCdnCache extends TestCase
{
    use CapTrait;
    public static function set_up_before_class()
    {
        parent::set_up_before_class();
        self::hasAdminCapBeforeClass();
        self::setAdminCap();
    }
    public static function tear_down_after_class()
    {
        parent::tear_down_after_class();
        self::resetAdminCap();
    }
    public function set_up()
    {
        parent::set_up();
        unset($_GET['_wpnonce']);
    }
    public function tear_down()
    {
        unset($_GET['_wpnonce']);
        // Clean up.
        remove_filter('wp_redirect', [$this, 'return_empty_string']);
        parent::tear_down();
    }
    public function testShouldWPNonceAysWhenNonceIsMissing()
    {
        Functions\expect('current_user_can')->never();
        $this->expectException(WPDieException::class);
        $this->expectExceptionMessage('The link you followed has expired.');
        do_action('admin_post_rocket_purge_rocketcdn');
    }
    public function testShouldWPNonceAysWhenNonceInvalid()
    {
        $_GET['_wpnonce'] = 'invalid';
        Functions\expect('current_user_can')->never();
        $this->expectException(WPDieException::class);
        $this->expectExceptionMessage('The link you followed has expired.');
        do_action('admin_post_rocket_purge_rocketcdn');
    }
    /**
     * Test should wp_die() when the current user doesn't have 'rocket_manage_options' capability.
     */
    public function testShouldWPDieWhenCurrentUserCant()
    {
        $user_id = $this->factory->user->create(['role' => 'contributor']);
        wp_set_current_user($user_id);
        $_GET['_wpnonce'] = wp_create_nonce('rocket_purge_rocketcdn');
        $this->assertFalse(current_user_can('rocket_manage_options'));
        Functions\expect('set_transient')->never();
        $this->expectException(WPDieException::class);
        do_action('admin_post_rocket_purge_rocketcdn');
    }
    public function testSetTransientAndRedirectWhenCurrentUserCan()
    {
        // Set up everything.
        $user_id = $this->factory->user->create(['role' => 'administrator']);
        wp_set_current_user($user_id);
        $_REQUEST['_wp_http_referer'] = addslashes('http://example.com/wp-admin/options-general.php?page=wprocket#page_cdn');
        $_SERVER['REQUEST_URI'] = $_REQUEST['_wp_http_referer'];
        $_GET['_wpnonce'] = wp_create_nonce('rocket_purge_rocketcdn');
        add_filter('wp_redirect', [$this, 'return_empty_string']);
        // Yes, we do expect wp_die() when running tests.
        $this->expectException(WPDieException::class);
        // Run it.
        do_action('admin_post_rocket_purge_rocketcdn');
        $this->assertTrue(current_user_can('rocket_manage_options'));
        // Check that the transient was set.
        $this->assertSame(['status' => 'error', 'message' => 'RocketCDN cache purge failed: Missing identifier parameter.'], get_transient('rocketcdn_purge_cache_response'));
    }
}
