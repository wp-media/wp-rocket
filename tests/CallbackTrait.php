<?php

namespace WP_Rocket\Tests;

trait CallbackTrait {

	protected function assertCallbackRegistered( $event, $method, $priority = 10 ) {
		$this->assertTrue( has_action( $event ) );

		global $wp_filter;
		$callbacks = $wp_filter[ $event ]->callbacks;

		$registration = current( $callbacks[ $priority ] );
		$this->assertEquals( $method, $registration['function'][1] );
	}
}
