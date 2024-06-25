<?php
namespace WP_Rocket\Engine\Common\JobManager\APIHandler;
class PluginInformationClient extends AbstractSafeAPIClient
{
	protected function getTransientKey()
	{
		return 'plugin_information';
	}

	protected function getApiUrl()
	{
		return 'https://wp-rocket.me';
	}
}
