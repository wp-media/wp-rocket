<?php
namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\RUCSS\Controller\UsedCSS;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Cache\Purge;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\UsedCSS as UsedCSS_Query;
use WP_Rocket\Engine\Optimization\RUCSS\Frontend\APIClient;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS::safelist_files_contains
 *
 * @group  RUCSS
 */
class Test_SafelistFilesContain extends TestCase {

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( $config, $expected ): void {
		$options        = Mockery::mock( Options_Data::class );
		$used_css_query = Mockery::mock( UsedCSS_Query::class );
		$purge          = Mockery::mock( Purge::class );
		$api_client     = Mockery::mock( APIClient::class );

		$options->shouldReceive( 'get' )->with( 'remove_unused_css_safelist', [] )->once()->andReturn( $config['safelist'] );

		$used_css = new UsedCSS( $options, $used_css_query, $purge, $api_client );
		$this->assertSame( $expected, $used_css->safelist_files_contains( $config['url'] ) );
	}

}
