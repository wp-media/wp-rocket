<?php
class Test_WP_Rocket extends WP_UnitTestCase {
    function test_activation() {
        $this->assertTrue( is_plugin_active('wp-rocket/wp-rocket.php') );
    }
}
