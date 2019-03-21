<?php

use Action_Scheduler\Migration\ActionScheduler_MigrationConfig;
use Action_Scheduler\Migration\ActionScheduler_MigrationScheduler;
use Action_Scheduler\WP_CLI\ActionScheduler_WPCLI_Migration_Command;
use Action_Scheduler\WP_CLI\ActionScheduler_WPCLI_ProgressBar;

/**
 * Class ActionScheduler_Data
 *
 * The main plugin/initialization class for the
 * Action Scheduler Custom Tables plugin.
 *
 * Responsible for hooking everything up with WordPress.
 *
 * @package Action_Scheduler
 *
 * @codeCoverageIgnore
 */
class ActionScheduler_Data {
	private static $instance;

	/** @var Migration_Scheduler */
	private $migration_scheduler;

	/** @var string */
	private $store_classname;

	/** @var string */
	private $logger_classname;

	/** @var bool */
	private $migrate_custom_store;

	/**
	 * ActionScheduler_Data constructor.
	 *
	 * @param Migration_Scheduler $migration_scheduler
	 */
	public function __construct( ActionScheduler_MigrationScheduler $migration_scheduler ) {
		$this->migration_scheduler = $migration_scheduler;
		$this->store_classname     = '';
	}

	/**
	 * Get the action store class name.
	 *
	 * @param string $class Classname of the store class.
	 *
	 * @return string
	 */
	public function get_store_class( $class ) {
		if ( $this->migration_scheduler->is_migration_complete() ) {
			return 'ActionScheduler_DBStore';
		} elseif ( ActionScheduler_Store::DEFAULT_CLASS !== $class ) {
			$this->store_classname = $class;
			return $class;
		} else {
			return 'ActionScheduler_HybridStore';
		}
	}

	/**
	 * Get the action logger class name.
	 *
	 * @param string $class Classname of the logger class.
	 *
	 * @return string
	 */
	public function get_logger_class( $class ) {
		ActionScheduler_Store::instance();

		if ( $this->store_classname ) {
			$this->logger_classname = $class;
			return $class;
		} else {
			return 'ActionScheduler_DBLogger';
		}
	}

	/**
	 * Register the WP-CLI command to handle bulk migrations
	 *
	 * @return void
	 */
	public function register_cli_command() {
		if ( defined( 'WP_CLI' ) && WP_CLI && $this->allow_custom_migration() && ! $this->migration_scheduler->is_migration_complete() && $this->migration_scheduler->dependencies_met() ) {
			$command = new ActionScheduler_WPCLI_Migration_Command();
			$command->register();
		}
	}

	/**
	 * Set up the background migration process
	 *
	 * @return void
	 */
	public function schedule_migration() {
		if ( $this->migration_scheduler->is_migration_complete() || $this->migration_scheduler->is_migration_scheduled() ) {
			return;
		}

		$this->migration_scheduler->schedule_migration();
	}

	/**
	 * Get the default migration config object
	 *
	 * @return Migration\Migration_Config
	 */
	public function get_migration_config_object() {
		$source_store = $this->store_classname ? new $this->store_classname() : new ActionScheduler_wpPostStore();
		$source_logger = $this->logger_classname ? new $this->logger_classname() : new ActionScheduler_wpCommentLogger();

		$config = new ActionScheduler_MigrationConfig();
		$config->set_source_store( $source_store );
		$config->set_source_logger( $source_logger );
		$config->set_destination_store( new ActionScheduler_DBStoreMigrator() );
		$config->set_destination_logger( new ActionScheduler_DBLogger() );

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			$config->set_progress_bar( new ActionScheduler_WPCLI_ProgressBar( '', 0 ) );
		}

		return apply_filters( 'action_scheduler/migration_config', $config );
	}

	public function hook_admin_notices() {
		if ( ! $this->allow_custom_migration() || $this->migration_scheduler->is_migration_complete() ) {
			return;
		}
		add_action( 'admin_notices', array( $this, 'display_migration_notice' ), 10, 0 );
	}

	public function display_migration_notice() {
		printf( '<div class="notice notice-warning"><p>%s</p></div>', __( 'Action Scheduler migration in progress. The list of scheduled actions may be incomplete.' ) );
	}

	private function hook() {
		add_filter( 'action_scheduler_store_class', array( $this, 'get_store_class' ), 100, 1 );
		add_filter( 'action_scheduler_logger_class', array( $this, 'get_logger_class' ), 100, 1 );
		add_action( 'init', array( $this, 'maybe_hook_migration' ) );
		add_action( 'shutdown', array( $this, 'schedule_migration' ), 0, 0 );

		// Action Scheduler may be displayed as a Tools screen or WooCommerce > Status administration screen
		add_action( 'load-tools_page_action-scheduler', array( $this, 'hook_admin_notices' ), 10, 0 );
		add_action( 'load-woocommerce_page_wc-status', array( $this, 'hook_admin_notices' ), 10, 0 );
	}

	/**
	 * Possibly hook the migration scheduler action.
	 *
	 * @author Jeremy Pry
	 */
	public function maybe_hook_migration() {
		if ( ! $this->allow_custom_migration() || $this->migration_scheduler->is_migration_complete() ) {
			return;
		}

		$this->migration_scheduler->hook();
		$this->register_cli_command();
	}

	public function allow_custom_migration() {
		if ( null === $this->migrate_custom_store ) {
			$this->migrate_custom_store = apply_filters( 'action_scheduler_migrate_custom_data_store', false );
		}

		return empty( $this->store_classname ) || $this->migrate_custom_store;
	}

	public static function init() {
		if ( self::instance()->migration_scheduler->dependencies_met() ) {
			self::instance()->hook();
		}
	}

	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new static( new ActionScheduler_MigrationScheduler() );
		}

		return self::$instance;
	}
}
