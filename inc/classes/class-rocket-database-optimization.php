<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Handles the database optimization process.
 *
 * @since 2.11
 * @author Remy Perona
 */
class Rocket_Database_Optimization {
	/**
	 * Process
	 *
	 * @since 2.11
	 * @var object $process Background Process instance.
	 * @access protected
	 */
	protected $process;

	/**
	 * Process
	 *
	 * @var array
	 * @access public
	 */
	public $options;

	/**
	 * Class constructor.
	 *
	 * @since 2.11
	 * @author Remy Perona
	 */
	public function __construct() {
		$this->process = new Rocket_Background_Database_Optimization();
		$this->options = array(
			'revisions',
			'auto_drafts',
			'trashed_posts',
			'spam_comments',
			'trashed_comments',
			'expired_transients',
			'all_transients',
			'optimize_tables',
		);
	}

	/**
	 * Initializes class and hooks.
	 *
	 * @since 2.11
	 * @author Remy Perona
	 */
	public static function init() {
		$self = new self();

		add_action( 'init', array( $self, 'database_optimization_scheduled' ) );
		add_action( 'rocket_database_optimization_time_event', array( $self, 'process_handler' ) );
		add_action( 'update_option_' . WP_ROCKET_SLUG, array( $self, 'save_optimize' ) );
		add_action( 'admin_post_rocket_optimize_database', array( $self, 'optimize' ) );
		add_action( 'admin_notices', array( $self, 'notice_process_running' ) );
		add_action( 'admin_notices', array( $self, 'notice_process_complete' ) );
	}

	/**
	 * Performs the database optimization
	 *
	 * @since 2.11
	 * @author Remy Perona
	 */
	public function process_handler() {
		if ( method_exists( $this->process, 'cancel_process' ) ) {
			$this->process->cancel_process();
		}

		foreach ( $this->options as $option ) {
			if ( get_rocket_option( 'database_' . $option, false ) ) {
				$this->process->push_to_queue( $option );
			}
		}

		$this->process->save()->dispatch();
	}

	/**
	 * Launches the database optimization from admin
	 *
	 * @since 2.8
	 * @author Remy Perona
	 *
	 * @see process_handler()
	 */
	public function optimize() {
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'rocket_optimize_database' ) ) {
			wp_nonce_ays( '' );
		}

		$this->process_handler();

		wp_safe_redirect( esc_url_raw( wp_get_referer() ) );
		die();
	}


	/**
	 * Plans database optimization cron
	 * If the task is not programmed, it is automatically triggered
	 *
	 * @since 2.8
	 * @author Remy Perona
	 *
	 * @see process_handler()
	 */
	public function database_optimization_scheduled() {
		if ( get_rocket_option( 'schedule_automatic_cleanup', false ) ) {
			if ( ! wp_next_scheduled( 'rocket_database_optimization_time_event' ) ) {
				wp_schedule_event( time(), get_rocket_option( 'automatic_cleanup_frequency', 'weekly' ), 'rocket_database_optimization_time_event' );
			}
		}
	}

	/**
	 * Launches the database optimization when the settings are saved with save and optimize button
	 *
	 * @since 2.8
	 * @author Remy Perona
	 *
	 * @see process_handler()
	 */
	public function save_optimize() {
		// Performs the database optimization when settings are saved with the "save and optimize" submit button".
		if ( ! empty( $_POST ) && isset( $_POST['wp_rocket_settings']['submit_optimize'] ) ) {
			$this->process_handler();
		}
	}

	/**
	 * Count the number of items concerned by the database cleanup
	 *
	 * @since 2.8
	 * @author Remy Perona
	 *
	 * @param string $type Item type to count.
	 * @return int Number of items for this type
	 */
	public function count_cleanup_items( $type ) {
		global $wpdb;

		$count = 0;

		switch ( $type ) {
			case 'revisions':
				$count = $wpdb->get_var( "SELECT COUNT(ID) FROM $wpdb->posts WHERE post_type = 'revision'" );
				break;
			case 'auto_drafts':
				$count = $wpdb->get_var( "SELECT COUNT(ID) FROM $wpdb->posts WHERE post_status = 'auto-draft'" );
				break;
			case 'trashed_posts':
				$count = $wpdb->get_var( "SELECT COUNT(ID) FROM $wpdb->posts WHERE post_status = 'trash'" );
				break;
			case 'spam_comments':
				$count = $wpdb->get_var( "SELECT COUNT(comment_ID) FROM $wpdb->comments WHERE comment_approved = 'spam'" );
				break;
			case 'trashed_comments':
				$count = $wpdb->get_var( "SELECT COUNT(comment_ID) FROM $wpdb->comments WHERE (comment_approved = 'trash' OR comment_approved = 'post-trashed')" );
				break;
			case 'expired_transients':
				$time  = isset( $_SERVER['REQUEST_TIME'] ) ? (int) $_SERVER['REQUEST_TIME'] : time();
				$count = $wpdb->get_var( "SELECT COUNT(option_name) FROM $wpdb->options WHERE option_name LIKE '_transient_timeout%' AND option_value < $time" );
				break;
			case 'all_transients':
				$count = $wpdb->get_var( "SELECT COUNT(option_id) FROM $wpdb->options WHERE option_name LIKE '_transient_%' OR option_name LIKE '_site_transient_%'" );
				break;
			case 'optimize_tables':
				$count = $wpdb->get_var( "SELECT COUNT(table_name) FROM information_schema.tables WHERE table_schema = '" . DB_NAME . "' and Engine <> 'InnoDB' and data_free > 0" );
				break;
		}

		return $count;
	}

	/**
	 * This notice is displayed after launching the database optimization process
	 *
	 * @since 2.11
	 * @author Remy Perona
	 */
	public function notice_process_running() {
		global $current_user;

		$screen              = get_current_screen();
		$rocket_wl_name      = get_rocket_option( 'wl_plugin_name', null );
		$wp_rocket_screen_id = isset( $rocket_wl_name ) ? 'settings_page_' . sanitize_key( $rocket_wl_name ) : 'settings_page_wprocket';

		/** This filter is documented in inc/admin-bar.php */
		if ( ! current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) ) ) {
			return;
		}

		if ( $screen->id !== $wp_rocket_screen_id ) {
			return;
		}

		$notice = get_transient( 'rocket_database_optimization_process' );

		if ( ! $notice ) {
			return;
		}

		?>
		<div class="notice notice-info is-dismissible">
			<p><?php _e( 'Database optimization process is running', 'rocket' ); ?></p>
		</div>
		<?php
	}

	/**
	 * This notice is displayed when the database optimization process is complete
	 *
	 * @since 2.11
	 * @author Remy Perona
	 */
	public function notice_process_complete() {
		global $current_user;

		$screen              = get_current_screen();
		$rocket_wl_name      = get_rocket_option( 'wl_plugin_name', null );
		$wp_rocket_screen_id = isset( $rocket_wl_name ) ? 'settings_page_' . sanitize_key( $rocket_wl_name ) : 'settings_page_wprocket';

		/** This filter is documented in inc/admin-bar.php */
		if ( ! current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) ) ) {
			return;
		}

		if ( $screen->id !== $wp_rocket_screen_id ) {
			return;
		}

		$optimized = get_transient( 'rocket_database_optimization_process_complete' );

		if ( false === $optimized ) {
			return;
		}

		delete_transient( 'rocket_database_optimization_process_complete' );
		?>
		<div class="notice notice-success is-dismissible">
			<?php if ( empty( $optimized ) ) : ?>
			<p><?php _e( 'Database optimization process is complete. Everything was already optimized!', 'rocket' ); ?></p>
			<?php else : ?>
			<p><?php _e( 'Database optimization process is complete. List of optimized items below:', 'rocket' ); ?></p>
			<ul>
			<?php foreach ( $optimized as $k => $number ) : ?>
				<li>
				<?php
					/* translators: %1$d = number of items optimized, %2$s = type of optimization */
					printf( __( '%1$d %2$s optimized.', 'rocket' ), $number, $k );

					/**
					 * “I feel like I’m kind of lazy, but I keep the yard looking good.”
					 * —Kris Kristofferson
					 *
					 * We shall do the same, shan’t we?
					 *
					 * @todo Replace $k in the printf() arguments with something nicer to read.
					 */
				?>
				</li>
			<?php endforeach; ?>
			</ul>
			<?php endif; ?>
		</div>
		<?php
	}
}
