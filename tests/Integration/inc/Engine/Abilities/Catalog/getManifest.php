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
	 * WP Rocket's read-only abilities. With WRITE_ABILITIES, every ability WP Rocket registers: a missing one,
	 * or a new one nobody sorted into reads or writes, fails the test.
	 */
	private const READ_ABILITIES = [
		'wp-rocket/get-cache-health',
		'wp-rocket/get-cache-status',
		'wp-rocket/get-insights-scores',
		'wp-rocket/get-options',
		'wp-rocket/get-page-insights-score',
		'wp-rocket/get-recommendations',
	];

	/**
	 * WP Rocket's other abilities, which change something.
	 */
	private const WRITE_ABILITIES = [
		'wp-rocket/add-page-insights',
		'wp-rocket/clear-url-cache',
		'wp-rocket/clear-website-cache',
		'wp-rocket/remove-page-insights',
		'wp-rocket/retest-page-insights',
		'wp-rocket/set-option',
	];

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

		$this->assertEqualsCanonicalizing(
			array_merge( self::READ_ABILITIES, self::WRITE_ABILITIES ),
			array_column( $manifest, 'name' ),
			'The registered abilities should be exactly the listed ones: add a new ability to READ_ABILITIES or WRITE_ABILITIES.'
		);

		foreach ( $manifest as $ability ) {
			$this->assertSame(
				in_array( $ability['name'], self::READ_ABILITIES, true ),
				true === ( $ability['meta']['annotations']['readonly'] ?? null ),
				"{$ability['name']} is listed on the wrong side of READ_ABILITIES / WRITE_ABILITIES."
			);
		}

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
