<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\DomainChange\Subscriber;

use Brain\Monkey\{Actions, Functions};
use Mockery;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\Admin\DomainChange\Subscriber;
use WP_Rocket\Engine\Common\Ajax\AjaxHandler;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\DomainChange\Subscriber::regenerate_configuration
 *
 * @group DomainChange
 */
class Test_RegenerateConfiguration extends TestCase {
	protected $ajax_handler;
	protected $beacon;
	protected $subscriber;

	public function set_up() {
		parent::set_up();

		$this->ajax_handler = Mockery::mock( AjaxHandler::class );
		$this->beacon       = Mockery::mock( Beacon::class );
		$this->subscriber   = new Subscriber( $this->ajax_handler, $this->beacon );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( $config, $expected ) {
		Functions\expect( 'get_option' )
			->with( 'home' )
			->andReturn( $config['home_url'] );
		Functions\when( 'trailingslashit' )->returnArg();

		$this->ajax_handler->shouldReceive( 'validate_referer' )
			->with( 'rocket_regenerate_configuration', 'rocket_manage_options' )
			->andReturn( $config['is_validated'] );

		if ( $config['is_validated'] ) {
			Functions\expect( 'get_transient' )
				->with( 'rocket_domain_changed' )
				->andReturn( $config['transient'] );
		} else {
			Functions\expect( 'get_transient' )
				->never();
		}

		if ( $config['is_validated'] && $config['transient'] ) {
			Actions\expectDone( 'rocket_domain_changed' )
				->with( $config['home_url'], $config['last_base_url'] );

			Functions\expect( 'delete_transient' )
				->with( 'rocket_domain_changed' );

			$this->ajax_handler->shouldReceive( 'redirect' );
		} else {
			Functions\expect( 'delete_transient' )->never();
		}

		$this->subscriber->regenerate_configuration();
	}
}
