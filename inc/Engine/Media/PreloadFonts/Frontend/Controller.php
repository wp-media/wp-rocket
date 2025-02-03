<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\PreloadFonts\Frontend;

use WP_Rocket\Engine\Common\PerformanceHints\Frontend\ControllerInterface;

class Controller implements ControllerInterface {

	public function optimize(string $html, $row): string {
		if ( ! $row->has_preload_fonts() ) {
			return $html;
		}


	}

	public function add_custom_data(array $data): array {
		return [];
	}
}
