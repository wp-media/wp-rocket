<?php
namespace WP_Rocket\Tests\Integration\Subscriber\Optimization;

use PHPUnit\Framework\TestCase;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Admin\Options;
use WP_Rocket\Subscriber\Optimization\Dequeue_JQuery_Migrate_Subscriber;

/**
 * Test Dequeue jQuery Migrate
 *
 * @since 3.4
 * @author Soponar Cristina
 */
class TestDequeueJQueryMigrate extends TestCase
{
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testShouldNotDequeueJQueryMigrate()
    {
        update_option(
            'wp_rocket_settings',
            [
                'dequeue_jquery_migrate' => false,
            ]
        );
        $scripts                           = wp_scripts();
        $options                           = new Options_Data((new Options('wp_rocket_'))->get('settings'));
        $dequeue_jquery_migrate_subscriber = new Dequeue_JQuery_Migrate_Subscriber($options);

        $this->assertSame(
            false,
            $dequeue_jquery_migrate_subscriber->dequeue_jquery_migrate($scripts)
        );
	}

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testShouldNotDequeueJQueryMigrateWhenDONOTROCKETOPTIMIZE()
    {
        update_option(
            'wp_rocket_settings',
            [
                'dequeue_jquery_migrate' => true,
            ]
        );

        define('DONOTROCKETOPTIMIZE', true);

        $scripts                           = wp_scripts();
        $options                           = new Options_Data((new Options('wp_rocket_'))->get('settings'));
        $dequeue_jquery_migrate_subscriber = new Dequeue_JQuery_Migrate_Subscriber($options);

        $this->assertSame(
            false,
            $dequeue_jquery_migrate_subscriber->dequeue_jquery_migrate($scripts)
        );
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testShouldDequeueJQueryMigrate()
    {
        update_option(
            'wp_rocket_settings',
            [
                'dequeue_jquery_migrate' => true,
            ]
        );

        $options                           = new Options_Data((new Options('wp_rocket_'))->get('settings'));
        $dequeue_jquery_migrate_subscriber = new Dequeue_JQuery_Migrate_Subscriber($options);

        $scripts = wp_scripts();
        $dequeue_jquery_migrate_subscriber->dequeue_jquery_migrate($scripts);

        $this->assertFalse(wp_script_is('jquery-migrate'));
    }
}
