<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\WPEngine;

use Mockery;
use WpeCommon;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\WPEngine;

abstract class WPEngineTestCase extends TestCase {
	protected $wpengine;
	protected $wpe_common_mock;

	public static function setUpBeforeClass() : void {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/ThirdParty/Hostings/WPEngine/wpe_param.php';
		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/ThirdParty/Hostings/WPEngine/WpeCommon.php';
	}

	public function setUp() : void {
		parent::setup();
		$this->wpengine        = new WPEngine();
		$this->wpe_common_mock = Mockery::mock( WpeCommon::class );
	}
}
