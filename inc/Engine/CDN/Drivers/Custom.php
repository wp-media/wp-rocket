<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\CDN\Drivers;

class Custom implements DriverInterface {
	public function should_rewrite_url( $url ) {
		return true;
	}
}
