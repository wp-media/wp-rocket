<?php

namespace WP_Rocket\Engine\Preload;

/**
 * Extends the background process class for the partial preload background process.
 *
 * @since 3.2
 * @author Remy Perona
 *
 * @see WP_Background_Process
 */
class PartialProcess extends AbstractProcess {

	/**
	 * Specific action identifier for partial preload
	 *
	 * @since 3.2
	 * @var string
	 */
	protected $action = 'partial_preload';

	/**
	 * Preload the URL provided by $item.
	 *
	 * @since  3.2
	 * @since  3.5 $item can be an array.
	 * @author Remy Perona
	 *
	 * @param  array|string $item {
	 *     The item to preload: an array containing the following values.
	 *     A string is allowed for backward compatibility (for the URL).
	 *
	 *     @type string $url    The URL to preload.
	 *     @type bool   $mobile True when we want to send a "mobile" user agent with the request.
	 *     @type string $source An identifier related to the source of the preload (e.g. RELOAD_ID).
	 * }
	 * @return bool False.
	 */
	protected function task( $item ) {
		$this->maybe_preload( $item );
		return false;
	}
}
