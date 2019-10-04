<?php
namespace WP_Rocket\Tests\Unit\Subscriber\Addons\VarnishSubscriber;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Subscriber\Addons\Varnish\VarnishSubscriber;
use Brain\Monkey\Functions;

class TestCleanDomain extends TestCase {
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    public function testShouldDoNothingWhenVarnishDisabled() {
        $options = $this->createMock('WP_Rocket\Admin\Options_Data');
        $map     = [
            [
                'varnish_auto_purge',
                '',
                1,
            ],
        ];

        $options->method('get')->will($this->returnValueMap($map));

        $varnish = $this->createMock('WP_Rocket\Addons\Varnish\Varnish');

        $varnish_subscriber = new VarnishSubscriber( $varnish, $options );

        $varnish_subscriber->clean_domain( 'wp-rocket/cache', '', 'http://example.org' );
    }

    public function testShouldPurgeOnceWhenVarnishEnabled() {
        $options = $this->createMock('WP_Rocket\Admin\Options_Data');
        $map     = [
            [
                'varnish_auto_purge',
                '',
                1,
            ],
        ];

        $options->method('get')->will($this->returnValueMap($map));

        $varnish = $this->createMock('WP_Rocket\Addons\Varnish\Varnish');

        $varnish_subscriber = new VarnishSubscriber( $varnish, $options );

        $varnish_subscriber->clean_domain( 'wp-rocket/cache', '', 'http://example.org' );
    }
}