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
 * Test class covering \WP_Rocket\Engine\Saas\Admin\Notices::display_success_notice
 *
 * @group SaaS
 */
class Test_DisplaySuccessNotice extends TestCase {
	private $options;
	private $beacon;
	private $notices;
	protected $atf_context;

	public function setUp(): void {
		parent::setUp();

		$this->options     = Mockery::mock( Options_Data::class );
		$this->beacon      = Mockery::mock( Beacon::class );
		$this->atf_context = Mockery::mock( ContextInterface::class );
		$this->notices     = new Notices( $this->options, $this->beacon, $this->atf_context );

		$this->stubTranslationFunctions();
		$this->stubEscapeFunctions();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		Functions\when( 'get_current_screen' )->justReturn( $config['current_screen'] );
		Functions\when( 'current_user_can' )->justReturn( $config['capability'] );
		Functions\when( 'get_current_user_id' )->justReturn( 1 );
		Functions\when( 'get_user_meta' )->justReturn( $config['boxes'] );
		Functions\when('get_transient')->alias(function ($name) use ($config) {
			if('wp_rocket_rucss_errors_count' === $name) {
				return $config['saas_transient'];
			}
			return $config['transient'];
		});

		$this->configureDisplayNotice($config);

		if ( $expected ) {
			Functions\expect( 'rocket_notice_html' )
				->with(
					$expected
				);
		} else {
			Functions\expect( 'rocket_notice_html' )->never();
		}

		$this->notices->display_success_notice();
	}

	public function configureDisplayNotice($config) {

		if( $config['saas_transient'] ) {
			return;
		}

		$this->options->shouldReceive( 'get' )
			->with( 'manual_preload', 0 )
			->andReturn( $config['manual_preload'] );

		$this->beacon->shouldReceive( 'get_suggest' )
			->andReturn(
				[
					'id' => 123,
					'url' => 'http://example.org',
				]
			);
	}
}
