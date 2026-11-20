<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Foo;

class Test_Valid {
	public function anonymous() {
		return new class() {};
	}
}
