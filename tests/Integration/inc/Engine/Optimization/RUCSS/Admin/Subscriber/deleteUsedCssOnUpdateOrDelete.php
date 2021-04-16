<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\RUCSS\Admin\Subscriber;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Admin\Subscriber::delete_used_css_on_update_or_delete
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS::delete_used_css
 *
 * @group  RUCSS
 */
class Test_DeleteUsedCssOnUpdateOrDelete extends TestCase{
	use DBTrait;

	private $posts;
	private $input;

	public static function setUpBeforeClass(): void {
		self::installFresh();

		parent::setUpBeforeClass();
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();

		self::uninstallAll();
	}

	public function tearDown() : void {
		remove_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'set_rucss_option' ] );

		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldTruncateTableWhenOptionIsEnabled( $input ){
		$container           = apply_filters( 'rocket_container', null );
		$rucss_usedcss_query = $container->get( 'rucss_used_css_query' );

		$this->input = $input;
		add_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'set_rucss_option' ] );

		foreach ( $input['items'] as $item ) {
			$post          = $this->factory->post->create_and_get();
			$url           = untrailingslashit( get_permalink( $post->ID ) );
			$item['url']   = $url;
			$this->posts[] = $post;

			$rucss_usedcss_query->add_item( $item );
		}

		$result = $rucss_usedcss_query->query();
		$this->assertCount( count( $input['items'] ), $result );

		foreach ( $this->posts as $post ) {
			do_action( 'delete_post', $post->ID );
		}

		$rucss_usedcss_query = $container->get( 'rucss_used_css_query' );
		$resultAfterTruncate = $rucss_usedcss_query->query();

		if ( $this->input['remove_unused_css'] ) {
			$this->assertCount( 0, $resultAfterTruncate );
		} else {
			$this->assertCount( count( $input['items'] ), $result );
		}
	}

	public function set_rucss_option() {
		return $this->input['remove_unused_css'] ?? false;
	}
}
