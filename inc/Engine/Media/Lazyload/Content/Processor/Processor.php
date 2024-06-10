<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\Lazyload\Content\Processor;

class Processor {
	private $processor;

	public function set_processor( $processor ) {
		if ( 'dom' === $processor ) {
			$this->processor = new Dom();
		} elseif ( 'regex' === $processor ) {
			$this->processor = new Regex();
		} elseif ( 'simple_html_dom' === $processor ) {
			$this->processor = new SimpleHtmlDom();
		}
	}

	public function get_processor() {
		return $this->processor;
	}
}
