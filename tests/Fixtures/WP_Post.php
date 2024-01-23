<?php

if (!class_exists('WP_Post')) {
	class WP_Post
	{
		public $ID;
		public function __construct(stdClass $stdClass) {
			$data = (array) $stdClass;
			foreach ($data as $key => $value) {
				$this->{$key} = $value;
			}
		}
	}
}
