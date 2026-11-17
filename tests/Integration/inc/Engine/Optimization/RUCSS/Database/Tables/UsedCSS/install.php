<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\RUCSS\Database\Tables\UsedCSS;

use WP_Rocket\Engine\Optimization\RUCSS\Database\Tables\UsedCSS as UsedCSS_Table;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\RUCSS\Database\Tables\UsedCSS::install
 *
 * @group RUCSS
 */
class Test_Install extends TestCase {
	private $options_api;

	public function set_up() {
		parent::set_up();

		$this->options_api = apply_filters( 'rocket_container', null )->get( 'options_api' );
		$this->options_api->delete( UsedCSS_Table::DEDUPED_OPTION );
	}

	public function tear_down() {
		self::uninstallUsedCssTable();
		$this->options_api->delete( UsedCSS_Table::DEDUPED_OPTION );

		parent::tear_down();
	}

	public function testShouldFlagFreshTableAsDeduplicated() {
		$this->assertFalse( $this->options_api->get( UsedCSS_Table::DEDUPED_OPTION, false ) );

		self::installUsedCssTable();

		$this->assertEquals( 1, $this->options_api->get( UsedCSS_Table::DEDUPED_OPTION, false ) );
	}
}
