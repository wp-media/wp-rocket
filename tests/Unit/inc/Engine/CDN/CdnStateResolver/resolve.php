<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\CdnStateResolver;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\CDN\CdnStateResolver;
use WP_Rocket\Engine\CDN\CdnStateTranslator;
use WP_Rocket\Tests\Unit\TestCase;

class Test_Resolve extends TestCase {
	/**
	 * @var Mockery\MockInterface|CdnStateTranslator
	 */
	private $translator;

	/**
	 * @var CdnStateResolver
	 */
	private $resolver;

	public function set_up() {
		parent::set_up();

		$this->translator = Mockery::mock( CdnStateTranslator::class );
		$this->resolver   = new CdnStateResolver( $this->translator );
	}

	public function testShouldTranslateLiveLegacyReadsRegardlessOfWhatIsStored() {
		// get_rocket_option() is what applies pre_get_rocket_option_cdn/_cdn_type - a forced
		// pause or any other filter forcing these values must already be baked into what
		// resolve() passes to the translator.
		Functions\expect( 'get_rocket_option' )
			->once()
			->with( 'cdn' )
			->andReturn( false );

		Functions\expect( 'get_rocket_option' )
			->once()
			->with( 'cdn_type' )
			->andReturn( 'rocketcdn' );

		$this->translator->shouldReceive( 'legacy_to_state' )
			->once()
			->with(
				[
					'cdn'      => false,
					'cdn_type' => 'rocketcdn',
				]
			)
			->andReturn( 'nothing' );

		$this->assertSame( 'nothing', $this->resolver->resolve( null, 'nothing' ) );
	}
}
