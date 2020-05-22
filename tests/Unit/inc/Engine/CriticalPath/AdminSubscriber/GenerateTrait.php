<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\AdminSubscriber;

use Mockery;
use Brain\Monkey\Functions;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\CriticalPath\AdminSubscriber;

trait GenerateTrait {
	protected        $beacon;
	protected        $options;
	protected        $subscriber;

	public function setUpMocks() {
		Functions\when( 'get_current_blog_id' )->justReturn( 1 );

		$this->beacon     = Mockery::mock( Beacon::class );
		$this->options    = Mockery::mock( Options_Data::class );
		$this->subscriber = Mockery::mock( AdminSubscriber::class . '[generate]', [
				$this->options,
				$this->beacon,
				'wp-content/cache/critical-css/',
				WP_ROCKET_PLUGIN_ROOT . 'views/cpcss/',
			]
		);
	}

	protected function setUpTest( $config ) {
		$GLOBALS['post'] = $config['post'];
		if ( isset( $config['options']['async_css'] ) ) {
			$this->options
				->shouldReceive( 'get' )
				->with( 'async_css', 0 )
				->andReturn( $config['options']['async_css'] );
		}
		if ( isset( $config['options']['async_css_mobile'] ) ) {
			$this->options
				->shouldReceive( 'get' )
				->with( 'async_css_mobile', 0 )
				->andReturn( $config['options']['async_css_mobile'] );
		}
		Functions\when( 'get_post_meta' )->justReturn( $config['is_option_excluded'] );
	}

	protected function setUpGenerate( $view, $data ) {
		$this->subscriber
			->shouldReceive( 'generate' )
			->with( $view, $data )
			->andReturn( '' );
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
