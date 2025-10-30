<?php

namespace WP_Rocket\Tests\Fixtures\Generators;

class RocketInsightsPromoGenerator {
	protected $expires_at = 0;

	protected $name = '';

	protected $description = '';

	protected $price = '';

	public function with_expires_at(int $expires_at): self {
		$this->expires_at = $expires_at;
		return $this;
	}

	public function with_name(string $name): self {
		$this->name = $name;
		return $this;
	}

	public function with_description(string $description): self {
		$this->description = $description;
		return $this;
	}
	public function with_price(string $price): self {
		$this->price = $price;
		return $this;
	}

	public function generate() {
		return [
			'name' => $this->name,
			'description' => $this->description,
			'price' => $this->price,
			'expires_at' => $this->expires_at,
			'billing' => '',
		];
	}
}
