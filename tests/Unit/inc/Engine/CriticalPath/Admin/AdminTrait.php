<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\Admin;

use Mockery;
use Brain\Monkey\Functions;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\CriticalPath\CriticalCSS;

trait AdminTrait {
	protected        $beacon;
	protected        $options;
	protected        $critical_css;
	protected        $subscriber;

	public function setUpMocks() {
		Functions\when( 'get_current_blog_id' )->justReturn( 1 );

		$this->beacon       = Mockery::mock( Beacon::class );
		$this->options      = Mockery::mock( Options_Data::class );
		$this->critical_css = Mockery::mock( CriticalCSS::class );
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
}
