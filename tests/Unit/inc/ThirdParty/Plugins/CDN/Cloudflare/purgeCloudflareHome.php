<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\CDN\Cloudflare;

use Mockery;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\ThirdParty\Plugins\CDN\{Cloudflare,CloudflareFacade};
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Admin\Options;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\CDN\Cloudflare::purge_cloudflare_home
 *
 * @group ThirdParty
 * @group CloudflarePlugin
 */
class Test_purgeCloudflareHome extends TestCase {

    /**
     * @var Options_Data
     */
    protected $options;

    /**
     * @var Beacon
     */
    protected $beacon;

    /**
     * @var Options
     */
    protected $option_api;

    /**
     * @var CloudflareFacade|\Mockery\MockInterface
     */
    protected $facade;

    /**
     * @var Cloudflare
     */
    protected $cloudflare;

    public function set_up() {
        parent::set_up();
        
        $this->options = Mockery::mock(Options_Data::class);
        $this->option_api = Mockery::mock(Options::class);
        $this->beacon = Mockery::mock(Beacon::class);
        $this->facade = Mockery::mock(CloudflareFacade::class);

        $this->cloudflare = new Cloudflare($this->options, $this->option_api, $this->beacon, $this->facade);
    }

    public function testShouldNotPurgeWhenPluginInactive()
    {
        // Mock plugin active check
        Functions\expect('is_plugin_active')
            ->with('cloudflare/cloudflare.php')
            ->once()
            ->andReturn(false);

        // Expect no purge when plugin is not active
        $this->facade->shouldNotReceive('purge_urls');

        $this->cloudflare->purge_cloudflare_home('en');
    }

    public function testShouldPurgeSpecificPostIdWhenFound()
    {
        // Mock plugin active check
        Functions\expect('is_plugin_active')
            ->with('cloudflare/cloudflare.php')
            ->once()
            ->andReturn(true);

        // Mock Cloudflare credentials to simulate proper setup
        Functions\when('get_option')->alias(function ($option_name, $default = false) {
            if ($option_name === 'cloudflare_api_email') {
                return 'test@example.com';
            }
            if ($option_name === 'cloudflare_api_key') {
                return 'test-api-key';
            }
            if ($option_name === 'cloudflare_cached_domain_name') {
                return 'example.com';
            }
            return $default;
        });

        // Mock getting home URL and converting to post ID
        Functions\expect('get_rocket_i18n_home_url')
            ->with('en')
            ->once()
            ->andReturn('https://example.com/en/');

        Functions\expect('url_to_postid')
            ->with('https://example.com/en/')
            ->once()
            ->andReturn(123);

        // Expect purge_urls to be called with specific post ID
        $this->facade->shouldReceive('purge_urls')
            ->with(123)
            ->once();

        $this->cloudflare->purge_cloudflare_home('en');
    }

    public function testShouldPurgeStaticFrontPageWhenUrlToPostIdReturnsZero()
    {
        // Mock plugin active check
        Functions\expect('is_plugin_active')
            ->with('cloudflare/cloudflare.php')
            ->once()
            ->andReturn(true);

        // Mock Cloudflare credentials
        Functions\when('get_option')->alias(function ($option_name, $default = false) {
            switch ($option_name) {
                case 'cloudflare_api_email':
                    return 'test@example.com';
                case 'cloudflare_api_key':
                    return 'test-api-key';
                case 'cloudflare_cached_domain_name':
                    return 'example.com';
                case 'show_on_front':
                    return 'page';
                case 'page_on_front':
                    return 5;
                default:
                    return $default;
            }
        });

        // Mock getting home URL and url_to_postid returning 0
        Functions\expect('get_rocket_i18n_home_url')
            ->with('')
            ->once()
            ->andReturn('https://example.com/');

        Functions\expect('url_to_postid')
            ->with('https://example.com/')
            ->once()
            ->andReturn(0);

        // Mock get_post to return a valid WP_Post object
        $post_mock = Mockery::mock('\WP_Post');
        Functions\expect('get_post')
            ->with(5)
            ->once()
            ->andReturn($post_mock);

        // Expect purge_urls to be called with the front page post ID
        $this->facade->shouldReceive('purge_urls')
            ->with(5)
            ->once();

        $this->cloudflare->purge_cloudflare_home('');
    }

    public function testShouldNotPurgeWhenStaticPageNotFound()
    {
        // Mock plugin active check
        Functions\expect('is_plugin_active')
            ->with('cloudflare/cloudflare.php')
            ->once()
            ->andReturn(true);

        // Mock Cloudflare credentials
        Functions\when('get_option')->alias(function ($option_name, $default = false) {
            switch ($option_name) {
                case 'cloudflare_api_email':
                    return 'test@example.com';
                case 'cloudflare_api_key':
                    return 'test-api-key';
                case 'cloudflare_cached_domain_name':
                    return 'example.com';
                case 'show_on_front':
                    return 'page';
                case 'page_on_front':
                    return 5;
                default:
                    return $default;
            }
        });

        // Mock getting home URL and url_to_postid returning 0
        Functions\expect('get_rocket_i18n_home_url')
            ->with('')
            ->once()
            ->andReturn('https://example.com/');

        Functions\expect('url_to_postid')
            ->with('https://example.com/')
            ->once()
            ->andReturn(0);

        // Mock get_post to return null (post not found)
        Functions\expect('get_post')
            ->with(5)
            ->once()
            ->andReturn(null);

        // Expect no purge when static page is not found
        $this->facade->shouldNotReceive('purge_urls');

        $this->cloudflare->purge_cloudflare_home('');
    }
}