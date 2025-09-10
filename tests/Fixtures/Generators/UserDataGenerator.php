<?php

namespace WP_Rocket\Tests\Fixtures\Generators;


class UserDataGenerator {

	protected $pma_sku_active ='perf-monitor-free';

	protected $expiration = 0;

	public function with_pma_expiration(int $expiration): self {
		$this->expiration = $expiration;
		return $this;
	}

	public function with_pma_active_sku(string $sku): self {
		$this->pma_sku_active = $sku;
		return $this;
	}

	public function generate() {

		$plans[] = (object) [
			"sku" => "perf-monitor-free",
			"status" => $this->pma_sku_active == "perf-monitor-free" ? "active" : "inactive",
			"upgrades" => [
				"perf-monitor-advanced"
			],
			"button" => (object) [
				"label" => "Your plan",
				"action" => "none",
				"url" => null,
			]
		];

		$plans[] = (object) [
			"sku" => "perf-monitor-advanced",
			"status" => $this->pma_sku_active == "perf-monitor-advanced" ? "active" : "inactive",
			"upgrades" => [],
			"button" => (object) [
				"label" => "Get Advanced",
				"action" => "purchase",
				"url" => "https://wp-rocket.me/express-checkout/?user_id=202331&domain=random.app&product_sku=perf-monitor-advanced&consumer_key=12be53be",
			],
			"price" => "0.00"
		];

		return (object)[
			'performance_monitoring' => (object) [
				"expiration" => $this->expiration,
				"active_sku" => $this->pma_sku_active,
				"plans" => $plans,
			]
		];
	}
}
