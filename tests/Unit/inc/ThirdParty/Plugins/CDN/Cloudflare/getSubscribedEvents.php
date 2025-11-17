<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\CDN\Cloudflare;

use Mockery;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\ThirdParty\Plugins\CDN\{Cloudflare,CloudflareFacade};
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Admin\Options;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\CDN\Cloudflare::get_subscribed_events
 *
 * @group ThirdParty
 * @group CloudflarePlugin
 */
class Test_getSubscribedEvents extends TestCase {

    public function testShouldReturnSubscribedEventsArray() {
        $events = [
            'admin_notices'                       => [
                [ 'display_server_pushing_mode_notice' ],
                [ 'display_apo_cookies_notice' ],
                [ 'display_apo_cache_notice' ],
            ],
            'rocket_display_input_do_cloudflare'  => 'hide_addon_radio',
            'rocket_cloudflare_field_settings'    => 'update_addon_field',
            'pre_get_rocket_option_do_cloudflare' => 'disable_cloudflare_option',
            'rocket_after_clean_domain'           => 'purge_cloudflare',
            'after_rocket_clean_files'            => 'purge_cloudflare_partial',
            'after_rocket_clean_home'             => 'purge_cloudflare',
            'rocket_after_automatic_cache_purge'  => 'purge_cloudflare_after_automatic_purge',
            'rocket_saas_complete_job_status'     => 'purge_cloudflare_after_usedcss',
            'rocket_rucss_after_clearing_usedcss' => 'purge_cloudflare_after_usedcss',
            'admin_post_rocket_enable_separate_mobile_cache' => 'enable_separate_mobile_cache',
            'rocket_cdn_helper_addons'            => 'add_cdn_helper_message',
            'init'                                => 'unregister_cloudflare_clean_on_post',
        ];

        $this->assertSame(
            $events,
            Cloudflare::get_subscribed_events()
        );
    }
}