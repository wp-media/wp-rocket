<?php
class Test_WP_Rocket extends WP_UnitTestCase {
	public function test_constants() {
		$this->assertSame( WP_ROCKET_VERSION, '2.10.6' );
	}
}
