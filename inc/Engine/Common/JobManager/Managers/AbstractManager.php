<?php

namespace WP_Rocket\Engine\Common\JobManager\Managers;

use WP_Rocket\Engine\Common\Context\ContextInterface;

class AbstractManager {
    /**
	 * Query instance.
	 *
	 * @var object
	 */
	protected $query;

    /**
     * Context.
     *
     * @var ContextInterface
     */
    protected $context;

    /**
	 * Determine if the action is allowed.
	 *
	 * @return boolean
	 */
	public function is_allowed(): bool {
		return $this->context->is_allowed();
	}

    /**
	 * Send the request to add url into the queue.
	 *
	 * @param string $url page URL.
	 * @param bool   $is_mobile page is for mobile.
	 *
	 * @return void
	 */
    public function add_url_to_the_queue( string $url, bool $is_mobile ) {
		$row = $this->query->get_row( $url, $is_mobile );

		if ( empty( $row ) ) {
			$this->query->create_new_job( $url, '', '', $is_mobile );
			return;
		}
		$this->query->reset_job( (int) $row->id );
	}

    /**
	 * Clear failed jobs.
	 *
	 * @param float  $delay delay before the urls are deleted.
	 * @param string $unit unit from the delay.
	 * @return array
	 */
    public function clear_failed_jobs( float $delay, string $unit ): array {
        $rows = $this->query->get_failed_rows( $delay, $unit );

        if ( empty( $rows ) ) {
            return [];
        }

        $failed_urls = [];

        foreach ( $rows as  $row ) {
            $failed_urls[] = $row->url;

            $id = (int) $row->id;

            if ( empty( $id ) ) {
                continue;
            }

            $this->add_url_to_the_queue( $row->url, (bool) $row->is_mobile );
        }

        return $failed_urls;
    }
}