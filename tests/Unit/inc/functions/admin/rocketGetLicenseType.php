<?php

namespace WP_Rocket\Tests\Unit\inc\functions\admin;

use WP_Rocket\Tests\Unit\TestCase;

class Test_RocketGetLicenseType extends TestCase {

    public function setUp(): void {
        parent::setUp();
    }

    /**
     * @dataProvider configTestData
     */
    public function testReturnAsExpected($config, $expected) {
        $this->stubTranslationFunctions();
        // Extract 'customer_data' from the config
        $customer_data = $config['customer_data'];
        $result = rocket_get_license_type($customer_data);

        $this->assertEquals($expected, $result);
    }
}
