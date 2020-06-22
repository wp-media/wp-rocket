<?php
return [
	'test_data' => [
		'shouldBailOutWhenNoOptimize' => [
			'config' => [
				'constants' => [
					'DONOTROCKETOPTIMIZE' => true,
					'DONOTASYNCCSS'       => false
				],
				'options' => [
					'async_css' => true
				],
				'exclude_options' => [
					'async_css' => false
				],
				'html' => '<!doctype html><html lang="en-US"><head><meta charset="UTF-8" /><link rel="stylesheet" type="text/css" href="file1.css" media="all"><link rel="stylesheet" type="text/css" href="file2.css" media="print"><link media="all" type="text/css" rel="stylesheet" href="file3.css"></head><body>Content here</body></html>'
			],
			'expected' => [
				'html' => '<!doctype html><html lang="en-US"><head><meta charset="UTF-8" /><link rel="stylesheet" type="text/css" href="file1.css" media="all"><link rel="stylesheet" type="text/css" href="file2.css" media="print"><link media="all" type="text/css" rel="stylesheet" href="file3.css"></head><body>Content here</body></html>'
			]
		],

		'shouldBailOutWhenNoAsyncCssConstant' => [
			'config' => [
				'constants' => [
					'DONOTROCKETOPTIMIZE' => false,
					'DONOTASYNCCSS'       => true
				],
				'options' => [
					'async_css' => true
				],
				'exclude_options' => [
					'async_css' => false
				],
				'html' => '<!doctype html><html lang="en-US"><head><meta charset="UTF-8" /><link rel="stylesheet" type="text/css" href="file1.css" media="all"><link rel="stylesheet" type="text/css" href="file2.css" media="print"><link media="all" type="text/css" rel="stylesheet" href="file3.css"></head><body>Content here</body></html>'
			],
			'expected' => [
				'html' => '<!doctype html><html lang="en-US"><head><meta charset="UTF-8" /><link rel="stylesheet" type="text/css" href="file1.css" media="all"><link rel="stylesheet" type="text/css" href="file2.css" media="print"><link media="all" type="text/css" rel="stylesheet" href="file3.css"></head><body>Content here</body></html>'
			]
		],

		'shouldBailOutWhenNoAsyncCssOption' => [
			'config' => [
				'constants' => [
					'DONOTROCKETOPTIMIZE' => false,
					'DONOTASYNCCSS'       => false
				],
				'options' => [
					'async_css' => false
				],
				'exclude_options' => [
					'async_css' => false
				],
				'html' => '<!doctype html><html lang="en-US"><head><meta charset="UTF-8" /><link rel="stylesheet" type="text/css" href="file1.css" media="all"><link rel="stylesheet" type="text/css" href="file2.css" media="print"><link media="all" type="text/css" rel="stylesheet" href="file3.css"></head><body>Content here</body></html>'
			],
			'expected' => [
				'html' => '<!doctype html><html lang="en-US"><head><meta charset="UTF-8" /><link rel="stylesheet" type="text/css" href="file1.css" media="all"><link rel="stylesheet" type="text/css" href="file2.css" media="print"><link media="all" type="text/css" rel="stylesheet" href="file3.css"></head><body>Content here</body></html>'
			]
		],

		'shouldBailOutWhenExcludeAsyncCss' => [
			'config' => [
				'constants' => [
					'DONOTROCKETOPTIMIZE' => false,
					'DONOTASYNCCSS'       => false
				],
				'options' => [
					'async_css' => true
				],
				'exclude_options' => [
					'async_css' => true
				],
				'html' => '<!doctype html><html lang="en-US"><head><meta charset="UTF-8" /><link rel="stylesheet" type="text/css" href="file1.css" media="all"><link rel="stylesheet" type="text/css" href="file2.css" media="print"><link media="all" type="text/css" rel="stylesheet" href="file3.css"></head><body>Content here</body></html>'
			],
			'expected' => [
				'html' => '<!doctype html><html lang="en-US"><head><meta charset="UTF-8" /><link rel="stylesheet" type="text/css" href="file1.css" media="all"><link rel="stylesheet" type="text/css" href="file2.css" media="print"><link media="all" type="text/css" rel="stylesheet" href="file3.css"></head><body>Content here</body></html>'
			]
		],

		'shouldDeferCssFilesWithMediaAll' => [
			'config' => [
				'constants' => [
					'DONOTROCKETOPTIMIZE' => false,
					'DONOTASYNCCSS'       => false
				],
				'options' => [
					'async_css' => true
				],
				'exclude_options' => [
					'async_css' => false
				],
				'html' => '<!doctype html><html lang="en-US"><head><meta charset="UTF-8" /><link rel="stylesheet" type="text/css" href="http://www.example.com/file1.css" media="all"></head><body>Content here</body></html>'
			],
			'expected' => [
				'html' => '<!doctype html><html lang="en-US"><head><meta charset="UTF-8" /><link rel="stylesheet"  as="style" type="text/css" onload="this.media=\'all\'" href="http://www.example.com/file1.css" media="print"></head><body>Content here<noscript><link rel="stylesheet" type="text/css" href="http://www.example.com/file1.css" media="all"></noscript></body></html>'
			]
		],

		'shouldDeferCssFilesWithMediaAllRelativeUrl' => [
			'config' => [
				'constants' => [
					'DONOTROCKETOPTIMIZE' => false,
					'DONOTASYNCCSS'       => false
				],
				'options' => [
					'async_css' => true
				],
				'exclude_options' => [
					'async_css' => false
				],
				'html' => '<!doctype html><html lang="en-US"><head><meta charset="UTF-8" /><link rel="stylesheet" type="text/css" href="//www.example.com/file1.css" media="all"></head><body>Content here</body></html>'
			],
			'expected' => [
				'html' => '<!doctype html><html lang="en-US"><head><meta charset="UTF-8" /><link rel="stylesheet"  as="style" type="text/css" onload="this.media=\'all\'" href="//www.example.com/file1.css" media="print"></head><body>Content here<noscript><link rel="stylesheet" type="text/css" href="//www.example.com/file1.css" media="all"></noscript></body></html>'
			]
		],

		'shouldDeferCssFilesWithMediaAllAndExcludedCssFiles' => [
			'config' => [
				'constants' => [
					'DONOTROCKETOPTIMIZE' => false,
					'DONOTASYNCCSS'       => false
				],
				'options' => [
					'async_css' => true
				],
				'exclude_options' => [
					'async_css' => false
				],
				'exclude_css_files' => [
					'http://www.example.com/file.css' => '/file.css'
				],
				'html' => '<!doctype html><html lang="en-US"><head><meta charset="UTF-8" /><link rel="stylesheet" type="text/css" href="http://www.example.com/file.css" media="all"></head><body>Content here</body></html>'
			],
			'expected' => [
				'html' => '<!doctype html><html lang="en-US"><head><meta charset="UTF-8" /><link rel="stylesheet" type="text/css" href="http://www.example.com/file.css" media="all"></head><body>Content here</body></html>'
			]
		],

		'shouldDeferCssFilesWithMediaPrint' => [
			'config' => [
				'constants' => [
					'DONOTROCKETOPTIMIZE' => false,
					'DONOTASYNCCSS'       => false
				],
				'options' => [
					'async_css' => true
				],
				'exclude_options' => [
					'async_css' => false
				],
				'html' => '<!doctype html><html lang="en-US"><head><meta charset="UTF-8" /><link rel="stylesheet" type="text/css" href="http://www.example.com/file1.css" media="print"></head><body>Content here</body></html>'
			],
			'expected' => [
				'html' => '<!doctype html><html lang="en-US"><head><meta charset="UTF-8" /><link rel="stylesheet"  as="style" type="text/css" onload="this.media=\'print\'" href="http://www.example.com/file1.css" media="print"></head><body>Content here<noscript><link rel="stylesheet" type="text/css" href="http://www.example.com/file1.css" media="print"></noscript></body></html>'
			]
		],

		'shouldDeferCssFilesWithOnloadExists' => [
			'config' => [
				'constants' => [
					'DONOTROCKETOPTIMIZE' => false,
					'DONOTASYNCCSS'       => false
				],
				'options' => [
					'async_css' => true
				],
				'exclude_options' => [
					'async_css' => false
				],
				'html' => '<!doctype html><html lang="en-US"><head><meta charset="UTF-8" /><link onload="alert(\'Hi\')" rel="stylesheet" type="text/css" href="http://www.example.com/file1.css" media="print"></head><body>Content here</body></html>'
			],
			'expected' => [
				'html' => '<!doctype html><html lang="en-US"><head><meta charset="UTF-8" /><link onload="this.media=\'print\'" rel="stylesheet"  as="style" type="text/css" href="http://www.example.com/file1.css" media="print"></head><body>Content here<noscript><link onload="alert(\'Hi\')" rel="stylesheet" type="text/css" href="http://www.example.com/file1.css" media="print"></noscript></body></html>'
			]
		],
		'shouldDeferCssFilesWithOnloadExistsAndQuotes' => [
			'config' => [
				'constants' => [
					'DONOTROCKETOPTIMIZE' => false,
					'DONOTASYNCCSS'       => false
				],
				'options' => [
					'async_css' => true
				],
				'exclude_options' => [
					'async_css' => false
				],
				'html' => '<!doctype html><html lang="en-US"><head><meta charset="UTF-8" /><link onload=\'alert(\'Hi\')\' rel="stylesheet" type="text/css" href="http://www.example.com/file1.css" media="print"></head><body>Content here</body></html>'
			],
			'expected' => [
				'html' => '<!doctype html><html lang="en-US"><head><meta charset="UTF-8" /><link onload="this.media=\'print\'" rel="stylesheet"  as="style" type="text/css" href="http://www.example.com/file1.css" media="print"></head><body>Content here<noscript><link onload=\'alert(\'Hi\')\' rel="stylesheet" type="text/css" href="http://www.example.com/file1.css" media="print"></noscript></body></html>'
			]
		],

		'shouldDeferCssFilesWithOnloadExistsAtTheEnd' => [
			'config' => [
				'constants' => [
					'DONOTROCKETOPTIMIZE' => false,
					'DONOTASYNCCSS'       => false
				],
				'options' => [
					'async_css' => true
				],
				'exclude_options' => [
					'async_css' => false
				],
				'html' => '<!doctype html><html lang="en-US"><head><meta charset="UTF-8" /><link rel="stylesheet" type="text/css" href="http://www.example.com/file1.css" media="print" onload="alert(\'Hi\')"></head><body>Content here</body></html>'
			],
			'expected' => [
				'html' => '<!doctype html><html lang="en-US"><head><meta charset="UTF-8" /><link rel="stylesheet"  as="style" type="text/css" href="http://www.example.com/file1.css" media="print" onload="this.media=\'print\'"></head><body>Content here<noscript><link rel="stylesheet" type="text/css" href="http://www.example.com/file1.css" media="print" onload="alert(\'Hi\')"></noscript></body></html>'
			]
		],
		'shouldDeferCssFilesWithOnloadExistsAndQuotesAtTheEnd' => [
			'config' => [
				'constants' => [
					'DONOTROCKETOPTIMIZE' => false,
					'DONOTASYNCCSS'       => false
				],
				'options' => [
					'async_css' => true
				],
				'exclude_options' => [
					'async_css' => false
				],
				'html' => '<!doctype html><html lang="en-US"><head><meta charset="UTF-8" /><link rel="stylesheet" type="text/css" href="http://www.example.com/file1.css" media="print" onload=\'alert(\'Hi\')\'></head><body>Content here</body></html>'
			],
			'expected' => [
				'html' => '<!doctype html><html lang="en-US"><head><meta charset="UTF-8" /><link rel="stylesheet"  as="style" type="text/css" href="http://www.example.com/file1.css" media="print" onload="this.media=\'print\'"></head><body>Content here<noscript><link rel="stylesheet" type="text/css" href="http://www.example.com/file1.css" media="print" onload=\'alert(\'Hi\')\'></noscript></body></html>'
			]
		],
		'shouldDeferCssFilesWithOnloadExistsAndQuotesAtTheEndMultipleFiles' => [
			'config' => [
				'constants' => [
					'DONOTROCKETOPTIMIZE' => false,
					'DONOTASYNCCSS'       => false
				],
				'options' => [
					'async_css' => true
				],
				'exclude_options' => [
					'async_css' => false
				],
				'html' => '<!doctype html><html lang="en-US"><head><meta charset="UTF-8" /><link onload="alert(\'Hi\')" rel="stylesheet" type="text/css" href="http://www.example.com/file2.css" media="print"><link rel="stylesheet" type="text/css" href="//www.example.com/file3.css" media="all"><link rel="stylesheet" type="text/css" href="http://www.example.com/file1.css" media="print" onload=\'alert(\'Hi\')\'></head><body>Content here</body></html>'
			],
			'expected' => [
				'html' => '<!doctype html><html lang="en-US"><head><meta charset="UTF-8" /><link onload="this.media=\'print\'" rel="stylesheet"  as="style" type="text/css" href="http://www.example.com/file2.css" media="print"><link rel="stylesheet"  as="style" type="text/css" onload="this.media=\'all\'" href="//www.example.com/file3.css" media="print"><link rel="stylesheet"  as="style" type="text/css" href="http://www.example.com/file1.css" media="print" onload="this.media=\'print\'"></head><body>Content here<noscript><link onload="alert(\'Hi\')" rel="stylesheet" type="text/css" href="http://www.example.com/file2.css" media="print"></noscript><noscript><link rel="stylesheet" type="text/css" href="//www.example.com/file3.css" media="all"></noscript><noscript><link rel="stylesheet" type="text/css" href="http://www.example.com/file1.css" media="print" onload=\'alert(\'Hi\')\'></noscript></body></html>'
			]
		],
	]
];
