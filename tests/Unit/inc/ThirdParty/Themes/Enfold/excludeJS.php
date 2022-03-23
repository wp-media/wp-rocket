<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Themes\Enfold;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Themes\Enfold;

/**
 * @covers \WP_Rocket\ThirdParty\Themes\Enfold::exclude_js()
 *
 * @group  ThirdParty
 */
class Test_ExcludeJS extends TestCase
{
    private $subscriber;

    public function setUp(): void
    {
        parent::setUp();
        $this->subscriber = new Enfold();
    }

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnExpected($config, $expected)
    {
		$this->assertEquals($expected['excluded'], $this->subscriber->exclude_js($config['excluded']));
    }
}
