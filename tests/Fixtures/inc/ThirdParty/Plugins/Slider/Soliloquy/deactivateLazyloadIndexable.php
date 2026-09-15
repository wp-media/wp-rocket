<?php

return [
	'shouldAddDataNoLazyToEachImageTag' => [
		'config'   => [ 'images' => '<img src="a.jpg"><img src="b.jpg">' ],
		// str_replace('<img', '<img data-no-lazy="1" ', …) leaves a double space
		// before src (replacement's trailing space + the original leading space).
		'expected' => '<img data-no-lazy="1"  src="a.jpg"><img data-no-lazy="1"  src="b.jpg">',
	],
];
