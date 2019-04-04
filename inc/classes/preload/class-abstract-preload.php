<?php
namespace WP_Rocket\Preload;

/**
 * Abstract preload class
 *
 * @since 3.2
 * @author Remy Perona
 */
abstract class Abstract_Preload {
	/**
	 * Background Process instance
	 *
	 * @since 3.2
	 * @var Full_Process
	 */
	protected $preload_process;

	/**
	 * Constructor
	 *
	 * @since 3.2
	 * @author Remy Perona
	 *
	 * @param Full_Process $preload_process Background Process instance.
	 */
	public function __construct( Full_Process $preload_process ) {
		$this->preload_process = $preload_process;
	}

	/**
	 * Cancels any preload process running
	 *
	 * @since 3.2
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function cancel_preload() {
		delete_transient( 'rocket_preload_running' );

		if ( \method_exists( $this->preload_process, 'cancel_process' ) ) {
			$this->preload_process->cancel_process();
		}
	}

	/**
	 * Checks if a process is already running
	 *
	 * @since 3.2.1.1
	 * @author Remy Perona
	 *
	 * @return boolean
	 */
	public function is_process_running() {
		return $this->preload_process->is_process_running();
	}
}
