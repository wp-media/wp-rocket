<?php

namespace WP_Rocket\Engine\Admin\API;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;

class ServiceProvider extends AbstractServiceProvider
{

	protected $provides = [
		'admin_api_subscriber',
	];

    /**
     * @inheritDoc
     */
    public function register()
    {
		$this->getContainer()->add('admin_api_subscriber', Subscriber::class);
    }
}
