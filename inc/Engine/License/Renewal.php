<?php

namespace WP_Rocket\Engine\License;

use WP_Rocket\Abstract_Render;
use WP_Rocket\Engine\License\API\Pricing;
use WP_Rocket\Engine\License\API\User;

class Renewal extends Abstract_Render {
    private $pricing;
	private $user;

	public function __construct( Pricing $pricing, User $user, $template_path ) {
		parent::__construct( $template_path );

		$this->pricing = $pricing;
		$this->user    = $user;
	}
}
