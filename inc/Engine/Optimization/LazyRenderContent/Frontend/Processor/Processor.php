<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\LazyRenderContent\Frontend\Processor;

class Processor {
	/**
	 * The processor to use.
	 *
	 * @var ProcessorInterface
	 */
	private $processor;

	/**
	 * Set the processor to use.
	 *
	 * @param string $processor The processor to use.
	 *
	 * @return void
	 */
	public function set_processor( $processor ): void {
		switch ( $processor ) {
			case 'dom':
				$this->processor = new Dom();
				break;
			case 'simplehtmldom':
				$this->processor = new SimpleHtmlDom();
				break;
			case 'regex':
				$this->processor = new Regex();
				break;
			default:
				$this->processor = new Dom();
		}
	}

	/**
	 * Get the processor.
	 *
	 * @return ProcessorInterface
	 */
	public function get_processor(): ProcessorInterface {
		return $this->processor;
	}
}
