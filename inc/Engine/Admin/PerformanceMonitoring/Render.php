<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\PerformanceMonitoring;

use WP_Rocket\Abstract_Render;

class Render extends Abstract_Render {
	public function render_ui( $items ) {
		echo $this->generate( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			'sections/performance-monitoring',
			[
				'items' => $items,
			]
		);
	}
}
