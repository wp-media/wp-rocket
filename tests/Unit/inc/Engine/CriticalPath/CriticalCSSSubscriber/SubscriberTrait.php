<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\CriticalCSSSubscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CriticalPath\CriticalCSS;
use WP_Rocket\Engine\CriticalPath\CriticalCSSGeneration;
use WP_Rocket\Engine\CriticalPath\CriticalCSSSubscriber;

trait SubscriberTrait {
	protected $options;
	protected $critical_css;
	protected $subscriber;

	protected function setUpTests( $filesystem = null, $site_id = 1 ) {
		Functions\expect( 'get_current_blog_id' )->andReturn( $site_id );
		Functions\expect( 'home_url' )->once()->with( '/' )->andReturn( 'http://example.com/' );

		$this->options      = Mockery::mock( Options_Data::class );
		$this->critical_css = Mockery::mock( CriticalCSS::class, [
			Mockery::mock( CriticalCSSGeneration::class ),
			$this->options,
			$filesystem,
		] );
		$this->subscriber   = new CriticalCSSSubscriber( $this->critical_css, $this->options, $filesystem );

	}
}
