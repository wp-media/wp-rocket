<?php

class Imagify_Partner {
	public static function has_imagify_api_key() {
		return rocket_get_constant( 'test_api_key' );
	}
}
