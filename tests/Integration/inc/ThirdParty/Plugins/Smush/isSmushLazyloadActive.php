<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\Smush;


class Test_IsSmushLazyloadActive extends SmushSubscriberTestCase
{
    /**
     * @dataProvider addDataProviderThatShouldNotDisableWPRocketLazyLoad
     */
    public function testShouldNotDisableWPRocketLazyLoad($lazyload_enabled, array $lazyload_formats)
    {
        $this->setSmushSettings($lazyload_enabled, $lazyload_formats);
        $this->assertNotContains('Smush', $this->subscriber->is_smush_lazyload_active([]));
    }
    /**
     * @dataProvider addDataProviderThatShouldDisableWPRocketLazyLoad
     */
    public function testShouldDisableWPRocketLazyLoadWhenAtLeastOneImageFormat($lazyload_enabled, array $lazyload_formats)
    {
        $this->setSmushSettings($lazyload_enabled, $lazyload_formats);
        $this->assertContains('Smush', $this->subscriber->is_smush_lazyload_active([]));
    }
    public function addDataProviderThatShouldNotDisableWPRocketLazyLoad()
    {
        return $this->getTestData(__DIR__, 'isSmushLazyloadActiveNotDisable');
    }
    public function addDataProviderThatShouldDisableWPRocketLazyLoad()
    {
        return $this->getTestData(__DIR__, 'isSmushLazyloadActiveDisable');
    }
}
