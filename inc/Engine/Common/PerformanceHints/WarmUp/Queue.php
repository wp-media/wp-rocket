<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Common\PerformanceHints\WarmUp;

use WP_Rocket\Engine\Common\Queue\AbstractASQueue;

class Queue extends AbstractASQueue {
	/**
	 * PerformanceHints queue group
	 *
	 * @var string
	 */
	protected $group = 'rocket-performance-hints-warmup';

	/**
	 * Add an async job to warm up home links
	 *
	 * @return int
	 */
	public function add_job_warmup(): int {
		return $this->add_async( 'rocket_job_warmup' );
	}

	/**
	 * Add an async job to send URL to SaaS for warmup
	 *
	 * @param string $url URL to warm up.
	 *
	 * @return int
	 */
	public function add_job_warmup_url( string $url ): int {
		return $this->add_async( 'rocket_job_warmup_url', [ $url ] );
	}
}
