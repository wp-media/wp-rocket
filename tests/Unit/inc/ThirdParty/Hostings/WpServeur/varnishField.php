<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\WpServeur;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\WpServeur;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\WpServeur::varnish_field
 *
 * @group WpServeur
 * @group ThirdParty
 * @group Hostings
 */
class Test_VarnishField extends TestCase {
	public function testShouldSetWpServeurTitle() {
		Functions\when( '__' )->returnArg( 1 );

		$subscriber = new WpServeur();

		$settings = $subscriber->varnish_field( [ 'varnish_auto_purge' => [ 'title' => '' ] ] );

		$this->assertSame(
			sprintf( 'Your site is hosted on %s, we have enabled Varnish auto-purge for compatibility.', 'WP Serveur' ),
			$settings['varnish_auto_purge']['title']
		);
	}
}
