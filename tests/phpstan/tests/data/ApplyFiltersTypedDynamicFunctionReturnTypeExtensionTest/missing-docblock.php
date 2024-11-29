<?php

namespace WP_Rocket\Engine\Admin;

use WP_Rocket\ThirdParty\ReturnTypesTrait;

class FilterTest {

	use ReturnTypesTrait;
	public function get_filter_value(string $buffer): string {

		return wpm_apply_filters_typed( 'string', 'rocket_performance_hints_buffer', $buffer );

	}
}
