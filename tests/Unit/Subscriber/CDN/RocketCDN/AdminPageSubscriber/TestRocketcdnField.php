<?php
namespace WP_Rocket\Tests\Unit\Subscriber\CDN\RocketCDN;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Subscriber\CDN\RocketCDN\AdminPageSubscriber;
use Brain\Monkey\Functions;

/**
 * @coversDefaultClass \WP_Rocket\Subscriber\CDN\RocketCDN\AdminPageSubscriber
 */
class TestRocketcdnField extends TestCase {
    /**
	 * @covers ::rocketcdn_field
	 * @group RocketCDN
	 */
    public function testShouldReturnDefaultFieldWhenRocketCDNNotActive() {
        Functions\when('get_transient')->justReturn(['is_active' => false]);

        $fields = [
            'cdn_cnames' => []
        ];

        $page = new AdminPageSubscriber( 'views/settings/rocketcdn');
        $this->assertSame(
            $fields,
            $page->rocketcdn_field( $fields )
        );
    }

    /**
	 * @covers ::rocketcdn_field
	 * @group RocketCDN
	 */
    public function testShouldReturnRocketCDNFieldWhenRocketCDNActive() {
        $this->mockCommonWpFunctions();

        Functions\when('get_transient')->justReturn(['is_active' => true]);

        $fields = [
            'cdn_cnames' => []
        ];

        $rocketcdn_field = [
            'cdn_cnames' => [
                'type'        => 'rocket_cdn',
                'label'       => __( 'CDN CNAME(s)', 'rocket' ),
                'description' => __( 'Specify the CNAME(s) below', 'rocket' ),
                'helper'      => __( 'Rocket CDN is currently active.', 'rocket' ) . ' <button class="wpr-rocketcdn-open" data-micromodal-trigger="wpr-rocketcdn-modal">' . __( 'Unsubscribe', 'rocket' ) . '</button>',
                'default'     => '',
                'section'     => 'cnames_section',
                'page'        => 'page_cdn',
            ]
        ];

        $page = new AdminPageSubscriber( 'views/settings/rocketcdn');
        $this->assertSame(
            $rocketcdn_field,
            $page->rocketcdn_field( $fields )
        );
    }
}