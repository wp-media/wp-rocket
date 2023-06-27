<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Common\ExtractCSS\Subscriber;

use WP_Rocket\Engine\Common\ExtractCSS\Subscriber;


use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Common\ExtractCSS\Subscriber::extract_css_files_from_html
 */
class Test_extractCssFilesFromHtml extends TestCase {

    /**
     * @var Subscriber
     */
    protected $subscriber;

    public function set_up() {
        parent::set_up();

        $this->subscriber = new Subscriber();
    }

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnAsExpected( $config, $expected )
    {
        $this->assertSame($expected, $this->subscriber->extract_css_files_from_html($config['data']));
    }
}
