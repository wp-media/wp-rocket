<?php
namespace WP_Rocket\Tests\Unit\Preload\Process;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Preload\Process;

/**
 * @covers \WP_Rocket\Preload\Process::format_item
 * @group Preload
 */
class Test_FormatItem extends TestCase {

	public function testShouldReturnArrayWhenValidArrayIsProvided() {
		$stub = $this->getMockForAbstractClass( Process::class );
		$item = $stub->format_item(
			[
				'url' => 'https://example.com',
			]
		);

		$this->assertTrue( is_array( $item ) );
		$this->assertArrayHasKey( 'url', $item );
		$this->assertArrayHasKey( 'mobile', $item );
		$this->assertSame( 'https://example.com', $item['url'] );
		$this->assertFalse( $item['mobile'] );

		$item = $stub->format_item(
			[
				'url'    => 'https://example.com',
				'mobile' => 0,
			]
		);

		$this->assertTrue( is_array( $item ) );
		$this->assertArrayHasKey( 'url', $item );
		$this->assertArrayHasKey( 'mobile', $item );
		$this->assertSame( 'https://example.com', $item['url'] );
		$this->assertFalse( $item['mobile'] );

		$item = $stub->format_item(
			[
				'url'    => 'https://example.com',
				'mobile' => 1,
			]
		);

		$this->assertTrue( is_array( $item ) );
		$this->assertArrayHasKey( 'url', $item );
		$this->assertArrayHasKey( 'mobile', $item );
		$this->assertSame( 'https://example.com', $item['url'] );
		$this->assertTrue( $item['mobile'] );
	}

	public function testShouldReturnArrayWhenStringIsProvided() {
		$stub = $this->getMockForAbstractClass( Process::class );
		$item = $stub->format_item( 'https://example.com' );

		$this->assertTrue( is_array( $item ) );
		$this->assertArrayHasKey( 'url', $item );
		$this->assertArrayHasKey( 'mobile', $item );
		$this->assertSame( 'https://example.com', $item['url'] );
		$this->assertFalse( $item['mobile'] );
	}

	public function testShouldReturnFalseWhenInvalidArgIsProvided() {
		$stub = $this->getMockForAbstractClass( Process::class );
		$item = $stub->format_item( [] );

		$this->assertFalse( $item );

		$item = $stub->format_item(
			[
				'src' => 'https://example.com',
			]
		);

		$this->assertFalse( $item );

		$item = $stub->format_item( 666 );

		$this->assertFalse( $item );
	}
}
