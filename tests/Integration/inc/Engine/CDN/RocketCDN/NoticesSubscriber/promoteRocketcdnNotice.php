<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\NoticesSubscriber;

use WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\TestCase;

class Test_PromoteRocketcdnNotice extends TestCase
{
    private $notice;
    public function set_up()
    {
        parent::set_up();
        if (empty($this->notice)) {
            $this->notice = $this->format_the_html($this->config['notice']);
        }
    }
    /**
     * @dataProvider configTestData
     */
    public function testShouldDisplayPerData($data, $expected, $config)
    {
        $this->white_label = isset($config['white_label']) ? $config['white_label'] : $this->white_label;
        if (isset($config['home_url'])) {
            $this->home_url = $config['home_url'];
            add_filter('home_url', [$this, 'home_url_cb']);
        }
        if (isset($data['rocketcdn_status'])) {
            set_transient('rocketcdn_status', $data['rocketcdn_status'], MINUTE_IN_SECONDS);
        }
        if (isset($config['role'])) {
            $this->configUser($config['role']);
        }
        if (isset($config['screen'])) {
            set_current_screen($config['screen']);
        }
        if (isset($config['user_meta'])) {
            add_user_meta(get_current_user_id(), 'rocketcdn_dismiss_notice', $config['user_meta']);
        }
        ob_start();
        do_action('admin_notices');
        $actual = ob_get_clean();
        if (!empty($actual)) {
            $actual = $this->format_the_html($actual);
        }
        if ($expected['should_display']) {
            $this->assertContains($this->notice, $actual);
        } else {
            $this->assertStringNotContainsString($this->notice, $actual);
        }
    }
    private function configUser($role)
    {
        // Make sure the capability is correct.
        $admin = get_role('administrator');
        if (!$admin->has_cap('rocket_manage_options')) {
            $admin->add_cap('rocket_manage_options');
        }
        $user_id = $this->factory->user->create(['role' => $role]);
        wp_set_current_user($user_id);
    }
}
