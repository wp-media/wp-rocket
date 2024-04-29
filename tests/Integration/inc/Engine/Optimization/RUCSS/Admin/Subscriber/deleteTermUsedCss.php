<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\RUCSS\Admin\Subscriber;

use WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\UsedCSS;
use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\RUCSS\Admin\Subscriber::delete_term_used_css
 *
 * @group  RUCSS
 */
class Test_DeleteTermUsedCss extends TestCase {
	use DBTrait;

	private $rucss_option;
	protected $rucss_enabled;

	public static function set_up_before_class() {
		self::installFresh();

		parent::set_up_before_class();
	}

	public static function tear_down_after_class() {
		parent::tear_down_after_class();

		self::uninstallAll();
	}

	public function set_up() {
		parent::set_up();
		UsedCSS::$table_exists = true;
	}

	public function tear_down() {
		remove_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'set_rucss_option' ] );
		remove_filter( 'rocket_rucss_deletion_activated', [ $this, 'set_rucss_enabled' ] );
		UsedCSS::$table_exists = false;

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config ) {
		$container           = apply_filters( 'rocket_container', null );
		$rucss_usedcss_query = $container->get( 'rucss_used_css_query' );

		$this->rucss_option = $config['remove_unused_css'];
		if(key_exists('is_disabled', $config)) {
			$this->rucss_enabled = $config['is_disabled'];
		}
		$this->set_permalink_structure( "/%postname%/" );

		$term = $this->factory->term->create_and_get([
			'name' => 'test_taxonomy'
		]);

		$item = [
			'url' => untrailingslashit( get_term_link( $term->term_id ) ),
		];

		$rucss_usedcss_query->add_item( $item );

		$result = $rucss_usedcss_query->query();
		$this->assertCount( 1, $result );

		add_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'set_rucss_option' ] );
		add_filter( 'rocket_saas_deletion_enabled', [ $this, 'set_rucss_enabled' ] );

		if ( $config['wp_error'] ) {
			do_action( 'edit_term', 0, 0, 'category' );

		} else {
			do_action( 'edit_term', $term->term_id, $term->term_taxonomy_id, $term->taxonomy );
		}


		$rucss_usedcss_query = $container->get( 'rucss_used_css_query' );
		$resultAfterDelete = $rucss_usedcss_query->query();

		if ( $config['removed'] ) {
			$this->assertCount( 0, $resultAfterDelete );
		} else {
			$this->assertCount( 1, $resultAfterDelete );
		}
	}

	public function set_rucss_option() {
		return $this->rucss_option;
	}

	public function set_rucss_enabled() {
		return $this->rucss_enabled;
	}
}
