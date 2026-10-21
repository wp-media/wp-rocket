<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\RUCSS\Database\Queries\UsedCSS;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\UsedCSS::add_item
 *
 * @group RUCSS
 */
class Test_AddItem extends TestCase {
	private $query;
	private $url = 'http://example.org/sample-page';

	public function set_up() {
		parent::set_up();

		self::installUsedCssTable();

		$this->query = apply_filters( 'rocket_container', null )->get( 'rucss_used_css_query' );
	}

	public function tear_down() {
		self::uninstallUsedCssTable();

		parent::tear_down();
	}

	public function testShouldStoreTheExactMd5OfTheUrl() {
		global $wpdb;

		$id = $this->query->add_item( $this->item( $this->url ) );

		$this->assertNotFalse( $id );
		$this->assertSame(
			md5( $this->url ),
			$wpdb->get_var( $wpdb->prepare( "SELECT url_hash FROM {$wpdb->prefix}wpr_rucss_used_css WHERE id = %d", $id ) )
		);
	}

	public function testShouldRejectTheSameUrlAndDevice() {
		global $wpdb;

		$this->assertNotFalse( $this->query->add_item( $this->item( $this->url ) ) );

		$previous = $wpdb->suppress_errors( true );
		$result   = $this->query->add_item( $this->item( $this->url ) );
		$wpdb->suppress_errors( $previous );

		$this->assertEmpty( $result );
		$this->assertSame( 1, (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}wpr_rucss_used_css" ) );
	}

	public function testShouldAcceptTheOtherDevice() {
		$this->assertNotFalse( $this->query->add_item( $this->item( $this->url ) ) );
		$this->assertNotFalse( $this->query->add_item( $this->item( $this->url, true ) ) );
	}

	public function testShouldAcceptACaseVariantOfTheUrl() {
		$this->assertNotFalse( $this->query->add_item( $this->item( $this->url ) ) );
		$this->assertNotFalse( $this->query->add_item( $this->item( 'http://example.org/Sample-Page' ) ) );
	}

	public function testShouldNotCacheTheRowByJobId() {
		$this->query->add_item( $this->item( $this->url ) );
		$this->query->make_status_pending( $this->url, 'job-42', 'EU', false );

		$this->assertFalse( wp_cache_get( 'job-42', 'wpr_used_css-by-job_id' ) );
	}

	private function item( string $url, bool $is_mobile = false ): array {
		return [
			'url'       => $url,
			'is_mobile' => $is_mobile,
			'status'    => 'to-submit',
		];
	}
}
