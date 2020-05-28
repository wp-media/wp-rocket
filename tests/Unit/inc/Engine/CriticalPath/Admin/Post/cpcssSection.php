<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\Admin\Post;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\CriticalPath\Admin\Post;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\Admin\AdminTrait;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\Admin\Post::cpcss_section
 *
 * @group  CriticalPath
 * @group  CriticalPathPost
 */
class Test_CpcssSection extends TestCase {
	use AdminTrait;

	protected static $mockCommonWpFunctionsInSetUp = true;

	private $post;

	protected function setUp() {
		parent::setUp();

		$this->setUpMocks();

		Functions\when( 'wp_sprintf_l' )->alias(
			function( $pattern, $args ) {
				return $this->wp_sprintf_l( $pattern, $args );
			}
		);

		$this->post = Mockery::mock( Post::class . '[generate]', [
				$this->options,
				$this->beacon,
				'wp-content/cache/critical-css/',
				WP_ROCKET_PLUGIN_ROOT . 'views/cpcss/',
			]
		);
	}

	protected function tearDown() {
		unset( $GLOBALS['post'] );
		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDisplayCPCSSSection( $config, $expected ) {
		$this->setUpTest( $config );

		$this->post->shouldReceive( 'generate' )
				   ->with( 'metabox/container', $expected['data'] )
				   ->andReturn( '' );

		ob_start();
		$this->post->cpcss_section();
		ob_get_clean();
	}

	public function wp_sprintf_l( $pattern, $args ) {
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
			$i --;
			if ( 0 == $i ) {
				$result .= $l['between_last_two'] . $arg;
			} else {
				$result .= $l['between'] . $arg;
			}
		}

		return $result . substr( $pattern, 2 );
	}
}
