<?php

namespace WP_Rocket\Engine\Common\JobManager\APIHandler;
class PluginUpdateClient extends AbstractSafeAPIClient
{
	protected function getTransientKey()
	{
		return 'plugin_update';
	}

	protected function getApiUrl()
	{
		return 'https://wp-rocket.me';
	}
}
