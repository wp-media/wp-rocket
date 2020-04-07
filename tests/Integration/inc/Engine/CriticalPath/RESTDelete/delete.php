<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\RESTDelete;

use WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\RestTestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\RESTDelete::delete
 * @group  CriticalPath
 */
class Test_Delete extends RestTestCase {
	protected $path_to_test_data = '/inc/Engine/CriticalPath/RESTDelete/delete.php';

	public function testShouldBailoutWithNoCapabilities() {
		$expected = [
			'code'    => 'rest_forbidden',
			'message' => __( 'Sorry, you are not allowed to do that.' ),
			'data'    => [
				'status' => 401,
			],
		];

		$this->assertSame( $expected, $this->requestDeleteCriticalPath( 1 ) );
	}

	public function testShouldBailoutIfPostDoesNotExist() {
		$this->addCriticalPathUserCapabilities();
		$expected = [
			'code'    => 'post_not_exists',
			'message' => __( 'Requested post does not exist', 'rocket' ),
			'data'    => [
				'status' => 400,
			],
		];

		$this->assertSame( $expected, $this->requestDeleteCriticalPath( 2 ) );
	}

	public function testShouldBailoutIfPostCPCSSNotExist() {
		$this->addCriticalPathUserCapabilities();
		$post_id  = $this->factory->post->create( array( 'import_id' => 3 ) );
		$file     = rocket_get_constant( 'WP_ROCKET_CRITICAL_CSS_PATH' ) . get_current_blog_id() . '/posts/post-type-' . $post_id . '.css';
		$expected = [
			'code'    => 'cpcss_not_exists',
			'message' => __( 'Critical CSS file does not exist', 'rocket' ),
			'data'    => [
				'status' => 400,
			],
		];

		$this->assertFalse( $this->filesystem->exists( $file ) );
		$this->assertSame( $expected, $this->requestDeleteCriticalPath( $post_id ) );
		$this->assertFalse( $this->filesystem->exists( $file ) );
	}

	public function testShouldReturnSuccessWhenEndpointRequest() {
		$this->addCriticalPathUserCapabilities();
		$post_id  = $this->factory->post->create( array( 'import_id' => 1 ) );
		$file     = rocket_get_constant( 'WP_ROCKET_CRITICAL_CSS_PATH' ) . get_current_blog_id() . '/posts/post-type-' . $post_id . '.css';
		$expected = [
			'code'    => 'success',
			'message' => __( 'Critical CSS file deleted successfully.', 'rocket' ),
			'data'    => [
				'status' => 200,
			],
		];

		$this->assertTrue( $this->filesystem->exists( $file ) );
		$this->assertSame( $expected, $this->requestDeleteCriticalPath( $post_id ) );
		$this->assertFalse( $this->filesystem->exists( $file ) );
	}
}
