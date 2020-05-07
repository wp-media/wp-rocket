<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\RocketCDN\DataManagerSubscriber;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Engine\CDN\RocketCDN\APIClient;
use WP_Rocket\Engine\CDN\RocketCDN\CDNOptionsManager;
use WP_Rocket\Engine\CDN\RocketCDN\DataManagerSubscriber;
use Mockery;

/**
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\DataManagerSubscriber::set_process_status
 * @group  RocketCDN
 */
class Test_SetProcessStatus extends TestCase {
	private $data_manager;

	public function setUp() {
		parent::setUp();

		$this->data_manager = new DataManagerSubscriber(
			Mockery::mock( APIClient::class ),
			Mockery::mock( CDNOptionsManager::class )
		);
	}

	public function testShouldReturnNullWhenNoCapacity() {
		Functions\when( 'check_ajax_referer' )->justReturn( true );
		Functions\when( 'current_user_can' )->justReturn( false );

		$this->assertNull( $this->data_manager->set_process_status() );
	}

	public function testShouldReturnNullWhenStatusEmpty() {
		Functions\when( 'check_ajax_referer' )->justReturn( true );
		Functions\when( 'current_user_can' )->justReturn( true );

		$this->assertNull( $this->data_manager->set_process_status() );
	}

	public function testShouldDeleteOptionWhenStatusFalse() {
		$_POST['status'] = 'false';

		Functions\when( 'check_ajax_referer' )->justReturn( true );
		Functions\when( 'current_user_can' )->justReturn( true );

		Functions\expect( 'delete_option' )
			->once()
			->with( 'rocketcdn_process' );

		$this->data_manager->set_process_status();
	}

	public function testShouldUpdateOptionWhenStatusTrue() {
		$_POST['status'] = 'true';

		Functions\when( 'check_ajax_referer' )->justReturn( true );
		Functions\when( 'current_user_can' )->justReturn( true );

		Functions\expect( 'update_option' )
			->once()
			->with( 'rocketcdn_process', true );

		$this->data_manager->set_process_status();
	}
}
