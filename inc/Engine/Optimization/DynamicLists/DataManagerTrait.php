<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\DynamicLists;

trait DataManagerTrait {
	/**
	 * Filesystem instance
	 *
	 * @var WP_Filesystem_Direct
	 */
	protected $filesystem;

	/**
	 * Gets the filesystem instance
	 *
	 * @return WP_Filesystem_Direct
	 */
	private function get_filesystem() {
		return ! is_null( $this->filesystem ) ? $this->filesystem : rocket_direct_filesystem();
	}

	/**
	 * Gets the path to the dynamic lists JSON file
	 *
	 * @return string
	 */
	private function get_json_filepath(): string {
		return rocket_get_constant( 'WP_ROCKET_CONFIG_PATH', '' ) . 'dynamic-lists.json';
	}

	/**
	 * Gets the lists content
	 *
	 * @return string
	 */
	protected function get_lists() {
		$transient = get_transient( 'wpr_dynamic_lists' );

		if ( false !== $transient ) {
			return $transient;
		}

		$json = $this->get_lists_from_file();

		$lists = json_decode( $json );

		if ( empty( $lists ) ) {
			return '';
		}

		$this->set_lists_cache( $lists );

		return $lists;
	}

	/**
	 * Gets lists JSON content from file
	 *
	 * @return string
	 */
	private function get_lists_from_file(): string {
		$content        = '';
		$lists_filepath = $this->get_json_filepath();

		if ( $this->get_filesystem()->exists( $lists_filepath ) ) {
			$content = $this->get_filesystem()->get_contents( $lists_filepath );
		}

		if ( ! empty( $content ) ) {
			return $content;
		}

		$fallback_filepath = rocket_get_constant( 'WP_ROCKET_PATH', '' ) . 'dynamic-lists.json';

		if ( $this->get_filesystem()->exists( $fallback_filepath ) ) {
			$content = $this->get_filesystem()->get_contents( $fallback_filepath );
		}

		if ( ! empty( $content ) ) {
			$this->put_lists_to_file( $content );

			return $content;
		}

		return $content;
	}

	/**
	 * Write lists content to JSON file
	 *
	 * @param string $content JSON content.
	 *
	 * @return bool
	 */
	protected function put_lists_to_file( string $content ): bool {
		return $this->get_filesystem()->put_contents( $this->get_json_filepath(), $content );
	}

	/**
	 * Sets transient for lists content
	 *
	 * @param string $content Lists content.
	 *
	 * @return bool
	 */
	private function set_lists_cache( string $content ): bool {
		return set_transient( 'wpr_dynamic_lists', $content, WEEK_IN_SECONDS );
	}
}
