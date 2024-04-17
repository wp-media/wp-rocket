<?php

namespace WP_Rocket\Tests\Integration\Inc\Addon\Cloudflare\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

class TestDeactivateDevmode extends TestCase
{
    public function testShouldDoExpected()
    {
        do_action('rocket_cron_deactivate_cloudflare_devmode');
        $options = get_option('wp_rocket_settings', []);
        $this->assertSame(0, $options['cloudflare_devmode']);
    }
}
