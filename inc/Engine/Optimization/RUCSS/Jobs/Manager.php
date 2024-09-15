<?php

namespace WP_Rocket\Engine\Optimization\RUCSS\Jobs;

use WP_Rocket\Logger\LoggerAware;
use WP_Rocket\Logger\LoggerAwareInterface;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\UsedCSS as UsedCSS_Query;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\Filesystem;
use WP_Rocket\Engine\Common\Context\ContextInterface;
use WP_Rocket\Engine\Optimization\CSSTrait;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Common\JobManager\Managers\AbstractManager;
use WP_Rocket\Engine\Common\JobManager\Managers\ManagerInterface;

class Manager implements ManagerInterface, LoggerAwareInterface {
	use LoggerAware;
	use CSSTrait;
	use AbstractManager;

	/**
	 * UsedCss Query instance.
	 *
	 * @var UsedCSS_Query
	 */
	protected $query;

	/**
	 * Filesystem instance
	 *
	 * @var Filesystem
	 */
	private $filesystem;

	/**
	 * RUCSS Context.
	 *
	 * @var ContextInterface
	 */
	protected $context;

	/**
	 * The type of optimization applied for the current job.
	 *
	 * @var string
	 */
	protected $optimization_type = 'rucss';

	/**
	 * Check if manager can process.
	 *
	 * @var boolean
	 */
	protected $can_process = true;

	/**
	 * Plugin options instance.
	 *
	 * @var Options_Data
	 */
	protected $options;

	/**
	 * Instantiate the class.
	 *
	 * @param UsedCSS_Query    $query Usedcss Query instance.
	 * @param Filesystem       $filesystem Filesystem instance.
	 * @param ContextInterface $context RUCSS Context.
	 * @param Options_Data     $options Options instance.
	 */
	public function __construct(
		UsedCSS_Query $query,
		Filesystem $filesystem,
		ContextInterface $context,
		Options_Data $options
	) {
		$this->query      = $query;
		$this->filesystem = $filesystem;
		$this->context    = $context;
		$this->options    = $options;
	}

	/**
	 * Get pending jobs from db.
	 *
	 * @param integer $num_rows Number of rows to grab.
	 * @return array
	 */
	public function get_pending_jobs( int $num_rows ): array {
		$this->logger::debug( "RUCSS: Start getting number of {$num_rows} pending jobs." );

		$pending_jobs = $this->query->get_pending_jobs( $num_rows );

		if ( ! $pending_jobs ) {
			$this->logger::debug( 'RUCSS: No pending jobs are there.' );

			return [];
		}

		return $pending_jobs;
	}

	/**
	 * Validate SaaS response and fail job.
	 *
	 * @param array  $job_details Details related to the job..
	 * @param object $row_details Details related to the row.
	 * @param string $optimization_type The type of optimization applied for the current job.
	 *
	 * @return void
	 */
	public function validate_and_fail( array $job_details, $row_details, string $optimization_type ): void {
		if ( 'all' !== $optimization_type && $this->optimization_type !== $optimization_type ) {
			return;
		}

		/**
		 * Filters the rocket min rucss css result size.
		 *
		 * @since 3.13.3
		 *
		 * @param int $min_rucss_size min size.
		 */
		$min_rucss_size = wpm_apply_filters_typed( 'integer', 'rocket_min_rucss_size', 150 );

		if ( isset( $job_details['contents']['shakedCSS_size'] ) && intval( $job_details['contents']['shakedCSS_size'] ) < $min_rucss_size ) {
			$message = 'RUCSS: shakedCSS size is less than ' . $min_rucss_size;
			$this->logger::error( $message );
			$this->make_status_failed( $row_details->url, $row_details->is_mobile, '500', $message );

			$this->can_process = false;
		}
	}

	/**
	 * Process SaaS response.
	 *
	 * @param array  $job_details Details related to the job..
	 * @param object $row_details Details related to the row.
	 * @param string $optimization_type The type of optimization applied for the current job.
	 *
	 * @return void
	 */
	public function process( array $job_details, $row_details, string $optimization_type ): void {
		if ( ! $this->is_allowed( $optimization_type ) || ! $this->can_process ) {
			return;
		}

		$css = $this->apply_font_display_swap( $job_details['contents']['shakedCSS'] );

		/**
		 * RUCSS hash.
		 *
		 * @param string $hash RUCSS hash.
		 * @param string $css RUCSS content.
		 * @param UsedCSSRow $row_details Job details.
		 */
		$hash = (string) apply_filters( 'rocket_rucss_hash',  md5( $css ), $css, $row_details );

		if ( ! $this->filesystem->write_used_css( $hash, $css ) ) {
			$message = 'RUCSS: Could not write used CSS to the filesystem: ' . $row_details->url;
			$this->logger::error( $message );
			$this->query->make_status_failed( $row_details->url, $row_details->is_mobile, '', $job_details['message'] );

			return;
		}

		// Everything is fine, save the usedcss into DB, change status to completed and reset queue_name and job_id.
		$this->logger::debug( 'RUCSS: Save used CSS for url: ' . $row_details->url );
		$this->query->make_status_completed( $row_details->url, $row_details->is_mobile, $hash );
	}

	/**
	 * Set the request parameter to be sent to the SaaS
	 *
	 * @return array
	 */
	public function set_request_param(): array {
		/**
		 * Filters the RUCSS safelist
		 *
		 * @since 3.11
		 *
		 * @param array $safelist Array of safelist values.
		 */
		$safelist = apply_filters( 'rocket_rucss_safelist', $this->options->get( 'remove_unused_css_safelist', [] ) );

		/**
		 * Filters the styles attributes to be skipped (blocked) by RUCSS.
		 *
		 * @since 3.14
		 *
		 * @param array $skipped_attr Array of safelist values.
		 */
		$skipped_attr = wpm_apply_filters_typed( 'array', 'rocket_rucss_skip_styles_with_attr', [] );

		return [
			'rucss_safelist'    => $safelist,
			'skip_attr'         => $skipped_attr,
			'optimization_list' => [
				'rucss',
			],
		];
	}

	/**
	 * Get the optimization type from the DB Row.
	 *
	 * @param object $row DB Row Object.
	 * @return boolean|string
	 */
	public function get_optimization_type_from_row( $row ) {
		if ( ! isset( $row->css ) ) {
			return false;
		}

		return $this->optimization_type;
	}
}
