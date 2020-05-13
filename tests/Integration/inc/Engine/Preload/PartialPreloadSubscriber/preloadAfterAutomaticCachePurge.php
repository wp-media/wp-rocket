<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Preload\PartialPreloadSubscriber;

use WPMedia\PHPUnit\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Preload\PartialPreloadSubscriber::preload_after_automatic_cache_purge
 * @group  Preload
 * @group thisone
 */
class Test_PreloadAfterAutomaticCachePurge extends TestCase {
	private $subscriber;
	private $property;
	private $manual_preload;

	public function setUp() {
		parent::setUp();

		$container        = apply_filters( 'rocket_container', null );
		$this->subscriber = $container->get( 'partial_preload_subscriber' );
		$this->property   = $this->get_reflective_property( 'urls', $this->subscriber );
		$this->property->setAccessible( true );
		$this->property->setValue( $this->subscriber, [] );
	}

	public function tearDown() {
		parent::tearDown();

		$this->property->setValue( $this->subscriber, [] );
		$this->property->setAccessible( false );

		remove_filter( 'pre_get_rocket_option_manual_preload', [ $this, 'manual_preload_filter' ] );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDoExpectedWithSlashedUrl( $option_value, $deleted, $expected ) {
		$this->manual_preload      = $option_value;

		add_filter( 'pre_get_rocket_option_manual_preload', [ $this, 'manual_preload_filter' ] );

		// Run it.
		do_action( 'rocket_after_automatic_cache_purge', $deleted );

		$this->property = $this->get_reflective_property( 'urls', $this->subscriber );
		$this->assertSame( $expected, $this->property->getValue( $this->subscriber ) );
	}

	public function manual_preload_filter() {
		return $this->manual_preload;
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'preloadAfterAutomaticCachePurge' );
	}
}
