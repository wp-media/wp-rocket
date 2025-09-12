<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\PerformanceMonitoring;

use WP_Rocket\Abstract_Render;

class Render extends Abstract_Render {
	/**
	 * Render the ui part from views.
	 *
	 * @param array $items Items from database.
	 *
	 * @return void
	 */
	public function render_ui( array $items ) {
		$data = compact( 'items' );
		echo $this->generate( 'sections/performance-monitoring', $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Render the settings section from views.
	 *
	 * @param array $data Data to render the settings section.
	 *
	 * @return void
	 */
	public function render_settings_section( array $data ) {
		echo $this->generate( 'partials/performance-monitoring/settings', $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Render the license banner section from views.
	 *
	 * @param array $data Data to render the license banner section.
	 *
	 * @return void
	 */
	public function render_license_banner_section( array $data ) {
		echo $this->generate( 'partials/performance-monitoring/license-banner', $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	public function render_license_banner_plan_price( string $price, string $currency = "$", string $period = "month") {
		$dot = get_locale() === 'en_US' ? '.' : ',';
		$price = number_format_i18n($price, 2);
		$price = explode($dot, $price);
		$data = [
			'price_number' => $price[0],
			'price_decimal' => $dot.$price[1],
			'currency' => $currency,
			'period' => $period,
		];
		echo $this->generate( 'partials/performance-monitoring/license-banner-plan-price', $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
