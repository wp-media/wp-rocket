<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\RUCSS\Admin\Subscriber;

use WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\UsedCSS;
use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Admin\Subscriber::delete_used_css_on_update_or_delete
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS::delete_used_css
 *
 * @group  RUCSS
 */
class Test_DeleteUsedCssOnUpdateOrDelete extends FilesystemTestCase{
	use DBTrait;

	protected $path_to_test_data = '/inc/Engine/Optimization/RUCSS/Admin/Subscriber/deleteUsedCssOnUpdateOrDelete.php';

	private $posts;
	private $input;

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

	public function tear_down() : void {
		remove_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'set_rucss_option' ] );
		UsedCSS::$table_exists = false;

		parent::tear_down();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldTruncateTableWhenOptionIsEnabled( $input ){
		$container           = apply_filters( 'rocket_container', null );
		$rucss_usedcss_query = $container->get( 'wp_rocket.engine.optimization.rucss.serviceprovider.rucss_used_css_query' );
		$rucss_usedcss_query = $container->get( 'wp_rocket.engine.optimization.rucss.serviceprovider.rucss_used_css_query' );
		$subscriber          = $container->get( 'wp_rocket.engine.optimization.rucss.serviceprovider.rucss_admin_subscriber' );
		$event_manager       = $container->get( 'event_manager' );

		$event_manager->remove_callback( 'permalink_structure_changed', [ $subscriber, 'truncate_used_css' ] );

		$this->input = $input;
		add_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'set_rucss_option' ] );
		$this->set_permalink_structure( "/%postname%/" );

		foreach ( $input['items'] as $key => $item ) {
			$post          = $this->factory->post->create_and_get([
				'post_name' => 'slug_' . $key
			]);

			$url           = untrailingslashit( get_permalink( $post->ID ) );
			$item['url']   = $url;
			$this->posts[] = $post;

			$rucss_usedcss_query->add_item( $item );
		}

		$result = $rucss_usedcss_query->query();
		$this->assertCount( count( $input['items'] ), $result );

		foreach ( $input['files_deleted'] as $file ) {
			$this->assertTrue( $this->filesystem->exists( $file ) );
		}

		foreach ( $input['files_preserved'] as $file ) {
			$this->assertTrue( $this->filesystem->exists( $file ) );
		}

		foreach ( $this->posts as $post ) {
			do_action( 'delete_post', $post->ID );
		}

		$rucss_usedcss_query = $container->get( 'wp_rocket.engine.optimization.rucss.serviceprovider.rucss_used_css_query' );
		$resultAfterTruncate = $rucss_usedcss_query->query();

		if ( $this->input['remove_unused_css'] ) {
			$this->assertCount( 0, $resultAfterTruncate );

			foreach ( $input['files_deleted'] as $file ) {
				$this->assertFalse( $this->filesystem->exists( $file ) );
			}
		} else {
			$this->assertCount( count( $input['items'] ), $result );

			foreach ( $input['files_deleted'] as $file ) {
				$this->asserttrue( $this->filesystem->exists( $file ) );
			}
		}

		foreach ( $input['files_preserved'] as $file ) {
			$this->assertTrue( $this->filesystem->exists( $file ) );
		}
	}

	public function set_rucss_option() {
		return $this->input['remove_unused_css'] ?? false;
	}
}
