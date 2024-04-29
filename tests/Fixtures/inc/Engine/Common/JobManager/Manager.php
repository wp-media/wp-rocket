<?php
namespace WP_Rocket\Tests\Fixtures\inc\Engine\Common\JobManager;

use WP_Rocket\Engine\Common\JobManager\Managers\ManagerInterface;

class Manager implements ManagerInterface {

	public function get_pending_jobs( int $num_rows ): array {
        return [];
    }

	public function validate_and_fail( array $job_details, $row_details, string $optimization_type ): void {
        return;
    }

	public function process( array $job_details, $row_details, string $optimization_type ): void {
        return;
    }

	public function set_request_param(): array {
        return [];
    }

	public function get_optimization_type_from_row( $row ) {
        return true;
    }
}