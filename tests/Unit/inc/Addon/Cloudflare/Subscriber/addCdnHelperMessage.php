<?php

namespace WP_Rocket\Tests\Unit\inc\Addon\Cloudflare\Subscriber;

use Mockery;
use WP_Rocket\Addon\Cloudflare\Subscriber;
use WP_Rocket\Addon\Cloudflare\Cloudflare;
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WPMedia\Cloudflare\Auth\AuthFactoryInterface;


use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Addon\Cloudflare\Subscriber::add_cdn_helper_message
 */
class Test_addCdnHelperMessage extends TestCase {

    /**
     * @var Cloudflare
     */
    protected $cloudflare;

    /**
     * @var Options_Data
     */
    protected $options;

    /**
     * @var Options
     */
    protected $options_api;

    /**
     * @var AuthFactoryInterface
     */
    protected $auth_factory;

    /**
     * @var Subscriber
     */
    protected $subscriber;

    public function set_up() {
        parent::set_up();
        $this->cloudflare = Mockery::mock(Cloudflare::class);
        $this->options = Mockery::mock(Options_Data::class);
        $this->options_api = Mockery::mock(Options::class);
        $this->auth_factory = Mockery::mock(AuthFactoryInterface::class);

        $this->subscriber = new Subscriber($this->cloudflare, $this->options, $this->options_api, $this->auth_factory);
    }

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnAsExpected( $config, $expected )
    {
        $this->assertSame($expected, $this->subscriber->add_cdn_helper_message($config['addons']));

    }
}
