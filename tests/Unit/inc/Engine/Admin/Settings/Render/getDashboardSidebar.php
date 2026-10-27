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
		Functions\when( 'sanitize_key' )->returnArg();
		Functions\when( 'get_current_user_id' )->justReturn( 1 );
		Functions\when( 'get_user_meta' )->justReturn( '' );

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

	public function testShouldRenderEveryAddonCardFromDataUnderTheAddonsCategory() {
		$imagify = [ 'id' => 'imagify', 'title' => 'Imagify' ];
		$cdn     = [ 'id' => 'rocketcdn', 'title' => 'CDN' ];

		Filters\expectApplied( 'rocket_dashboard_sidebar_license' )->once()->andReturn( '' );
		Filters\expectApplied( 'rocket_dashboard_sidebar_promos' )->once()->andReturn( '' );
		Filters\expectApplied( 'rocket_dashboard_sidebar_addons' )->once()->with( [] )->andReturn( [ $imagify, $cdn ] );

		$this->render->shouldReceive( 'generate' )->once()->ordered()->with( 'partials/sidebar/addon-card', $imagify )->andReturn( '<i>1</i>' );
		$this->render->shouldReceive( 'generate' )->once()->ordered()->with( 'partials/sidebar/addon-card', $cdn )->andReturn( '<i>2</i>' );
		$this->render->shouldReceive( 'generate' )
			->once()
			->ordered()
			->with( 'partials/sidebar/category', [ 'title' => 'Add-ons', 'content' => '<i>1</i><i>2</i>' ] )
			->andReturn( '<div>Add-ons</div>' );

		$this->assertSame( '<div>Add-ons</div>', $this->render->get_dashboard_sidebar() );
	}

	public function testShouldNotRenderTheAddonsCategoryWhenThereIsNoCard() {
		Filters\expectApplied( 'rocket_dashboard_sidebar_license' )->once()->andReturn( '' );
		Filters\expectApplied( 'rocket_dashboard_sidebar_promos' )->once()->andReturn( '' );
		Filters\expectApplied( 'rocket_dashboard_sidebar_addons' )->once()->andReturn( [] );

		$this->render->shouldReceive( 'generate' )->never();

		$this->assertSame( '', $this->render->get_dashboard_sidebar() );
	}

	public function testShouldSkipTheDismissedAddonCard() {
		$imagify = [ 'id' => 'imagify', 'title' => 'Imagify' ];
		$cdn     = [ 'id' => 'rocketcdn', 'title' => 'CDN' ];

		Functions\when( 'get_user_meta' )->justReturn( [ 'rocket_addon_card_imagify' ] );

		Filters\expectApplied( 'rocket_dashboard_sidebar_license' )->once()->andReturn( '' );
		Filters\expectApplied( 'rocket_dashboard_sidebar_promos' )->once()->andReturn( '' );
		Filters\expectApplied( 'rocket_dashboard_sidebar_addons' )->once()->andReturn( [ $imagify, $cdn ] );

		$this->render->shouldReceive( 'generate' )->once()->with( 'partials/sidebar/addon-card', $cdn )->andReturn( '<i>2</i>' );
		$this->render->shouldReceive( 'generate' )
			->once()
			->with( 'partials/sidebar/category', [ 'title' => 'Add-ons', 'content' => '<i>2</i>' ] )
			->andReturn( '<div>Add-ons</div>' );

		$this->assertSame( '<div>Add-ons</div>', $this->render->get_dashboard_sidebar() );
	}

	public function testShouldNotRenderTheAddonsCategoryWhenEveryCardIsDismissed() {
		$imagify = [ 'id' => 'imagify', 'title' => 'Imagify' ];

		Functions\when( 'get_user_meta' )->justReturn( [ 'rocket_addon_card_imagify' ] );

		Filters\expectApplied( 'rocket_dashboard_sidebar_license' )->once()->andReturn( '' );
		Filters\expectApplied( 'rocket_dashboard_sidebar_promos' )->once()->andReturn( '' );
		Filters\expectApplied( 'rocket_dashboard_sidebar_addons' )->once()->andReturn( [ $imagify ] );

		$this->render->shouldReceive( 'generate' )->never();

		$this->assertSame( '', $this->render->get_dashboard_sidebar() );
	}

	public function testShouldNeverDismissACardWithoutId() {
		$card = [ 'title' => 'No id' ];

		Functions\expect( 'get_user_meta' )->never();

		Filters\expectApplied( 'rocket_dashboard_sidebar_license' )->once()->andReturn( '' );
		Filters\expectApplied( 'rocket_dashboard_sidebar_promos' )->once()->andReturn( '' );
		Filters\expectApplied( 'rocket_dashboard_sidebar_addons' )->once()->andReturn( [ $card ] );

		$this->render->shouldReceive( 'generate' )->once()->with( 'partials/sidebar/addon-card', $card )->andReturn( '<i>1</i>' );
		$this->render->shouldReceive( 'generate' )
			->once()
			->with( 'partials/sidebar/category', [ 'title' => 'Add-ons', 'content' => '<i>1</i>' ] )
			->andReturn( '<div>Add-ons</div>' );

		$this->assertSame( '<div>Add-ons</div>', $this->render->get_dashboard_sidebar() );
	}
}
