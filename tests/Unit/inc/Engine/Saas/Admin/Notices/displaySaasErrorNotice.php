<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Saas\Admin\Notices;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\Common\Context\ContextInterface;
use WP_Rocket\Engine\Saas\Admin\Notices;
use WP_Rocket\Tests\Unit\FilesystemTestCase;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * Test class covering \WP_Rocket\Engine\Saas\Admin\Notices::display_saas_error_notice
 * 
 * @group Saas
 */
class Test_DisplaySaasErrorNotice extends FilesystemTestCase
{
	protected $path_to_test_data = '/inc/Engine/Saas/Admin/Notices/displaySaasErrorNotice.php';

	protected $notices;
	protected $options;
	protected $beacon;
	protected $atf_context;

	protected function setUp(): void
	{
		parent::setUp();
		$this->options = Mockery::mock(Options_Data::class);
		$this->beacon = Mockery::mock(Beacon::class);
		$this->atf_context  = Mockery::mock( ContextInterface::class );
		$this->notices = new Notices($this->options, $this->beacon, $this->atf_context);
		$this->stubTranslationFunctions();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnAsExpected($config, $expected) {
		Functions\when( 'get_current_screen' )->justReturn( $config['current_screen'] );
		Functions\when( 'current_user_can' )->justReturn( $config['has_rights'] );
		Functions\when( 'get_current_user_id' )->justReturn( 1 );
		Functions\when( 'get_user_meta' )->justReturn( $config['boxes'] );
		Functions\expect('get_transient')->with('wp_rocket_rucss_errors_count' )->andReturn($config['saas_transient']);
		Functions\when('rocket_notice_html')->justEcho();
		Functions\stubEscapeFunctions();
		$this->beacon->shouldReceive( 'get_suggest' )->with( 'rucss_firewall_ips' )->andReturn( $config['beacon']['en'] );

		ob_start();
		$this->notices->display_saas_error_notice();
		$result = ob_get_clean();
		$this->assertSame($expected, $result);
	}
}
