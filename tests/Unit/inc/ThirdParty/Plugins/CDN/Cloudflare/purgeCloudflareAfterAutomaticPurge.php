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
 * Test class covering \WP_Rocket\ThirdParty\Plugins\CDN\Cloudflare::purge_cloudflare_after_automatic_purge
 *
 * @group ThirdParty
 * @group CloudflarePlugin
 */
class Test_purgeCloudflareAfterAutomaticPurge extends TestCase {

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

    public function testShouldPurgeWhenPluginActive()
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

        // Expect purge_everything to be called when plugin is active
        $this->facade->shouldReceive('purge_everything')
            ->once();

        $this->cloudflare->purge_cloudflare_after_automatic_purge(
            ['/path/to/cache/file1.html'],
            ['lifespan' => 86400, 'file_age_limit' => 3600]
        );
    }

    public function testShouldNotPurgeWhenPluginInactive()
    {
        // Mock plugin active check
        Functions\expect('is_plugin_active')
            ->with('cloudflare/cloudflare.php')
            ->once()
            ->andReturn(false);

        // Mock empty credentials 
        Functions\when('get_option')->alias(function ($option_name, $default = false) {
            if ($option_name === 'cloudflare_api_email') {
                return '';
            }
            if ($option_name === 'cloudflare_api_key') {
                return '';
            }
            if ($option_name === 'cloudflare_cached_domain_name') {
                return '';
            }
            return $default;
        });

        // Expect no purge when plugin is not active
        $this->facade->shouldNotReceive('purge_everything');

        $this->cloudflare->purge_cloudflare_after_automatic_purge(
            ['/path/to/cache/file1.html'],
            ['lifespan' => 86400, 'file_age_limit' => 3600]
        );
    }
}