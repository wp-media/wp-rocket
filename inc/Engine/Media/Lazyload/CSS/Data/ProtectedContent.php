<?php

namespace WP_Rocket\Engine\Media\Lazyload\CSS\Data;

class ProtectedContent {

	/**
	 * Protected content.
	 *
	 * @var string
	 */
	protected $content = '';

	/**
	 * Mapping between protected files and their placeholder.
	 *
	 * @var array
	 */
	protected $protected_files_mapping = [];

	/**
	 * Set protected content.
	 *
	 * @param string $content Protected content.
	 * @return void
	 */
	public function set_content( string $content ): void {
		$this->content = $content;
	}

	/**
	 * Get protected content.
	 *
	 * @return string
	 */
	public function get_content(): string {
		return $this->content;
	}

	/**
	 * Set mapping between protected files and their placeholder.
	 *
	 * @param array $protected_files_mapping Mapping between protected files and their placeholder.
	 * @return void
	 */
	public function set_protected_files_mapping( array $protected_files_mapping ): void {
		$this->protected_files_mapping = $protected_files_mapping;
	}

	/**
	 * Get mapping between protected files and their placeholder.
	 *
	 * @return array
	 */
	public function get_protected_files_mapping(): array {
		return $this->protected_files_mapping;
	}
}
