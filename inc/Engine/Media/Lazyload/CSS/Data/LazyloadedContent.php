<?php

namespace WP_Rocket\Engine\Media\Lazyload\CSS\Data;

class LazyloadedContent {

	/**
	 * URls extracted from the content.
	 *
	 * @var array
	 */
	protected $urls = [];

	/**
	 * Lazyloaded content.
	 *
	 * @var string
	 */
	protected $content = '';

	/**
	 * Get URls extracted from the content.
	 *
	 * @return array
	 */
	public function get_urls(): array {
		return $this->urls;
	}

	/**
	 * Set URls extracted from the content.
	 *
	 * @param array $urls URls extracted from the content.
	 * @return void
	 */
	public function set_urls( array $urls ): void {
		$this->urls = $urls;
	}

	/**
	 * Get lazyloaded content.
	 *
	 * @return string
	 */
	public function get_content(): string {
		return $this->content;
	}

	/**
	 * Set lazyloaded content.
	 *
	 * @param string $content Lazyloaded content.
	 * @return void
	 */
	public function set_content( string $content ): void {
		$this->content = $content;
	}
}
