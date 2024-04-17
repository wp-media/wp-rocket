<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\Subscriber;


class Test_MaybeReplaceUrl extends TestCase
{
    /**
     * @dataProvider providerTestData
     */
    public function testShouldMaybeReplaceURL($original, $zones, $cdn_urls, $home_url, $expected)
    {
        $this->cnames = $cdn_urls;
        $this->home_url = $home_url;
        add_filter('pre_get_rocket_option_cdn', [$this, 'return_true']);
        add_filter('rocket_cdn_cnames', [$this, 'setCnames']);
        add_filter('home_url', [$this, 'setHomeURL']);
        $this->assertSame($expected, apply_filters('rocket_asset_url', $original, $zones));
    }
    public function providerTestData()
    {
        return $this->getTestData(__DIR__, 'maybe-replace-url');
    }
}
