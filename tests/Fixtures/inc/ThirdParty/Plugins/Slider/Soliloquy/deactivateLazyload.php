<?php

return [
	'shouldAppendDataNoLazyToAttributes' => [
		'config'   => [ 'attr' => 'class="soliloquy-image"' ],
		'expected' => 'class="soliloquy-image" data-no-lazy="1" ',
	],
];
