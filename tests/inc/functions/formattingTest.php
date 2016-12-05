<?php
class formattingTest extends WP_UnitTestCase {
	function test_rocket_clean_exclude_file() {
		$path = rocket_clean_exclude_file( 'http://www.geekpress.fr/referencement-wordpress/' );
		$this->assertEquals( '/referencement-wordpress/', $path );
	}
}