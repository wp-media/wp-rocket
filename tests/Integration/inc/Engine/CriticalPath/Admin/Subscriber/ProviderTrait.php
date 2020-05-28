<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\Admin\Subscriber;

use ReflectionObject;

trait ProviderTrait {
	public function providerTestData() {
		$obj      = new ReflectionObject( $this );
		$filename = $obj->getFileName();

		$dir  = WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/Engine/CriticalPath/Admin/' . self::$class_name . '/';
		$data = $this->getTestData( $dir, basename( $filename, '.php' ) );

		return isset( $data['test_data'] )
			? $data['test_data']
			: $data;
	}
}
