<?php

namespace WP_Rocket\Engine\CriticalPath;

trait TransientTrait {
	/**
	 * Updates CPCSS running transient with status notices.
	 *
	 * @since 3.6
	 *
	 * @param array       $transient Transient to be updated.
	 * @param string      $item_path Path for processed item.
	 * @param bool        $mobile    If this request is for mobile cpcss.
	 * @param string      $message   CPCSS reply message.
	 * @param bool|string $success   CPCSS success or failure.
	 * @return void
	 */
	private function update_running_transient( $transient, $item_path, $mobile, $message, $success ) {
		$path = ! (bool) $mobile ? $item_path : str_replace( '-mobile.css', '.css', $item_path );

		$transient['items'][ $path ]['status'][ ! (bool) $mobile ? 'nonmobile' : 'mobile' ]['message'] = $message;
		$transient['items'][ $path ]['status'][ ! (bool) $mobile ? 'nonmobile' : 'mobile' ]['success'] = $success;
		set_transient( 'rocket_critical_css_generation_process_running', $transient, HOUR_IN_SECONDS );
	}
}
