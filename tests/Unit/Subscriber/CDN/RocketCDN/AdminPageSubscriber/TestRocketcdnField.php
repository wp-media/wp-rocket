<?php
namespace WP_Rocket\Tests\Unit\Subscriber\CDN\RocketCDN;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Subscriber\CDN\RocketCDN\AdminPageSubscriber;
use Brain\Monkey\Functions;

/**
 * @coversDefaultClass \WP_Rocket\Subscriber\CDN\RocketCDN\AdminPageSubscriber
 * @group RocketCDN
 */
class TestRocketcdnField extends TestCase {
    private $options;
	private $beacon;

	public function setUp() {
		parent::setUp();

		$this->options = $this->createMock('WP_Rocket\Admin\Options_Data');
		$this->beacon  = $this->createMock('WP_Rocket\Admin\Settings\Beacon');
	}

    /**
	 * @covers ::rocketcdn_field
	 */
    public function testShouldReturnDefaultFieldWhenRocketCDNNotActive() {
        Functions\when('get_transient')->justReturn(['is_active' => false]);

        $fields = [
            'cdn_cnames' => []
        ];

        $page = new AdminPageSubscriber( $this->options, $this->beacon, 'views/settings/rocketcdn');
        $this->assertSame(
            $fields,
            $page->rocketcdn_field( $fields )
        );
    }

    /**
	 * @covers ::rocketcdn_field
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
                'helper'      => __( 'Rocket CDN is currently active. <a href="" data-beacon-article="" rel="noopener noreferrer" target="_blank">More Info</a>', 'rocket' ),
                'default'     => '',
                'section'     => 'cnames_section',
                'page'        => 'page_cdn',
            ]
        ];

        $page = new AdminPageSubscriber( $this->options, $this->beacon, 'views/settings/rocketcdn');
        $this->assertSame(
            $rocketcdn_field,
            $page->rocketcdn_field( $fields )
        );
    }
}