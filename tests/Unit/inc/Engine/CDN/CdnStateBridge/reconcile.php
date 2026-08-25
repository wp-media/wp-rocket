<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\CdnStateBridge;

use Mockery;
use ReflectionProperty;
use WP_Rocket\Admin\Options;
use WP_Rocket\Engine\CDN\CdnStateBridge;
use WP_Rocket\Engine\CDN\CdnStateTranslator;
use WP_Rocket\Tests\Unit\TestCase;

class Test_Reconcile extends TestCase {
	/**
	 * @var Mockery\MockInterface|CdnStateTranslator
	 */
	private $translator;

	/**
	 * @var Mockery\MockInterface|Options
	 */
	private $options_api;

	/**
	 * @var CdnStateBridge
	 */
	private $bridge;

	public function set_up() {
		parent::set_up();

		$this->translator  = Mockery::mock( CdnStateTranslator::class );
		$this->options_api = Mockery::mock( Options::class );
		$this->bridge       = new CdnStateBridge( $this->translator, $this->options_api );

		$this->reset_depth();
	}

	public function tear_down() {
		$this->reset_depth();

		parent::tear_down();
	}

	private function reset_depth(): void {
		$prop = new ReflectionProperty( CdnStateBridge::class, 'depth' );
		$prop->setAccessible( true );
		$prop->setValue( null, 0 );
	}

	public function testShouldDoNothingWhenValuesAreNotArrays() {
		$this->options_api->shouldNotReceive( 'set' );

		$this->bridge->reconcile( null, 'not-an-array' );
	}

	public function testShouldDoNothingWhenStateDidNotChange() {
		$settings = [
			'cdn'       => 1,
			'cdn_type'  => 'rocketcdn',
			'cdn_state' => 'rocketcdn_free',
		];

		$this->translator->shouldNotReceive( 'state_to_legacy' );
		$this->options_api->shouldNotReceive( 'set' );

		$this->bridge->reconcile( $settings, $settings );
	}

	public function testShouldIgnoreLegacyFieldChangesEntirely() {
		// legacy -> state is CdnStateResolver's job now, not the bridge's - reconcile() must
		// not react to a legacy-only change at all.
		$old = [
			'cdn'       => 0,
			'cdn_type'  => 'rocketcdn',
			'cdn_state' => 'nothing',
		];
		$new = [
			'cdn'       => 1,
			'cdn_type'  => 'byocdn',
			'cdn_state' => 'nothing',
		];

		$this->translator->shouldNotReceive( 'legacy_to_state' );
		$this->translator->shouldNotReceive( 'state_to_legacy' );
		$this->options_api->shouldNotReceive( 'set' );

		$this->bridge->reconcile( $old, $new );
	}

	public function testShouldNotPersistWhenStateChangedButLegacyAlreadyAgrees() {
		$old = [
			'cdn'       => 1,
			'cdn_type'  => 'rocketcdn',
			'cdn_state' => 'rocketcdn_free',
		];
		$new = [
			'cdn'       => 1,
			'cdn_type'  => 'rocketcdn',
			'cdn_state' => 'rocketcdn_paid',
		];

		$this->translator->shouldReceive( 'state_to_legacy' )
			->with( 'rocketcdn_paid' )
			->andReturn(
				[
					'cdn'      => 1,
					'cdn_type' => 'rocketcdn',
				]
			);

		$this->options_api->shouldNotReceive( 'set' );

		$this->bridge->reconcile( $old, $new );
	}

	public function testShouldPersistCorrectedLegacyWhenStateChanged() {
		$old = [
			'cdn'       => 0,
			'cdn_type'  => 'rocketcdn',
			'cdn_state' => 'nothing',
		];
		$new = [
			'cdn'       => 0,
			'cdn_type'  => 'rocketcdn',
			'cdn_state' => 'rocketcdn_paid',
		];

		$this->translator->shouldReceive( 'state_to_legacy' )
			->with( 'rocketcdn_paid' )
			->andReturn(
				[
					'cdn'      => 1,
					'cdn_type' => 'rocketcdn',
				]
			);

		$expected = $new;
		$expected['cdn']      = 1;
		$expected['cdn_type'] = 'rocketcdn';

		$this->options_api->shouldReceive( 'set' )
			->once()
			->with( 'settings', $expected );

		$this->bridge->reconcile( $old, $new );
	}

	public function testShouldBailOutWhenAlreadyAtMaxDepth() {
		$prop = new ReflectionProperty( CdnStateBridge::class, 'depth' );
		$prop->setAccessible( true );
		$prop->setValue( null, CdnStateBridge::MAX_DEPTH );

		$old = [
			'cdn'       => 0,
			'cdn_type'  => 'rocketcdn',
			'cdn_state' => 'nothing',
		];
		$new = [
			'cdn'       => 0,
			'cdn_type'  => 'rocketcdn',
			'cdn_state' => 'rocketcdn_paid',
		];

		$this->translator->shouldNotReceive( 'state_to_legacy' );
		$this->options_api->shouldNotReceive( 'set' );

		$this->bridge->reconcile( $old, $new );
	}
}
