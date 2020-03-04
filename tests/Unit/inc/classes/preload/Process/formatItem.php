<?php
namespace WP_Rocket\Tests\Unit\inc\classes\preload\Process;

use WPMedia\PHPUnit\Unit\TestCase;
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
				'url' => 'https://example.org',
			]
		);

		$this->assertTrue( is_array( $item ) );
		$this->assertArrayHasKey( 'url', $item );
		$this->assertArrayHasKey( 'mobile', $item );
		$this->assertSame( 'https://example.org', $item['url'] );
		$this->assertFalse( $item['mobile'] );

		$item = $stub->format_item(
			[
				'url'    => 'https://example.org',
				'mobile' => 0,
			]
		);

		$this->assertTrue( is_array( $item ) );
		$this->assertArrayHasKey( 'url', $item );
		$this->assertArrayHasKey( 'mobile', $item );
		$this->assertSame( 'https://example.org', $item['url'] );
		$this->assertFalse( $item['mobile'] );

		$item = $stub->format_item(
			[
				'url'    => 'https://example.org',
				'mobile' => 1,
			]
		);

		$this->assertTrue( is_array( $item ) );
		$this->assertArrayHasKey( 'url', $item );
		$this->assertArrayHasKey( 'mobile', $item );
		$this->assertSame( 'https://example.org', $item['url'] );
		$this->assertTrue( $item['mobile'] );
	}

	public function testShouldReturnArrayWhenStringIsProvided() {
		$stub = $this->getMockForAbstractClass( Process::class );
		$item = $stub->format_item( 'https://example.org' );

		$this->assertTrue( is_array( $item ) );
		$this->assertArrayHasKey( 'url', $item );
		$this->assertArrayHasKey( 'mobile', $item );
		$this->assertSame( 'https://example.org', $item['url'] );
		$this->assertFalse( $item['mobile'] );
	}

	public function testShouldReturnEmptyArrayWhenInvalidArgIsProvided() {
		$stub = $this->getMockForAbstractClass( Process::class );
		$item = $stub->format_item( [] );

		$this->assertSame( [], $item );

		$item = $stub->format_item(
			[
				'src' => 'https://example.org',
			]
		);

		$this->assertSame( [], $item );

		$item = $stub->format_item( 666 );

		$this->assertSame( [], $item );
	}
}
