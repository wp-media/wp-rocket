<?php
class SampleTest extends WP_UnitTestCase {
    function testActivation() {
        $this->assertTrue( is_plugin_active('wp-rocket/wp-rocket.php') );
    }
}
