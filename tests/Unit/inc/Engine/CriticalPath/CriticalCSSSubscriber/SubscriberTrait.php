<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\CriticalCSSSubscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CriticalPath\CriticalCSS;
use WP_Rocket\Engine\CriticalPath\ProcessorService;
use WP_Rocket\Engine\CriticalPath\CriticalCSSGeneration;
use WP_Rocket\Engine\CriticalPath\CriticalCSSSubscriber;
use WP_Rocket\Engine\License\API\User;

trait SubscriberTrait {
	protected $options;
	protected $subscriber;
	protected $critical_css;
	protected $user;
	protected $processor_service;

	protected function setUpTests( $filesystem = null, $site_id = 1 ) {
		Functions\expect( 'get_current_blog_id' )->andReturn( $site_id );
		Functions\expect( 'home_url' )->once()->with( '/' )->andReturn( 'http://example.com/' );

		$this->options      = Mockery::mock( Options_Data::class );
		$this->critical_css = Mockery::mock( CriticalCSS::class, [
			Mockery::mock( CriticalCSSGeneration::class ),
			$this->options,
			$filesystem,
		] );
		$this->processor_service = Mockery::mock( ProcessorService::class );
		$this->user = Mockery::mock(User::class);
		$this->subscriber        = new CriticalCSSSubscriber( $this->critical_css, $this->processor_service, $this->options, $this->user, $filesystem );

	}
}
