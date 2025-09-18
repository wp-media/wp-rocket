<?php

namespace WP_Rocket\Tests\Integration\Inc\Engine\Admin\PerformanceMonitoring\Subscriber;

use WP_Rocket\Tests\Integration\AdminTestCase;
use WP_Rocket\Tests\Integration\TestCase;
use Brain\Monkey\Functions;

/**
 * Test class covering WP_Rocket\Engine\Admin\PerformanceMonitoring\Subscriber::check_upgrade
 *
 * @group PerformanceMonitoring
 * @group AdminOnly
 */
class TestCheckUpgrade extends AdminTestCase {
    /**
     * Instance of the User.
     *
     * @var \WP_Rocket\Engine\License\API\User
     */
    private $user;

	protected $action_called;

	protected $wp_redirect_called;

    /**
     * Setup test.
     */
    public function set_up() {
        parent::set_up();

		$this->wp_redirect_called = false;

		$container = apply_filters('rocket_container', null);
		$this->user = $container->get('user');

        $this->unregisterAllCallbacksExcept('admin_init', 'check_upgrade', 10);

		add_filter('wp_rocket_pma_upgraded', [$this, 'mock_wp_rocket_pma_upgraded']);
    }

    /**
     * Teardown test.
     */
    public function tear_down() {
		remove_filter( 'wp_redirect', [ $this, 'return_empty_string' ], PHP_INT_MAX );

		remove_filter('wp_rocket_pma_upgraded', [$this, 'mock_wp_rocket_pma_upgraded']);

        $this->restoreWpHook('admin_init');
        unset($_GET['upgrade']);

        parent::tear_down();
    }

    /**
     * @dataProvider configTestData
     */
    public function testShouldDoExpected($config, $expected) {
		self::removeDBHooks();
		$this->setCurrentUser( 'administrator' );
		update_user_meta( $this->user_id, 'rocket_boxes', ['pma_upgrade_notice'] );
		// Set up the GET parameter
        if ($config['upgrade_param']) {
            $_GET['upgrade'] = '1';
        }

        // Mock user data
        $user_data = $config['user_data']();
        $this->user->set_user($user_data);

        // Track action calls
        $this->action_called = false;

        // Mock redirect function
		add_filter( 'wp_redirect', [ $this, 'return_empty_string' ], PHP_INT_MAX );

        // Execute the method
		$this->fireAdminInit();

		$this->assertSame($expected['upgrade_action'], $this->action_called);

        if ($expected['has_user']) {
			$this->assertFalse($this->is_notice_present('pma_upgrade_notice'));
            $this->assertTrue($this->wp_redirect_called);
        }
    }

	protected function is_notice_present(string $notice_id) {
		$values = get_user_meta( get_current_user_id(), 'rocket_boxes', true ) ?: [];
		return in_array( $notice_id, $values, true );
	}

	public function mock_wp_rocket_pma_upgraded() {
		$this->action_called = true;
	}

	public function mock_http_request($resp, $args, $url) {
		if( strpos( $url, '/v1/user.php' ) !== false) {
			return [

			];
		}
		return $resp;
	}

	public function return_empty_string() {
		$this->wp_redirect_called = true;
		return '';
	}
}
