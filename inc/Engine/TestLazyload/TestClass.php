<?php
namespace WP_Rocket\Engine\TestLazyload;

class TestClass {

	public function __construct() {
		error_log( 'log message from TestClass::__construct' );
		error_log( var_export( wp_debug_backtrace_summary(), true ) );
		error_log( '-------------------------' );
	}

	public function test1() {
		error_log( 'log message from TestClass::test1' );
		error_log( var_export( wp_debug_backtrace_summary(), true ) );
		error_log( '-------------------------' );
	}

}
