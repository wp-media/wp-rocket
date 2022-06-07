<?php

namespace WP_Rocket\Tests\Fixtures\inc\ThirdParty\Plugins\Jetpack;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\Jetpack;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

class Test_RemoveJetpackCookieLawMandatoryCookie extends TestCase
{
	protected $option;
	protected $subscriber;

	protected function setUp(): void
	{
		parent::setUp();
		$this->option = Mockery::mock(Options_Data::class);
		$this->subscriber = new Jetpack($this->option);
	}

	public function testShouldDoAsExpected() {
		Filters\expectRemoved('rocket_htaccess_mod_rewrite')->with('__return_false', 76);
		Filters\expectRemoved('rocket_cache_mandatory_cookies')->with('_rocket_add_eu_cookie_law_mandatory_cookie');
		Functions\expect('flush_rocket_htaccess');
		Functions\expect('rocket_generate_config_file');
		Functions\expect('delete_option')->with('rocket_jetpack_eu_cookie_widget');
		$this->subscriber->remove_jetpack_cookie_law_mandatory_cookie();
	}
}
