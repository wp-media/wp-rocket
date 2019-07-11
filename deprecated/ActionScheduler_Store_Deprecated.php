<?php

/**
 * Class ActionScheduler_Store_Deprecated
 * @codeCoverageIgnore
 */
abstract class ActionScheduler_Store_Deprecated {

	/**
	 * Get the site's local time.
	 *
	 * @deprecated 2.1.0
	 * @return DateTimeZone
	 */
	protected function get_local_timezone() {
		_deprecated_function( __FUNCTION__, '2.1.0', 'ActionScheduler_TimezoneHelper::set_local_timezone()' );
		return ActionScheduler_TimezoneHelper::get_local_timezone();
	}
}
