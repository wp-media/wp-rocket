<?php

namespace WP_Rocket\Tests\Integration\Inc\Engine\Admin\PerformanceMonitoring\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;
use Brain\Monkey\Functions;
use Mockery;

/**
 * Test class covering WP_Rocket\Engine\Admin\PerformanceMonitoring\Subscriber::reset_user_data
 *
 * @group PerformanceMonitoring
 */
class TestResetUserData extends TestCase {
    /**
     * Instance of the User.
     *
     * @var \WP_Rocket\Engine\License\API\User
     */
    private $user;

    /**
     * Instance of the UserClient.
     *
     * @var \WP_Rocket\Engine\License\API\UserClient
     */
    private $user_client;

    /**
     * Instance of the Subscriber.
     *
     * @var \WP_Rocket\Engine\Admin\PerformanceMonitoring\Subscriber
     */
    private $subscriber;

    /**
     * Mock of the UserClient for method verification.
     *
     * @var \Mockery\MockInterface
     */
    private $user_client_mock;

    /**
     * Mock of the User for method verification.
     *
     * @var \Mockery\MockInterface
     */
    private $user_mock;

    /**
     * Flag to track if flush_cache was called.
     *
     * @var bool
     */
    private $flush_cache_called = false;

    /**
     * Flag to track if set_user was called.
     *
     * @var bool
     */
    private $set_user_called = false;

    /**
     * User data to return from get_user_data.
     *
     * @var mixed
     */
    private $updated_user_data = null;

    /**
     * Setup test.
     */
    public function set_up() {
        parent::set_up();

        $container = apply_filters('rocket_container', null);

        // Get the real user and user_client instances
        $this->user = $container->get('user');
        $this->user_client = $container->get('user_client');
        $this->subscriber = $container->get('pm_subscriber');

        // Create mocks to verify method calls
        $this->user_client_mock = Mockery::mock($this->user_client);
        $this->user_mock = Mockery::mock($this->user);

        // Reset tracking flags
        $this->flush_cache_called = false;
        $this->set_user_called = false;
        $this->updated_user_data = null;
    }

    /**
     * Teardown test.
     */
    public function tear_down() {
        Mockery::close();

        parent::tear_down();
    }

    /**
     * @dataProvider configTestData
     */
    public function testShouldDoExpected($config, $expected) {
        // Set initial user data
        $initial_user_data = $config['user_data']();
        $this->user->set_user($initial_user_data);

        // Verify initial state
        $this->assertEquals(
            $expected['sku_before'],
            $this->user->get_pma_addon_sku_active(),
            'Initial SKU should match expected value'
        );

        // Setup updated user data
        $this->updated_user_data = $config['updated_user_data']();

        // Setup mocks
        $this->user_client_mock->shouldReceive('flush_cache')
            ->andReturnUsing(function() {
                $this->flush_cache_called = true;
            });

        $this->user_client_mock->shouldReceive('get_user_data')
            ->andReturnUsing(function() {
                return $this->updated_user_data;
            });

        $this->user_mock->shouldReceive('set_user')
            ->with($this->updated_user_data)
            ->andReturnUsing(function() {
                $this->set_user_called = true;
                if ($this->updated_user_data) {
                    $this->user->set_user($this->updated_user_data);
                }
            });

        // Setup method overrides for the subscriber
        $reflection = new \ReflectionObject($this->subscriber);

        $user_client_prop = $reflection->getProperty('user_client');
        $user_client_prop->setAccessible(true);
        $user_client_prop->setValue($this->subscriber, $this->user_client_mock);

        $user_prop = $reflection->getProperty('user');
        $user_prop->setAccessible(true);
        $user_prop->setValue($this->subscriber, $this->user_mock);

        // Call the method
        do_action('wp_rocket_pma_upgraded');

        // Verify method calls
        $this->assertEquals(
            $expected['flush_cache_called'],
            $this->flush_cache_called,
            'flush_cache() should ' . ($expected['flush_cache_called'] ? '' : 'not ') . 'be called'
        );

        $this->assertEquals(
            $expected['set_user_called'],
            $this->set_user_called,
            'set_user() should ' . ($expected['set_user_called'] ? '' : 'not ') . 'be called'
        );

        // Verify final state if set_user was expected to be called
        if ($expected['set_user_called']) {
            $this->assertEquals(
                $expected['sku_after'],
                $this->user->get_pma_addon_sku_active(),
                'Final SKU should match expected value'
            );
        }
    }
}
