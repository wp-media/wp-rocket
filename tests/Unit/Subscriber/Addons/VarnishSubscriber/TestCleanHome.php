<?php
namespace WP_Rocket\Tests\Unit\Subscriber\Addons\VarnishSubscriber;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Subscriber\Addons\Varnish\VarnishSubscriber;
use Brain\Monkey\Functions;

class TestCleanHome extends TestCase {
	use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
    public function testShouldDoNothingWhenVarnishDisabled() {
        $options = $this->createMock('WP_Rocket\Admin\Options_Data');
        $map     = [
            [
                'varnish_auto_purge',
                0,
                0,
            ],
        ];

        $options->method('get')->will($this->returnValueMap($map));

        $varnish = \Mockery::mock(\WP_Rocket\Addons\Varnish\Varnish::class);
        $varnish->shouldNotReceive('purge');

        $varnish_subscriber = new VarnishSubscriber( $varnish, $options );

        $varnish_subscriber->clean_home( 'wp-rocket/cache', '' );
    }

    public function testShouldPurgeHomeAndPaginationWhenVarnishEnabled() {
        $options = $this->createMock('WP_Rocket\Admin\Options_Data');
        $map     = [
            [
                'varnish_auto_purge',
                0,
                1,
            ],
        ];

        $options->method('get')->will($this->returnValueMap($map));

        Functions\when( 'get_rocket_i18n_home_url' )->justReturn( 'http://example.org/' );
        $GLOBALS['wp_rewrite'] = new \stdClass();
        $GLOBALS['wp_rewrite']->pagination_base = 'page/';

        $varnish = \Mockery::mock(\WP_Rocket\Addons\Varnish\Varnish::class);
        $varnish->shouldReceive('purge')->once()->with('http://example.org/');
        $varnish->shouldReceive('purge')->once()->with('http://example.org/page/?regex');

        $varnish_subscriber = new VarnishSubscriber( $varnish, $options );

        $varnish_subscriber->clean_home( 'wp-rocket/cache', '' );
    }
}