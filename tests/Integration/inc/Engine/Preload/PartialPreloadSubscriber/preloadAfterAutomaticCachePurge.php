<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Preload\PartialPreloadSubscriber;

use WPMedia\PHPUnit\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Preload\PartialPreloadSubscriber::preload_after_automatic_cache_purge
 * @group  Preload
 */
class Test_PreloadAfterAutomaticCachePurge extends TestCase {
	private $urls;
	private $subscriber;
	private $property;
	private $permalink_structure;
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
		remove_filter( 'pre_option_permalink_structure', [ $this, 'permalink_structure_filter' ] );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDoExpectedWithSlashedUrl( $permalink_structure, $option_value, $deleted, $expected ) {
		$this->permalink_structure = $permalink_structure;
		$this->manual_preload      = $option_value;

		add_filter( 'pre_get_rocket_option_manual_preload', [ $this, 'manual_preload_filter' ] );
		add_filter( 'pre_option_permalink_structure', [ $this, 'permalink_structure_filter' ] );

		do_action( 'rocket_after_automatic_cache_purge', $deleted );

		$this->property = $this->get_reflective_property( 'urls', $this->subscriber );
		$this->urls     = $this->property->getValue( $this->subscriber );

		if ( ! $expected ) {
			$this->assertEmpty( $this->urls );
		}

		foreach ( $expected as $url ) {
			$this->assertContains( $url, $this->urls );
		}
	}

	public function manual_preload_filter() {
		return $this->manual_preload;
	}

	public function permalink_structure_filter() {
		return $this->permalink_structure;
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'preloadAfterAutomaticCachePurge' );
	}
}
