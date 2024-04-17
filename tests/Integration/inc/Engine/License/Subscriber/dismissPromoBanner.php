<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\License\Subscriber;

use WP_Rocket\Tests\Integration\AjaxTestCase;

class Test_DismissPromoBanner extends AjaxTestCase
{
    /**
     * User's ID.
     * @var int
     */
    private static $user_id = 0;
    /**
     * Set up the User ID before tests start.
     */
    public static function wpSetUpBeforeClass($factory)
    {
        $role = get_role('administrator');
        $role->add_cap('rocket_manage_options');
        self::$user_id = $factory->user->create(['role' => 'administrator']);
    }
    public function set_up()
    {
        parent::set_up();
        wp_set_current_user(self::$user_id);
        $this->action = 'rocket_dismiss_promo';
    }
    public function tear_down()
    {
        parent::tear_down();
        delete_transient('rocket_promo_banner_' . self::$user_id);
    }
    public function testCallbackIsRegistered()
    {
        $this->assertTrue(has_action('wp_ajax_rocket_dismiss_promo'));
        global $wp_filter;
        $obj = $wp_filter['wp_ajax_rocket_dismiss_promo'];
        $callback_registration = current($obj->callbacks[10]);
        $this->assertEquals('dismiss_promo_banner', $callback_registration['function'][1]);
    }
    public function testShouldSetTransientWhenValid()
    {
        $_POST['nonce'] = wp_create_nonce('rocket-ajax');
        $_POST['action'] = 'rocket_dismiss_promo';
        $this->callAjaxAction();
        $this->assertTrue((bool) get_transient('rocket_promo_banner_' . self::$user_id));
    }
}
