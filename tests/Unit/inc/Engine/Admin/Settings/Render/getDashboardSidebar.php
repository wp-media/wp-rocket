<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\Settings\Render;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Dependencies\WPMedia\PluginFamily\Model\PluginFamily;
use WP_Rocket\Engine\Admin\Settings\Render;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Admin\Settings\Render::get_dashboard_sidebar
 *
 * @group SettingsPage
 */
class Test_GetDashboardSidebar extends TestCase {
	/**
	 * @var Render|Mockery\MockInterface
	 */
	private $render;

	protected function setUp(): void {
		parent::setUp();

		Functions\stubTranslationFunctions();

		$this->render = Mockery::mock( Render::class, [ 'vfs://views', Mockery::mock( PluginFamily::class ) ] )->makePartial();
	}

	public function testShouldReturnEmptyStringWhenNothingIsHooked() {
		Filters\expectApplied( 'rocket_dashboard_sidebar_license' )->once()->with( '' );
		Filters\expectApplied( 'rocket_dashboard_sidebar_promos' )->once()->with( '' );

		$this->render->shouldReceive( 'generate' )->never();

		$this->assertSame( '', $this->render->get_dashboard_sidebar() );
	}

	public function testShouldReturnEmptyStringWhenCategoriesAreBlank() {
		Filters\expectApplied( 'rocket_dashboard_sidebar_license' )->once()->andReturn( "  \n" );
		Filters\expectApplied( 'rocket_dashboard_sidebar_promos' )->once()->andReturn( '' );

		$this->render->shouldReceive( 'generate' )->never();

		$this->assertSame( '', $this->render->get_dashboard_sidebar() );
	}

	public function testShouldRenderOnlyTheCategoryWithContent() {
		Filters\expectApplied( 'rocket_dashboard_sidebar_license' )->once()->andReturn( '' );
		Filters\expectApplied( 'rocket_dashboard_sidebar_promos' )->once()->andReturn( '<p>promo</p>' );

		$this->render->shouldReceive( 'generate' )
			->once()
			->with( 'partials/sidebar/category', [ 'title' => 'Promos', 'content' => '<p>promo</p>' ] )
			->andReturn( '<div>Promos</div>' );

		$this->assertSame( '<div>Promos</div>', $this->render->get_dashboard_sidebar() );
	}

	public function testShouldRenderCategoriesInOrderLicenseThenPromos() {
		Filters\expectApplied( 'rocket_dashboard_sidebar_license' )->once()->andReturn( '<p>license</p>' );
		Filters\expectApplied( 'rocket_dashboard_sidebar_promos' )->once()->andReturn( '<p>promo</p>' );

		$this->render->shouldReceive( 'generate' )
			->once()
			->ordered()
			->with( 'partials/sidebar/category', [ 'title' => 'License', 'content' => '<p>license</p>' ] )
			->andReturn( '<div>License</div>' );
		$this->render->shouldReceive( 'generate' )
			->once()
			->ordered()
			->with( 'partials/sidebar/category', [ 'title' => 'Promos', 'content' => '<p>promo</p>' ] )
			->andReturn( '<div>Promos</div>' );

		$this->assertSame( '<div>License</div><div>Promos</div>', $this->render->get_dashboard_sidebar() );
	}

	public function testShouldCastNullTemplateToEmptyString() {
		Filters\expectApplied( 'rocket_dashboard_sidebar_license' )->once()->andReturn( '<p>license</p>' );
		Filters\expectApplied( 'rocket_dashboard_sidebar_promos' )->once()->andReturn( '' );

		$this->render->shouldReceive( 'generate' )->once()->andReturn( null );

		$this->assertSame( '', $this->render->get_dashboard_sidebar() );
	}
}
