<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\RESTWPPost;

use Brain\Monkey\Functions;
use WP_REST_Request;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CriticalPath\APIClient;
use WP_Rocket\Engine\CriticalPath\ProcessorService;
use WP_Rocket\Engine\CriticalPath\DataManager;
use WP_Rocket\Engine\CriticalPath\RESTWPPost;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Error;
use Mockery;

/**
 * Test class covering \WP_Rocket\Engine\CriticalPath\RESTWPPost::delete
 * @uses   \WP_Rocket\Engine\CriticalPath\ProcessorService::process_delete
 * @uses   \WP_Rocket\Admin\Options_Data::get
 *
 * @group  CriticalPath
 */
class Test_Delete extends TestCase {
	protected $post_id;
	protected $post_status;
	protected $post_type;
	protected $is_mobile;
	protected $cpcss_service;
	protected $options;
	protected $request;
	protected $restwppost;

	public static function setUpBeforeClass() : void {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/WP_REST_Request.php';
		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/WP_Error.php';
	}

	public function setUp() : void {
		parent::setUp();

		Functions\stubTranslationFunctions();
		Functions\when( 'get_current_blog_id' )->justReturn( 1 );

		$this->is_mobile = false;
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$this->setUpTest( $config );

		switch ( $expected['code'] ) {
			case 'rest_forbidden' :
				// Skip as it's not valid for the unit test.
				$this->assertTrue( true );

				return;

			case 'post_not_exists':
				$this->assertPostNotExists( $expected );
				break;

			case 'cpcss_not_exists':
				$this->assertCpcssNotExists( $config, $expected );
				break;

			case 'success':
				$this->assertSuccess( $config, $expected );
		}

		$this->assertSame( $expected, $this->restwppost->delete( $this->request ) );
	}

	private function setUpTest( $config ) {
		$this->post_id   = ! isset( $config['post_data']['post_id'] )
			? $config['post_data']['import_id']
			: $config['post_data']['post_id'];
		$this->post_type = ! isset( $config['post_data']['post_type'] )
			? 'post'
			: $config['post_data']['post_type'];

		if ( isset( $config['is_mobile'] ) ) {
			$this->is_mobile = $config['is_mobile'];
		}

		$this->cpcss_service = Mockery::mock( ProcessorService::class, [
			Mockery::mock( DataManager::class, [ '', null ] ),
			Mockery::mock( APIClient::class ),
		] );

		$this->options       = Mockery::mock( Options_Data::class );
		$this->restwppost    = new RESTWPPost( $this->cpcss_service, $this->options );
		$this->request       = new WP_REST_Request();
		$this->request['id'] = $this->post_id;
	}

	private function assertPostNotExists( $expected ) {
		Functions\expect( 'get_permalink' )
			->once()
			->with( $this->post_id )
			->andReturn( false );

		Functions\expect( 'is_wp_error' )
			->once()
			->andReturn( true );

		Functions\expect( 'rest_ensure_response' )
			->once()
			->with( $expected )
			->andReturnFirstArg();

		$this->options
			->shouldReceive( 'get' )
			->with( 'async_css_mobile', 0 )
			->never();
	}

	private function assertCpcssNotExists( $config, $expected ) {
		Functions\expect( 'get_permalink' )
			->once()
			->with( $this->post_id )
			->andReturn( $this->getPermalink() );

		$this->options
			->shouldReceive( 'get' )
			->with( 'async_css_mobile', 0 )
			->andReturn( $config['options']['async_css_mobile'] );

		$error = $this->getError( $expected );
		$this->cpcss_service
			->shouldReceive( 'process_delete' )
			->once()
			->with( $this->getPath() )
			->andReturn( $error );

		Functions\expect( 'is_wp_error' )
			->once()
			->with( true )
			->andReturn( false )
			->andAlsoExpectIt()
			->once()
			->with( $error )
			->andReturn( true );

		Functions\expect( 'rest_ensure_response' )
			->once()
			->with( $expected )
			->andReturnFirstArg();
	}

	private function assertSuccess( $config, $expected ) {
		Functions\expect( 'get_permalink' )
			->atLeast( 1 )
			->atMost( 2 )
			->with( $this->post_id )
			->andReturn( $this->getPermalink() );

		$this->options
			->shouldReceive( 'get' )
			->with( 'async_css_mobile', 0 )
			->andReturn( $config['options']['async_css_mobile'] );

		$deleted = [
			'code'    => 'success',
			'message' => 'Critical CSS file deleted successfully.',
		];

		if ( $this->is_mobile ) {
			$this->cpcss_service
				->shouldReceive( 'process_delete' )
				->once()
				->with( $this->getPath( true ) )
				->andReturn( $deleted );
		}

		$this->cpcss_service
			->shouldReceive( 'process_delete' )
			->once()
			->with( $this->getPath() )
			->andReturn( $deleted );

		Functions\expect( 'is_wp_error' )
			->once()
			->with( true )
			->andReturn( false )
			->andAlsoExpectIt()
			->once()
			->with( $deleted )
			->andReturn( false );

		Functions\expect( 'rocket_clean_files' )
			->once()
			->with( $this->getPermalink() );

		Functions\expect( 'rest_ensure_response' )
			->once()
			->with( $expected )
			->andReturnFirstArg();
	}

	private function getError( $expected ) {
		return new WP_Error(
			$expected['code'],
			$expected['message'],
			$expected['data']
		);
	}

	private function getPermalink() {
		return "http://example.org/?p={$this->post_id}";
	}

	private function getPath( $is_mobile = false ) {
		Functions\expect( 'get_post_type' )
			->atLeast( 1 )
			->atMost( 2 )
			->with( $this->post_id )
			->andReturn( $this->post_type );

		if ( $is_mobile ) {
			return "posts/{$this->post_type}-{$this->post_id}-mobile.css";
		}

		return "posts/{$this->post_type}-{$this->post_id}.css";
	}

}
