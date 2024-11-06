<?php

namespace WP_Rocket\Engine\Media\Fonts;

abstract class GoogleFontVersion {
	/**
	 * Base URL for the Google Font version.
	 *
	 * @var string
	 */
	protected $url;

	/**
	 * Get the local URL for the Google Font.
	 *
	 * @return string
	 */
	abstract public function get_local_url(): string;

}
