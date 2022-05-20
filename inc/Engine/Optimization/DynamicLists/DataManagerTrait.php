<?php
declare( strict_types=1 );

namespace WP_Rocket\Engine\Optimization\DynamicLists;

trait DataManagerTrait {
	/**
	 * Filesystem instance
	 *
	 * @var \WP_Filesystem_Direct
	 */
	private $filesystem;

	/**
	 * Get json file path.
	 *
	 * @return string json file path.
	 */
	protected function get_json_file_path() {
		return 'wp-content/wp-rocket-config/dynamic-lists.json';
	}

	/**
	 * Get wpr_dynamic_lists from transient or son file.
	 * Set the transient if it was not set.
	 *
	 * @return string wpr_dynamic_lists.
	 */
	public function get_lists() {
		$wpr_dynamic_lists = get_transient( 'wpr_dynamic_lists' );
		if ( false === $wpr_dynamic_lists ) {
			$wpr_dynamic_lists = $this->get_lists_from_file();
			$this->set_lists_cache( $wpr_dynamic_lists );
		}

		return $wpr_dynamic_lists;
	}

	/**
	 * Get get file system.
	 *
	 * @return \WP_Filesystem_Direct filesystem.
	 */
	private function get_filesystem() {
		if ( empty( $this->filesystem ) ) {
			$this->filesystem = rocket_direct_filesystem();
		}

		return $this->filesystem;
	}

	/**
	 * Get wpr_dynamic_lists from json file.
	 *
	 * @return string wpr_dynamic_lists.
	 */
	private function get_lists_from_file() {
		$content = $this->get_filesystem()->get_contents( $this->get_json_file_path() );
		if ( ! $content ) {
			return null;
		}

		return $content;
	}

	/**
	 * set wpr_dynamic_lists in json file.
	 *
	 * @return bool True if the value was set, false otherwise.
	 */
	protected function put_lists_to_file( string $content ) {
		return $this->filesystem->put_contents( $this->get_json_file_path(), $content, rocket_get_filesystem_perms( 'file' ) );
	}

	/**
	 * set wpr_dynamic_lists transient.
	 *
	 * @return bool True if the value was set, false otherwise.
	 */
	protected function set_lists_cache( string $content ) {
		return set_transient( 'wpr_dynamic_lists', $content, WEEK_IN_SECONDS );
	}
}
