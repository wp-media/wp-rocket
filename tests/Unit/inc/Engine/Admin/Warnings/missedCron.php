<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\Warnings;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Warnings;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Admin\Warnings::missed_cron
 * @group  Warnings
 */
class Test_MissedCron extends TestCase {
	protected static $mockCommonWpFunctionsInSetUp = true;
	protected $options;
	private $warnings;

	public function setUp() {
		parent::setUp();

		$this->options  = Mockery::mock( Options_Data::class );
		$this->warnings = new Warnings( $this->options );
	}

	private function getActualHtml() {
		ob_start();
		$this->warnings->missed_cron();

		return $this->format_the_html( ob_get_clean() );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnNullWhenNothingToDisplay( $config ) {
		Functions\when( 'current_user_can' )->justReturn( $config['cap'] );
		Functions\when( 'get_current_screen' )->alias( function() use ( $config ) {
			return (object) [
				'id' => $config['screen'],
			];
		} );
		Functions\when( 'get_current_user_id' )->justReturn( 1 );
		Functions\when( 'get_user_meta' )->justReturn( $config['dismissed'] );

		$this->options->shouldReceive( 'get' )
			->atMost()
			->times( 4 )
			->andReturnValues( $config['options'] );

		Functions\expect( 'wp_next_scheduled' )
			->atMost()
			->times( 5 )
			->andReturnValues( $config['events'] );

		Functions\when( 'rocket_notice_html' )->alias( function( $args ) {
			echo '<div class="notice notice-warning ">' . $args['message'] . '<p><a class="rocket-dismiss" href="http://example.org/admin-post.php?action=rocket_ignore&box=rocket_warning_cron">Dismiss this notice</a></p></div>';
		} );

		if ( empty( $config['expected'] ) ) {
			$this->assertNull( $this->warnings->missed_cron() );
		} else {
			$this->assertSame(
				$this->format_the_html( $config['expected'] ),
				$this->getActualHtml()
			);
		}
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'missed-cron' );
	}
}
