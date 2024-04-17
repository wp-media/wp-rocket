<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\O2Switch;

use WP_Rocket\Tests\Integration\TestCase;
use WP_Rocket\ThirdParty\Hostings\O2Switch;

class Test_AddPurgeHeaders extends TestCase
{
    /**
     * @dataProvider configTestData
     */
    public function testShouldDoExpected($input, $expected)
    {
        $constants = isset($input['constants']) ? $input['constants'] : [];
        foreach ($constants as $constant => $value) {
            $this->constants[$constant] = $value;
        }
        $headers = isset($input['headers']) ? $input['headers'] : [];
        $this->assertSame($expected, apply_filters('rocket_varnish_purge_headers', $headers));
    }
}
