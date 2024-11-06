<?php
namespace WP_Rocket\ThirdParty\Hostings;
use HeaderCollector;

//this overrides the header function for that namespace
//it works only if the function is called without the backslash

function headers_sent()
{
	return false;
}

function header($string, $replace)
{
	if (  in_array( $string , HeaderCollector::$headers, true ) ) {
		return;
	}
	HeaderCollector::$headers[] = $string;
}

