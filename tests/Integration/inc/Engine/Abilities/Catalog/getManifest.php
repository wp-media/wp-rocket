<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Abilities\Catalog;

use WP_Rocket\Engine\Abilities\Catalog;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Abilities\Catalog::get_manifest
 *
 * @group Abilities
 * @group AdminOnly
 */
class Test_GetManifest extends TestCase {
	/**
	 * Minimum WordPress version required.
	 */
	private const MIN_WP_VERSION = '6.9';

	/**
	 * Abilities WAP treats as reads, under the support chat's "read" permission. Every other one is a write.
	 */
	private const READ_PREFIX = 'wp-rocket/get-';

	/**
	 * Set up the test.
	 *
	 * @return void
	 */
	public function set_up() {
		global $wp_version;

		parent::set_up();

		if ( version_compare( $wp_version, self::MIN_WP_VERSION, '<' ) ) {
			$this->markTestSkipped( 'WordPress Abilities API requires WordPress ' . self::MIN_WP_VERSION . ' or higher.' );
		}
	}

	/**
	 * WAP allows a `get-` ability under the read permission without looking further, so a write named
	 * `get-…` would run where the customer only allowed reads, and a read named otherwise would be refused.
	 */
	public function testShouldNameExactlyTheReadOnlyAbilitiesGet(): void {
		$manifest = ( new Catalog() )->get_manifest();

		$this->assertNotEmpty( $manifest, 'WP Rocket abilities should be registered.' );

		foreach ( $manifest as $ability ) {
			$is_readonly = true === ( $ability['meta']['annotations']['readonly'] ?? null );
			$is_get_name = 0 === strpos( $ability['name'], self::READ_PREFIX );

			$this->assertSame(
				$is_readonly,
				$is_get_name,
				$is_readonly
					? "{$ability['name']} is read-only, so its name should start with " . self::READ_PREFIX
					: "{$ability['name']} is not read-only, so its name should not start with " . self::READ_PREFIX
			);
		}
	}
}
