<?php

namespace WP_Rocket\ThirdParty;

trait ReturnTypesTrait {

	/**
	 * Returns false.
	 *
	 * @since 3.6.1
	 *
	 * @return bool
	 */
	public function return_false() {
		return false;
	}

	/**
	 * Returns true.
	 *
	 * @since 3.6.1
	 *
	 * @return true
	 */
	public function return_true() {
		return true;
	}

	/**
	 * Returns an empty string.
	 *
	 * @since 3.6.1
	 *
	 * @return string Empty string
	 */
	public function return_empty_string() {
		return '';
	}

	/**
	 * Returns an empty array.
	 *
	 * @since 3.6.1
	 *
	 * @return array Empty array.
	 */
	public function return_empty_array() {
		return [];
	}
}
