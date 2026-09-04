<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\CDN\Cloudflare;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\TestCase;
use WP_Rocket\ThirdParty\Plugins\CDN\Cloudflare;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\CDN\Cloudflare::display_server_pushing_mode_notice
 *
 * @group AdminOnly
 * @group ThirdParty
 * @group CloudflarePlugin
 */
class Test_DisplayServerPushingModeNotice extends TestCase{

	protected $rucss;
    protected $combine_css;
    private static $admin_user_id = 0;
	private static $contributer_user_id = 0;

	/**
	 * @var \WP_Rocket\Event_Management\Event_Manager
	 */
	private $event_manager;

	/**
	 * @var Cloudflare
	 */
	private $cloudflare;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		$admin_role = get_role( 'administrator' );
		$admin_role->add_cap( 'rocket_manage_options' );

		self::$admin_user_id = static::factory()->user->create( [ 'role' => 'administrator' ] );
		self::$contributer_user_id = static::factory()->user->create( [ 'role' => 'contributor' ] );
	}

	public function set_up()
	{
		parent::set_up();

		// PluginResolver gates cloudflare_plugin_subscriber out of the container at boot
		// (the official Cloudflare plugin isn't installed in this test environment), so its
		// display_server_pushing_mode_notice callback was never wired to the event manager.
		// Build the subscriber directly (same approach Test_ExcludeDelayJs uses for Termly)
		// and wire it here so the notice under test actually fires.
		$container            = apply_filters( 'rocket_container', null );
		$this->cloudflare     = new Cloudflare(
			$container->get( 'options' ),
			$container->get( 'options_api' ),
			$container->get( 'beacon' ),
			$container->get( 'cloudflare_plugin_facade' )
		);
		$this->event_manager = $container->get( 'event_manager' );
		$this->event_manager->add_subscriber( $this->cloudflare );
	}

	public function tear_down()
	{
		$this->event_manager->remove_subscriber( $this->cloudflare );

		remove_filter('pre_get_rocket_option_remove_unused_css', [$this, 'rucss']);
        remove_filter('pre_get_rocket_option_minify_concatenate_css', [$this, 'combine_css']);

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected($config, $expected) {

		Functions\when( 'rocket_is_cloudflare' )->justReturn( true );

		$this->constants['CLOUDFLARE_PLUGIN_DIR'] = true;
        $this->constants['CLOUDFLARE_HTTP2_SERVER_PUSH_ACTIVE'] = $config['server_push'];

        if ( $config['capability'] ) {
            $user_id = self::$admin_user_id;
        }else{
            $user_id = self::$contributer_user_id;
        }
        wp_set_current_user( $user_id );

        set_current_screen( $config['current_screen']->id );

		$this->rucss = $config['remove_unused_css'];
        $this->combine_css = $config['minify_concatenate_css'];

        add_filter('pre_get_rocket_option_remove_unused_css', [$this, 'rucss']);
        add_filter('pre_get_rocket_option_minify_concatenate_css', [$this, 'combine_css']);

        update_user_meta( $user_id, 'rocket_boxes', $config['boxes'] );

		ob_start();
		do_action('admin_notices');
		$result = ob_get_clean();

        $this->assertStringContainsString(
            $this->format_the_html( $expected['html'] ),
            $this->format_the_html( $result )
        );
	}

	public function rucss() {
		return $this->rucss;
	}

    public function combine_css() {
        return $this->combine_css;
    }
}
