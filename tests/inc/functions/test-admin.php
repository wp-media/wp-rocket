<?php
class WP_Rocket_Admin_Functions_Test extends WP_UnitTestCase {
	function test_rocket_sanitize_key() {
		$special_chars = rocket_sanitize_key( 'abcd$.3' );
		$uppercase = rocket_sanitize_key( 'WP_ROCKET-2.9' );
		
		$this->assertEquals( 'abcd3', $special_chars );
		$this->assertEquals( 'WP_ROCKET-29', $uppercase );
	}

	function test_rocket_sanitize_ua() {
		$bad_user_agent = rocket_sanitize_ua( '&iPhone$' );
		$user_agent_regex = rocket_sanitize_ua( '(.*)iPhone 4_S\/Apple-phone.4' );

		$this->assertEquals( 'iPhone', $bad_user_agent );
		$this->assertEquals( '(.*)iPhone 4_S\/Apple-phone.4', $user_agent_regex );
	}
}