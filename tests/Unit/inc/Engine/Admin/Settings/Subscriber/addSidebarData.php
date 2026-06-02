<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\Settings\Subscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Dependencies\WPMedia\PluginFamily\Controller\PluginFamily;
use WP_Rocket\Engine\Admin\Settings\{Page, Subscriber};
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\Settings\Subscriber::add_sidebar_data
 *
 * @group Admin
 * @group SettingsSubscriber
 */
class TestAddSidebarData extends TestCase {
	/**
	 * Page mock.
	 *
	 * @var Page
	 */
	private $page;

	/**
	 * Subscriber instance under test.
	 *
	 * @var Subscriber
	 */
	private $subscriber;

	/**
	 * Sets up the test instance.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		$this->page = Mockery::mock( Page::class );

		$this->subscriber = new Subscriber(
			$this->page,
			Mockery::mock( PluginFamily::class )
		);
	}

	/**
	 * Tests that add_sidebar_data merges show_sidebar and is_fresh_sidebar_install into the input array.
	 *
	 * @dataProvider configTestData
	 *
	 * @param int   $sidebar_value      Value returned by Page::get_sidebar_show_option().
	 * @param bool  $has_fresh_install  Whether the fresh install transient is set.
	 * @param array $input_data         Input data array passed to the filter.
	 * @param array $expected           Expected merged data array.
	 */
	public function testShouldMergeSidebarDataIntoData( $sidebar_value, $has_fresh_install, $input_data, $expected ): void {
		$this->page
			->shouldReceive( 'get_sidebar_show_option' )
			->once()
			->andReturn( $sidebar_value );

		Functions\expect( 'get_transient' )
			->once()
			->with( 'wpr_sidebar_reset_needed' )
			->andReturn( $has_fresh_install ? 1 : false );

		$result = $this->subscriber->add_sidebar_data( $input_data );

		$this->assertSame( $expected, $result );
	}
}
