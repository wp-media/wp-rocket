<?php
namespace WP_Rocket\Subscriber\Optimization;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Admin\Options_Data as Options;

/**
 * Dequeue jQuery Migrate
 *
 * @since 3.4
 * @author Soponar Cristina
 */
class Dequeue_JQuery_Migrate_Subscriber implements Subscriber_Interface
{
    /**
     * Plugin options
     *
     * @since 3.4
     * @author Soponar Cristina
     *
     * @var Options
     */
    private $options;

    /**
     * Constructor
     *
     * @since 3.4
     * @author Soponar Cristina
     *
     * @param Options $options Plugin options.
     */
    public function __construct(Options $options)
    {
        $this->options = $options;
    }

    /**
     * @inheritDoc
     */
    public static function get_subscribed_events()
    {
        return [
            'wp_default_scripts' => [ 'dequeue_jquery_migrate' ],
        ];
    }

    /**
     * Dequeue jquery migrate
     *
     * @since 3.4
     * @author Soponar Cristina
     *
     * @return string
     */
    public function dequeue_jquery_migrate($scripts)
    {
        if ( defined( 'DONOTROCKETOPTIMIZE' ) && DONOTROCKETOPTIMIZE ) {
			return false;
        }

        if ( ! $this->is_allowed() ) {
            return false;
        }

        if ( ! empty($scripts->registered['jquery']) ) {
            $jquery_dependencies                 = $scripts->registered['jquery']->deps;
            $scripts->registered['jquery']->deps = array_diff($jquery_dependencies, array( 'jquery-migrate' ));
        }
    }

    /**
     * Check if dequeue jquery migrate option is enabled
     *
     * @since 3.4
     * @author Soponar Cristina
     *
     * @return boolean
     */
    protected function is_allowed()
    {
        if ( ! $this->options->get('dequeue_jquery_migrate') ) {
            return false;
        }

        return true;
    }
}
