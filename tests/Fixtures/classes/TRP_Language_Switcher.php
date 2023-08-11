<?php

class TRP_Language_Switcher {
	public function add_flag( $code, $name ) {
		return '<img class="trp-flag-image" src="http://example.org/wp-content/translatepress-multilingual/assets/images/flags/' . $code . '.png" width="18" height="12" alt="' . $code . '" title="' . $name . '">';
	}
}
