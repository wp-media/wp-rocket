<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\RUCSS\Admin\Subscriber;

use Mockery;
use WP_Rocket\Engine\Optimization\RUCSS\Admin\Database;
use WP_Rocket\Engine\Optimization\RUCSS\Admin\Settings;
use WP_Rocket\Engine\Optimization\RUCSS\Admin\Subscriber;
use WP_Rocket\Engine\Common\JobManager\Queue\Queue;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS;
use Brain\Monkey\Functions;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\RUCSS\Admin\Subscriber::delete_used_css_on_update_or_delete
 *
 * @group  RUCSS
 */
class Test_DeleteUsedCssOnUpdateOrDelete extends \WP_Rocket\Tests\Unit\TestCase {
	private $settings;
	private $database;
	private $usedCSS;
	private $subscriber;

	public function setUp() : void {
		parent::setUp();

		$this->settings   = Mockery::mock( Settings::class );
		$this->database   = Mockery::mock( Database::class );
		$this->usedCSS    = Mockery::mock( UsedCSS::class );
		$this->subscriber = new Subscriber( $this->settings, $this->database, $this->usedCSS, Mockery::mock( Queue::class ) );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config ) {
		$this->settings
			->shouldReceive( 'is_enabled' )
			->once()
			->andReturn( $config['remove_unused_css'] );

		Functions\when( 'get_permalink' )
			->justReturn( $config['url'] );

		$this->configureDeletion($config);

		$this->subscriber->delete_used_css_on_update_or_delete( $config['post_id'] );
	}

	protected function configureDeletion($config) {
		Functions\expect( 'is_wp_error' )
			->andReturn( $config['wp_error'] );
		$this->usedCSS->shouldReceive( 'delete_used_css' )
			->atMost()
			->once()
			->with( rtrim( $config['url'], '/' ) );
	}
}
