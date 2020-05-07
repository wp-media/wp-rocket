<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\AdminSubscriber;

use Mockery;
use Brain\Monkey\Functions;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\CriticalPath\AdminSubscriber;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\AdminSubscriber::cpcss_section
 * @group  CriticalPath
 */
class Test_CpcssSection extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/CriticalPath/AdminSubscriber/cpcssSection.php';
	protected static $mockCommonWpFunctionsInSetUp = true;

	private $beacon;
	private $options;
	private $subscriber;

	public function setUp() {
		parent::setUp();

		Functions\when( 'get_current_blog_id' )->justReturn( 1 );

		$this->beacon     = Mockery::mock( Beacon::class );
		$this->options    = Mockery::mock( Options_Data::class );
		$this->subscriber = new AdminSubscriber(
			$this->options,
			$this->beacon,
			'wp-content/cache/critical-css/',
			$this->filesystem->getUrl( 'wp-content/plugins/wp-rocket/views/metabox/cpcss' )
		);
	}

	private function getActualHtml() {
		ob_start();
		$this->subscriber->cpcss_section();

		return $this->format_the_html( ob_get_clean() );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDisplayCPCSSSection( $config, $expected ) {
		$this->options->shouldReceive( 'get' )
			->with( 'async_css', 0 )
			->andReturn( $config['options']['async_css'] );

		Functions\when('esc_js')->returnArg();

		Functions\when( 'wp_sprintf_l' )->alias( function ( $pattern, $args ) {
			if ( substr( $pattern, 0, 2 ) != '%l' ) {
				return $pattern;
			}

			if ( empty( $args ) ) {
				return '';
			}

			$l = [
				'between'          => sprintf( '%1$s, %2$s', '', '' ),
				'between_last_two' => sprintf( '%1$s, and %2$s', '', '' ),
				'between_only_two' => sprintf( '%1$s and %2$s', '', '' ),
			];

			$args   = (array) $args;
			$result = array_shift( $args );
			if ( count( $args ) == 1 ) {
				$result .= $l['between_only_two'] . array_shift( $args );
			}

			$i = count( $args );
			while ( $i ) {
				$arg = array_shift( $args );
				$i--;
				if ( 0 == $i ) {
					$result .= $l['between_last_two'] . $arg;
				} else {
					$result .= $l['between'] . $arg;
				}
			}

			return $result . substr( $pattern, 2 );
		} );

		$GLOBALS['post'] = (object) [
			'ID'          => $config['post']['ID'],
			'post_status' => $config['post']['post_status'],
			'post_type'   => $config['post']['post_type'],
		];

		Functions\when( 'get_post_meta' )->justReturn( $config['is_option_excluded'] );
		Functions\when( 'rest_url' )->justReturn( 'http://example.org/wp-rocket/v1/cpcss/post/' . $config['post']['ID'] );
		Functions\when( 'wp_create_nonce' )->justReturn( 'wp_rest_nonce' );

		$this->assertSame(
			$this->format_the_html( $expected ),
			$this->getActualHtml()
		);
		$this->assertSame( 1, did_action( 'rocket_metabox_cpcss_content' ) );
	}
}
