<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\Abilities\Catalog;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Proof-of-concept: dumps every registered `wp-rocket/*` ability as JSON to STDERR
 * so a CI step can capture it from the Action log.
 *
 * This does not assert anything about individual abilities — it is a feasibility
 * showcase for a future CI-driven abilities manifest extraction, not a regression test.
 *
 * @group Abilities
 * @group AbilitiesCatalog
 */
class DumpAbilitiesCatalogTest extends TestCase {
	/**
	 * Marker lines the CI step greps for to isolate the JSON payload from other test output.
	 */
	private const START_MARKER = '===ABILITIES_CATALOG_JSON_START===';
	private const END_MARKER   = '===ABILITIES_CATALOG_JSON_END===';

	/**
	 * Vendor namespace prefix abilities belonging to this plugin are registered under.
	 */
	private const VENDOR_PREFIX = 'wp-rocket/';

	public function set_up() {
		parent::set_up();

		if ( version_compare( $GLOBALS['wp_version'], '6.9', '<' ) ) {
			$this->markTestSkipped( 'WordPress 6.9+ required for the Abilities API.' );
		}

		if ( ! function_exists( 'wp_get_abilities' ) ) {
			$this->markTestSkipped( 'wp_get_abilities() is not available in this environment.' );
		}
	}

	/**
	 * Reads every registered ability via the public Abilities API and prints
	 * the ones belonging to WP Rocket as a JSON manifest.
	 *
	 * @return void
	 */
	public function testShouldDumpRegisteredAbilitiesAsJson(): void {
		$abilities = wp_get_abilities();

		$this->assertNotEmpty( $abilities, 'Expected at least one ability to be registered by wp_get_abilities().' );

		$manifest = [];

		foreach ( $abilities as $ability ) {
			if ( 0 !== strpos( $ability->get_name(), self::VENDOR_PREFIX ) ) {
				continue;
			}

			$manifest[] = [
				'name'          => $ability->get_name(),
				'label'         => $ability->get_label(),
				'description'   => $ability->get_description(),
				'category'      => $ability->get_category(),
				'input_schema'  => $ability->get_input_schema(),
				'output_schema' => $ability->get_output_schema(),
				'meta'          => $ability->get_meta(),
			];
		}

		$this->assertNotEmpty( $manifest, 'Expected at least one wp-rocket/* ability to be registered.' );

		$json = wp_json_encode( $manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );

		fwrite( STDERR, "\n" . self::START_MARKER . "\n" . $json . "\n" . self::END_MARKER . "\n" );

		// Also write to a file the CI workflow uploads as a downloadable artifact —
		// avoids scraping the JSON out of the raw Action log by hand.
		$outputPath = getenv( 'ABILITIES_CATALOG_OUTPUT_PATH' ) ?: sys_get_temp_dir() . '/wp-rocket-abilities-catalog.json';
		file_put_contents( $outputPath, $json );
	}
}
