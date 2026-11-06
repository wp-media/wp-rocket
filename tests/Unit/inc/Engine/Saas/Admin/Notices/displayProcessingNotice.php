<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Saas\Admin\Notices;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\Common\Context\ContextInterface;
use WP_Rocket\Engine\Saas\Admin\Notices;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Saas\Admin\Notices::display_processing_notice
 *
 * @group SaaS
 */
class Test_DisplayProcessingNotice extends TestCase {
	private $options;
	protected $atf_context;
	private $notices;
	protected $config;

	public function setUp(): void {
		parent::setUp();

		$this->options     = Mockery::mock( Options_Data::class );
		$this->atf_context = Mockery::mock( ContextInterface::class );
		$this->notices     = new Notices( $this->options, Mockery::mock( Beacon::class ) );

		$this->stubTranslationFunctions();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		Functions\when( 'get_current_screen' )->justReturn( $config['current_screen'] );
		Functions\when( 'current_user_can' )->justReturn( $config['capability'] );

		$this->options->shouldReceive( 'get' )
				->with( 'remove_unused_css', 0 )
				->andReturn( $config['remove_unused_css'] );

		Functions\when('get_transient')->alias(function ($name) use ($config) {
			if('wp_rocket_rucss_errors_count' === $name) {
				return $config['saas_transient'];
			}
			return $config['transient'];
		});

		if ( $expected ) {
			Functions\expect( 'rocket_notice_html' )
				->once();
		} else {
			Functions\expect( 'rocket_notice_html' )->never();
		}

		$this->notices->display_processing_notice();
	}
}
