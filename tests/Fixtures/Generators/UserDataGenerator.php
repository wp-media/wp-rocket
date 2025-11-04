<?php

namespace WP_Rocket\Tests\Fixtures\Generators;

class UserDataGenerator {

	protected $ri_sku_active = 'perf-monitor-free';

	protected $expiration = 0;

	protected $promos = [];

	protected $is_reseller = 0;

	protected $custom_limit = null;

	public function with_rocket_insights_expiration( int $expiration ): self {
		$this->expiration = $expiration;
		return $this;
	}

	public function with_custom_limit( int $limit ): self {
		$this->custom_limit = $limit;
		return $this;
	}

	public function with_rocket_insights_active_sku( string $sku ): self {
		$this->ri_sku_active = $sku;
		return $this;
	}

	public function with_reseller_status( int $is_reseller ): self {
		$this->is_reseller = $is_reseller;
		return $this;
	}

	/**
	 * @param string                                 $sku
	 * @param callable(RocketInsightsPromoGenerator) $callback
	 *
	 * @return $this
	 */
	public function with_promo( string $sku, callable $callback ): self {
		$this->promos[ $sku ] = $callback( new RocketInsightsPromoGenerator() );
		return $this;
	}

	public function generate() {
		$free_limit = $this->custom_limit !== null ? (string) $this->custom_limit : '3';

		$plans[] = (object) [
			'sku'        => 'perf-monitor-free',
			'status'     => $this->ri_sku_active == 'perf-monitor-free' ? 'active' : 'inactive',
			'upgrades'   => [
				'perf-monitor-advanced',
			],
			'button'     => (object) [
				'label'  => 'Your plan',
				'action' => 'none',
				'url'    => null,
			],
			'highlights' => [],
			'billing'    => '',
			'promo'      => (object) $this->generate_promo( 'perf-monitor-free' ),
			'limit'      => $free_limit,
		];

		$plans[] = (object) [
			'sku'        => 'perf-monitor-advanced',
			'status'     => $this->ri_sku_active == 'perf-monitor-advanced' ? 'active' : 'inactive',
			'upgrades'   => [],
			'button'     => (object) [
				'label'  => 'Get Advanced',
				'action' => 'purchase',
				'url'    => 'https://wp-rocket.me/express-checkout/?user_id=202331&domain=random.app&product_sku=perf-monitor-advanced&consumer_key=12be53be',
			],
			'highlights' => [
				'Up to 10 pages tracked',
				'Automatic performance monitoring',
				'performance',
			],
			'price'      => '0.00',
			'limit'      => '10',
			'billing'    => '',
			'promo'      => (object) $this->generate_promo( 'perf-monitor-advanced' ),
		];

		return (object) [
			'is_reseller'            => $this->is_reseller,
			'performance_monitoring' => (object) [
				'expiration' => $this->expiration,
				'active_sku' => $this->ri_sku_active,
				'plans'      => $plans,
			],
		];
	}

	protected function generate_promo( string $sku ) {
		return key_exists( $sku, $this->promos ) ?
			$this->promos[ $sku ]->generate()
			:
			[
				'name'        => '',
				'price'       => '',
				'expires_at'  => 0,
				'description' => '',
			];
	}
}
