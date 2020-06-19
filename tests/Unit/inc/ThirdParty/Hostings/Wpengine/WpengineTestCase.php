<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\Wpengine;

use Mockery;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\Wpengine;

abstract class WpengineTestCase extends TestCase {
	protected $wpengine;
	protected $wpe_common_mock;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/ThirdParty/Hostings/Wpengine/wpe_param.php';
		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/ThirdParty/Hostings/Wpengine/WpeCommon.php';
	}

	public function setup() {
		parent::setup();
		$this->wpengine        = new Wpengine();
		$this->wpe_common_mock = Mockery::mock( WpeCommon::class );
	}
}
