<?php
class SampleTest extends WP_UnitTestCase {
    function testSample() {
        $this->assertTrue( is_plugin_active('wp-rocket/wp-rocket.php') );
    }
}
