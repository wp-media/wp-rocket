<?php
namespace WP_Rocket\Tests\Unit\Subscriber\Addons\VarnishSubscriber;

use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Subscriber\Addons\Varnish\VarnishSubscriber;
use Brain\Monkey\Functions;

/**
 * @group Varnish
 * @group Addons
 * @group Subscriber
 */
class TestCleanFile extends TestCase {
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

        $varnish_subscriber->clean_file( 'http://example.org/about/' );
    }

    public function testShouldPurgeOnceWhenVarnishEnabled() {
        $options = $this->createMock('WP_Rocket\Admin\Options_Data');
        $map     = [
            [
                'varnish_auto_purge',
                0,
                1,
            ],
        ];

        $options->method('get')->will($this->returnValueMap($map));

        $varnish = \Mockery::mock(\WP_Rocket\Addons\Varnish\Varnish::class);
        $varnish->shouldReceive('purge')->once()->with('http://example.org/about/?regex');

        $varnish_subscriber = new VarnishSubscriber( $varnish, $options );

        $varnish_subscriber->clean_file( 'http://example.org/about/' );
    }
}
