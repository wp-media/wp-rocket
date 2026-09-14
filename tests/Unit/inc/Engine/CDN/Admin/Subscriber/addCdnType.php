<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\Admin\Subscriber;

use WP_Rocket\Engine\CDN\Admin\Subscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\Admin\Subscriber::add_cdn_type
 *
 * Regression coverage for the Task 9.1 companion fix (issue #8707): `cdn`
 * must be registered as a hidden settings field alongside `cdn_type`/`cdn_state`
 * so a classic "Save Changes" submit on any tab doesn't silently reset it to 0.
 *
 * @group CDN
 */
class Test_AddCdnType extends TestCase {

	private $subscriber;

	public function setUp(): void {
		parent::setUp();

		$this->subscriber = new Subscriber();
	}

	public function testShouldAddCdnToHiddenFields(): void {
		$fields = $this->subscriber->add_cdn_type( [] );

		$this->assertContains( 'cdn_type', $fields );
		$this->assertContains( 'cdn_state', $fields );
		$this->assertContains( 'cdn', $fields );
	}

	public function testShouldPreserveExistingFields(): void {
		$fields = $this->subscriber->add_cdn_type( [ 'some_other_field' ] );

		$this->assertSame( [ 'some_other_field', 'cdn_type', 'cdn_state', 'cdn' ], $fields );
	}
}
