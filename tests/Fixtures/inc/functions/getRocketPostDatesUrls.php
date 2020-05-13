<?php

return [
	[
		[
			'post_title'   => 'Lorem ipsum',
			'post_content' => 'Lorem ipsum dolor sit amet',
			'post_status'  => 'publish',
			'post_date'    => '2020-03-01',
		],
		[
			'http://example.org/2020/index.html',
			'http://example.org/2020/index.html_gzip',
			'http://example.org/2020/page',
			'http://example.org/2020/03/index.html',
			'http://example.org/2020/03/index.html_gzip',
			'http://example.org/2020/03/page',
			'http://example.org/2020/03/01/',
		],
	],
	[
		[
			'post_title'   => 'Nec ullamcorper',
			'post_content' => 'Nec ullamcorper sit amet risus nullam eget.',
			'post_status'  => 'publish',
			'post_date'    => '2019-10-24',
		],
		[
			'http://example.org/2019/index.html',
			'http://example.org/2019/index.html_gzip',
			'http://example.org/2019/page',
			'http://example.org/2019/10/index.html',
			'http://example.org/2019/10/index.html_gzip',
			'http://example.org/2019/10/page',
			'http://example.org/2019/10/24/',
		],
	],
	[
		[
			'post_title'   => 'Enim nunc faucibus',
			'post_content' => 'Enim nunc faucibus a pellentesque sit amet porttitor eget.',
			'post_status'  => 'draft',
			'post_date'    => '2012-05-13',
		],
		[
			'http://example.org/2012/index.html',
			'http://example.org/2012/index.html_gzip',
			'http://example.org/2012/page',
			'http://example.org/2012/05/index.html',
			'http://example.org/2012/05/index.html_gzip',
			'http://example.org/2012/05/page',
			'http://example.org/2012/05/13/',
		],
	],
	[
		[
			'post_title'   => 'Semper viverra nam libero justo',
			'post_content' => 'Semper viverra nam libero justo. Blandit cursus risus at ultrices mi tempus imperdiet nulla.',
			'post_status'  => 'pending',
			'post_type'    => 'page',
			'post_date'    => '2022-06-30',
		],
		[
			'http://example.org/2022/index.html',
			'http://example.org/2022/index.html_gzip',
			'http://example.org/2022/page',
			'http://example.org/2022/06/index.html',
			'http://example.org/2022/06/index.html_gzip',
			'http://example.org/2022/06/page',
			'http://example.org/2022/06/30/',
		],
	],
];
