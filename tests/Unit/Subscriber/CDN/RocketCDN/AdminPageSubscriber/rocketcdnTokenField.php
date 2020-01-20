<?php

namespace WP_Rocket\Tests\Unit\Subscriber\CDN\RocketCDN;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Subscriber\CDN\RocketCDN\AdminPageSubscriber;

/**
 * @covers \WP_Rocket\Subscriber\CDN\RocketCDN\AdminPageSubscriber::rocketcdn_token_field
 * @group  RocketCDN
 */
class Test_RocketcdnTokenField extends TestCase {
    private $api_client;
	private $page;

	public function setUp() {
		parent::setUp();

        $this->api_client = $this->createMock( 'WP_Rocket\CDN\RocketCDN\APIClient' );
		$this->page       = new AdminPageSubscriber(
			$this->api_client,
			$this->createMock( 'WP_Rocket\Admin\Options_Data' ),
			$this->createMock( 'WP_Rocket\Admin\Settings\Beacon' ),
			'views/settings/rocketcdn'
        );

        $this->mockCommonWpFunctions();
    }

    /**
     * Test should return default fields when RocketCDN is active
     */
    public function testShouldReturnDefaultFieldsWhenRocketCDNIsActive() {
        $this->api_client->expects( $this->once() )
		                 ->method( 'get_subscription_data' )
                         ->willReturn( [ 'subscription_status' => 'running', 'cdn_url' => 'example1.org' ] );

        $fields = [ 'cdn_cnames' => [] ];

        $this->assertSame( $fields, $this->page->rocketcdn_token_field( $fields ) );
    }

    /**
     * Test should return array with token field when RocketCDN is not active
     */
    public function testShouldReturnTokenFieldWhenRocketCDNNotActive() {
        $this->api_client->expects( $this->once() )
		                 ->method( 'get_subscription_data' )
                         ->willReturn( [ 'subscription_status' => 'cancelled' ] );

        $expected = [
            'rocketcdn_token' => [
                'type'            => 'text',
                'label'           => 'RocketCDN token',
                'description'     => 'The RocketCDN token used to send request to RocketCDN API',
                'default'         => '',
                'container_class' => [
                    'wpr-rocketcdn-token',
                    'wpr-isHidden',
                ],
                'section'         => 'cnames_section',
                'page'            => 'page_cdn',
            ]
        ];

        $this->assertSame( $expected, $this->page->rocketcdn_token_field( [] ) );
    }
}