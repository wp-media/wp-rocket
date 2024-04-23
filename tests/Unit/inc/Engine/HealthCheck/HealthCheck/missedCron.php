<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\HealthCheck\HealthCheck;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\HealthCheck\HealthCheck;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\HealthCheck\HealthCheck::missed_cron
 *
 * @group  HealthCheck
 */
class Test_MissedCron extends TestCase {
	protected        $options;
	private          $health;

	public function setUp() : void {
		parent::setUp();

		Functions\stubTranslationFunctions();

		$this->options = Mockery::mock( Options_Data::class );
		$this->health  = new HealthCheck( $this->options );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnNullWhenNothingToDisplay( $config, $expected ) {
		$this->configUser( $config );
		$this->configOptions( $config );

		Functions\when( 'get_current_screen' )->alias(
			function () use ( $config ) {
				return (object) [
					'id' => $config['screen'],
				];
			}
		);

		Functions\expect( 'wp_next_scheduled' )
			->atMost()
			->times( 5 )
			->andReturnValues( $config['events'] );

		Functions\when( 'rocket_notice_html' )->alias(
			function ( $args ) {
				echo '<div class="notice notice-warning ">' . $args['message'] . '<p><a class="rocket-dismiss " href="http://example.org/wp-admin/admin-post.php?action=rocket_ignore&amp;box=rocket_warning_cron&amp;_wpnonce=123456">Dismiss this notice</a></p></div>';
			}
		);

		$expected = empty( $expected )
			? $expected
			: $this->format_the_html( $expected );

		$this->assertSame(
			$expected,
			$this->getActualHtml()
		);
	}

	protected function configUser( $config ) {
		Functions\when( 'current_user_can' )->justReturn( $config['cap'] );
		Functions\when( 'get_current_user_id' )->justReturn( 1 );
		Functions\when( 'get_user_meta' )->justReturn( $config['dismissed'] );
	}

	protected function configOptions( $config ) {
		$this->disable_wp_cron = $config['disable_cron'];

		$this->options->shouldReceive( 'get' )
		        ->atMost()
		        ->times( 4 )
		        ->andReturnValues( $config['options'] );
	}

	private function getActualHtml() {
		ob_start();
		$this->health->missed_cron();
		$actual = ob_get_clean();

		return empty( $actual )
			? $actual
			: $this->format_the_html( $actual );
	}
}
