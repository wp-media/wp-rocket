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
	 * Rocket_Background_Database_Optimization instance
	 *
	 * @since 2.11
	 * @var Rocket_Background_Database_Optimization $process Background Process instance.
	 * @access protected
	 */
	protected $process;

	/**
	 * Array of option name/label pairs.
	 *
	 * @var array
	 * @access private
	 */
	private $options;

	/**
	 * Class constructor.
	 *
	 * @since 2.11
	 * @author Remy Perona
	 */
	public function __construct() {
		$this->process = new Rocket_Background_Database_Optimization();
		$this->options = array(
			'database_revisions'          => __( 'Revisions', 'rocket' ),
			'database_auto_drafts'        => __( 'Auto Drafts', 'rocket' ),
			'database_trashed_posts'      => __( 'Trashed Posts', 'rocket' ),
			'database_spam_comments'      => __( 'Spam Comments', 'rocket' ),
			'database_trashed_comments'   => __( 'Trashed Comments', 'rocket' ),
			'database_expired_transients' => __( 'Expired transients', 'rocket' ),
			'database_all_transients'     => __( 'Transients', 'rocket' ),
			'database_optimize_tables'    => __( 'Tables', 'rocket' ),
		);
	}

	/**
	 * Get Database options
	 *
	 * @since 3.0.4
	 * @author Remy Perona
	 *
	 * @return array
	 */
	public function get_options() {
		return $this->options;
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
		add_action( 'rocket_database_optimization_time_event', array( $self, 'cron_optimize' ) );
		add_filter( 'pre_update_option_' . WP_ROCKET_SLUG, array( $self, 'save_optimize' ) );
		add_action( 'admin_notices', array( $self, 'notice_process_running' ) );
		add_action( 'admin_notices', array( $self, 'notice_process_complete' ) );
	}

	/**
	 * Performs the database optimization
	 *
	 * @since 2.11
	 * @author Remy Perona
	 *
	 * @param array $options WP Rocket Database options.
	 */
	private function process_handler( $options ) {
		if ( method_exists( $this->process, 'cancel_process' ) ) {
			$this->process->cancel_process();
		}

		array_map( array( $this->process, 'push_to_queue' ), $options );

		$this->process->save()->dispatch();
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
	 * Database Optimization cron callback
	 *
	 * @since 3.0.4
	 * @author Remy Perona
	 */
	public function cron_optimize() {
		$items = array_filter( array_keys( $this->options ), 'get_rocket_option' );

		if ( empty( $items ) ) {
			return;
		}

		$this->process_handler( $items );
	}

	/**
	 * Launches the database optimization when the settings are saved with optimize button
	 *
	 * @since 2.8
	 * @author Remy Perona
	 *
	 * @see process_handler()
	 *
	 * @param array $value The new, unserialized option value.
	 * @return array
	 */
	public function save_optimize( $value ) {
		if ( empty( $_POST ) ) {
			return $value;
		}

		if ( empty( $value ) || ! isset( $value['submit_optimize'] ) ) {
			return $value;
		}

		unset( $value['submit_optimize'] );

		if ( ! current_user_can( apply_filters( 'rocket_capability', 'manage_options' ) ) ) {
			return $value;
		}

		$items = [];

		foreach ( $value as $key => $option_value ) {
			if ( isset( $this->options[ $key ] ) && 1 === $option_value ) {
				$items[] = $key;
			}
		}

		if ( empty( $items ) ) {
			return $value;
		}

		$this->process_handler( $items );

		return $value;
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
			case 'database_revisions':
				$count = $wpdb->get_var( "SELECT COUNT(ID) FROM $wpdb->posts WHERE post_type = 'revision'" );
				break;
			case 'database_auto_drafts':
				$count = $wpdb->get_var( "SELECT COUNT(ID) FROM $wpdb->posts WHERE post_status = 'auto-draft'" );
				break;
			case 'database_trashed_posts':
				$count = $wpdb->get_var( "SELECT COUNT(ID) FROM $wpdb->posts WHERE post_status = 'trash'" );
				break;
			case 'database_spam_comments':
				$count = $wpdb->get_var( "SELECT COUNT(comment_ID) FROM $wpdb->comments WHERE comment_approved = 'spam'" );
				break;
			case 'database_trashed_comments':
				$count = $wpdb->get_var( "SELECT COUNT(comment_ID) FROM $wpdb->comments WHERE (comment_approved = 'trash' OR comment_approved = 'post-trashed')" );
				break;
			case 'database_expired_transients':
				$time  = isset( $_SERVER['REQUEST_TIME'] ) ? (int) $_SERVER['REQUEST_TIME'] : time();
				$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(option_name) FROM $wpdb->options WHERE option_name LIKE %s AND option_value < %d", $wpdb->esc_like( '_transient_timeout' ) . '%', $time ) );
				break;
			case 'database_all_transients':
				$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(option_id) FROM $wpdb->options WHERE option_name LIKE %s OR option_name LIKE %s", $wpdb->esc_like( '_transient_' ) . '%', $wpdb->esc_like( '_site_transient_' ) . '%' ) );
				break;
			case 'database_optimize_tables':
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
		$screen = get_current_screen();

		/** This filter is documented in inc/admin-bar.php */
		if ( ! current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) ) ) {
			return;
		}

		if ( 'settings_page_wprocket' !== $screen->id ) {
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
		$screen = get_current_screen();

		/** This filter is documented in inc/admin-bar.php */
		if ( ! current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) ) ) {
			return;
		}

		if ( 'settings_page_wprocket' !== $screen->id ) {
			return;
		}

		$optimized = get_transient( 'rocket_database_optimization_process_complete' );

		if ( false === $optimized ) {
			return;
		}

		delete_transient( 'rocket_database_optimization_process_complete' );
		?>
		<div class="notice notice-success is-dismissible">
			<?php
			$message = __( 'Database optimization process is complete. Everything was already optimized!', 'rocket' );

			if ( ! empty( $optimized ) ) {
				$message = __( 'Database optimization process is complete. List of optimized items below:', 'rocket' );
			}
			?>

			<p><?php echo $message; ?></p>
			<?php if ( ! empty( $optimized ) ) : ?>
			<ul>
			<?php foreach ( $optimized as $key => $number ) : ?>
				<li>
				<?php
					/* translators: %1$d = number of items optimized, %2$s = type of optimization */
					printf( __( '%1$d %2$s optimized.', 'rocket' ), $number, $this->options[ $key ] );
				?>
				</li>
			<?php endforeach; ?>
			</ul>
			<?php endif; ?>
		</div>
		<?php
	}
}
