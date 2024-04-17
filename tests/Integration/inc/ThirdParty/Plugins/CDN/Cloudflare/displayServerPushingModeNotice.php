<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\CDN\Cloudflare;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\CDN\Cloudflare::display_server_pushing_mode_notice
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

	public static function set_up_before_class() {
		parent::set_up_before_class();

		$admin_role = get_role( 'administrator' );
		$admin_role->add_cap( 'rocket_manage_options' );

		self::$admin_user_id = static::factory()->user->create( [ 'role' => 'administrator' ] );
		self::$contributer_user_id = static::factory()->user->create( [ 'role' => 'contributor' ] );
	}

	public function tear_down()
	{
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
