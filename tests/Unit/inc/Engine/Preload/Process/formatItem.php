<?php
namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Process;

use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Engine\Preload\PartialProcess;

/**
 * @covers \WP_Rocket\Engine\Preload\AbstractProcess::format_item
 * @group Preload
 */
class Test_FormatItem extends TestCase {
	private $process;
	public function setUp() : void {
		parent::setUp();

		$this->process = new PartialProcess();
	}

	public function testShouldReturnArrayWhenValidArrayIsProvided() {
		$item = $this->process->format_item(
			[
				'url' => 'https://example.org',
			]
		);

		$this->assertTrue( is_array( $item ) );
		$this->assertArrayHasKey( 'url', $item );
		$this->assertArrayHasKey( 'mobile', $item );
		$this->assertSame( 'https://example.org', $item['url'] );
		$this->assertFalse( $item['mobile'] );

		$item = $this->process->format_item(
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

		$item = $this->process->format_item(
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
		$item = $this->process->format_item( 'https://example.org' );

		$this->assertTrue( is_array( $item ) );
		$this->assertArrayHasKey( 'url', $item );
		$this->assertArrayHasKey( 'mobile', $item );
		$this->assertSame( 'https://example.org', $item['url'] );
		$this->assertFalse( $item['mobile'] );
	}

	public function testShouldReturnEmptyArrayWhenInvalidArgIsProvided() {
		$item = $this->process->format_item( [] );

		$this->assertSame( [], $item );

		$item = $this->process->format_item(
			[
				'src' => 'https://example.org',
			]
		);

		$this->assertSame( [], $item );

		$item = $this->process->format_item( 666 );

		$this->assertSame( [], $item );
	}
}
