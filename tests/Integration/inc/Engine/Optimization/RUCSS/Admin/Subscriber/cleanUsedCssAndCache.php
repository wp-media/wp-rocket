<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\RUCSS\Admin\Subscriber;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Admin\Subscriber::clean_used_css_and_cache
 *
 * @group  RUCSS
 */
class Test_CleanUsedCssAndCache extends TestCase {
	use DBTrait;

	private $input;

	public static function set_up_before_class() {
		self::installFresh();

		parent::set_up_before_class();
	}

	public static function tear_down_after_class() {
		parent::tear_down_after_class();

		self::uninstallAll();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $input ) {
		$container              = apply_filters( 'rocket_container', null );
		$rucss_usedcss_query   = $container->get( 'rucss_used_css_query' );

		$this->input = $input;

		foreach ( $input['items'] as $item ) {
			$rucss_usedcss_query->add_item( $item );
		}
		$result = $rucss_usedcss_query->query();

		$this->assertCount( count( $input['items'] ), $result );

		do_action( 'update_option_wp_rocket_settings', $input['settings'], $input['old_settings'] );

		$rucss_usedcss_query = $container->get( 'rucss_used_css_query' );
		$resultAfterTruncate = $rucss_usedcss_query->query();

		if (
			isset( $input['settings']['remove_unused_css_safelist'], $input['old_settings']['remove_unused_css_safelist'] )
			&&
			$input['settings']['remove_unused_css_safelist'] !== $input['old_settings']['remove_unused_css_safelist']
		 ) {
			$this->assertCount( 0, $resultAfterTruncate );
		} else {
			$this->assertCount( count( $input['items'] ), $result );
		}
	}
}
