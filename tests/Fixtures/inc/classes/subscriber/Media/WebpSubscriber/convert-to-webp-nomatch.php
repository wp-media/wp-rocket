<?php
return [
	// No matching attributes.
	[
		'<!DOCTYPE html>
		<html lang="en-US" dir="ltr">
		<head>
			<meta property="og:image" data-url="https://example.com/wp-content/uploads/2019/09/one-image.png"/>
			<link rel="image_src" data-foohref="https://example.com/wp-content/uploads/2019/09/one-image.png" />
			<link rel="apple-touch-icon" data-barhref="https://cdn-example.net/wp-content/uploads/2017/02/apple-touch-icon.png" />
			<link rel="icon" type="image/png" data-newhref="https://example.com/wp-content/uploads/2017/02/favicon-32x32.png" sizes="32x32" />
			<meta name="msapplication-TileImage" data-lazycontent="https://example.com/wp-content/uploads/2017/02/mstile-144x144.png" />
		</head>
		<body>
			<img width="200" height="200" data-ssrc="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-lazysrc="https://example.com/wp-content/uploads/2019/09/one-image.png" class="attachment-thumbnail portrait wp-post-image" alt="" itemprop="contentUrl" data-lazysrcset="https://example.com/wp-content/uploads/2019/09/one-image.png 200w, https://example.com/wp-content/uploads/2019/09/one-image-60x60.png 60w" data-lazysizes="(max-width: 200px) 100vw, 200px" />
			<noscript>
				<img width="200" height="200" data-ssrc="https://example.com/wp-content/uploads/2019/09/one-image.png" class="attachment-thumbnail portrait wp-post-image" alt="" itemprop="contentUrl" data-ssrcset="https://example.com/wp-content/uploads/2019/09/one-image.png 200w, https://example.com/wp-content/uploads/2019/09/one-image-60x60.png 60w" sizes="(max-width: 200px) 100vw, 200px" />
			</noscript>
			<img data-msrc="https://example.com/wp-content/uploads/2017/02/stats-php.gif?idsite=1" alt="" />
		</body>
		</html>',
		'<!DOCTYPE html>
		<html lang="en-US" dir="ltr">
		<head>
			<meta property="og:image" data-url="https://example.com/wp-content/uploads/2019/09/one-image.png"/>
			<link rel="image_src" data-foohref="https://example.com/wp-content/uploads/2019/09/one-image.png" />
			<link rel="apple-touch-icon" data-barhref="https://cdn-example.net/wp-content/uploads/2017/02/apple-touch-icon.png" />
			<link rel="icon" type="image/png" data-newhref="https://example.com/wp-content/uploads/2017/02/favicon-32x32.png" sizes="32x32" />
			<meta name="msapplication-TileImage" data-lazycontent="https://example.com/wp-content/uploads/2017/02/mstile-144x144.png" />
		</head>
		<body>
			<img width="200" height="200" data-ssrc="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-lazysrc="https://example.com/wp-content/uploads/2019/09/one-image.png" class="attachment-thumbnail portrait wp-post-image" alt="" itemprop="contentUrl" data-lazysrcset="https://example.com/wp-content/uploads/2019/09/one-image.png 200w, https://example.com/wp-content/uploads/2019/09/one-image-60x60.png 60w" data-lazysizes="(max-width: 200px) 100vw, 200px" />
			<noscript>
				<img width="200" height="200" data-ssrc="https://example.com/wp-content/uploads/2019/09/one-image.png" class="attachment-thumbnail portrait wp-post-image" alt="" itemprop="contentUrl" data-ssrcset="https://example.com/wp-content/uploads/2019/09/one-image.png 200w, https://example.com/wp-content/uploads/2019/09/one-image-60x60.png 60w" sizes="(max-width: 200px) 100vw, 200px" />
			</noscript>
			<img data-msrc="https://example.com/wp-content/uploads/2017/02/stats-php.gif?idsite=1" alt="" />
		</body>
		</html><!-- Rocket no webp -->',
	],
	// No matching extensions.
	[
		'<!DOCTYPE html>
		<html lang="en-US" dir="ltr">
		<head>
			<meta property="og:image" content="https://example.com/wp-content/uploads/2019/09/one-image.bmp"/>
			<link rel="image_src" href="https://example.com/wp-content/uploads/2019/09/one-image.bmp" />
			<link rel="apple-touch-icon" href="https://cdn-example.net/wp-content/uploads/2017/02/apple-touch-icon.bmp" />
			<link rel="icon" type="image/bmp" href="https://example.com/wp-content/uploads/2017/02/favicon-32x32.bmp" sizes="32x32" />
			<meta name="msapplication-TileImage" content="https://example.com/wp-content/uploads/2017/02/mstile-144x144.bmp" />
		</head>
		<body>
			<img width="200" height="200" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-lazy-src="https://example.com/wp-content/uploads/2019/09/one-image.bmp" class="attachment-thumbnail portrait wp-post-image" alt="" itemprop="contentUrl" data-lazy-srcset="https://example.com/wp-content/uploads/2019/09/one-image.bmp 200w, https://example.com/wp-content/uploads/2019/09/one-image-60x60.bmp 60w" data-lazy-sizes="(max-width: 200px) 100vw, 200px" />
			<noscript>
				<img width="200" height="200" src="https://example.com/wp-content/uploads/2019/09/one-image.bmp" class="attachment-thumbnail portrait wp-post-image" alt="" itemprop="contentUrl" srcset="https://example.com/wp-content/uploads/2019/09/one-image.bmp 200w, https://example.com/wp-content/uploads/2019/09/one-image-60x60.bmp 60w" sizes="(max-width: 200px) 100vw, 200px" />
			</noscript>
			<img src="https://example.com/wp-content/uploads/2017/02/stats-php.bmp?idsite=1" alt="" />
		</body>
		</html>',
		'<!DOCTYPE html>
		<html lang="en-US" dir="ltr">
		<head>
			<meta property="og:image" content="https://example.com/wp-content/uploads/2019/09/one-image.bmp"/>
			<link rel="image_src" href="https://example.com/wp-content/uploads/2019/09/one-image.bmp" />
			<link rel="apple-touch-icon" href="https://cdn-example.net/wp-content/uploads/2017/02/apple-touch-icon.bmp" />
			<link rel="icon" type="image/bmp" href="https://example.com/wp-content/uploads/2017/02/favicon-32x32.bmp" sizes="32x32" />
			<meta name="msapplication-TileImage" content="https://example.com/wp-content/uploads/2017/02/mstile-144x144.bmp" />
		</head>
		<body>
			<img width="200" height="200" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-lazy-src="https://example.com/wp-content/uploads/2019/09/one-image.bmp" class="attachment-thumbnail portrait wp-post-image" alt="" itemprop="contentUrl" data-lazy-srcset="https://example.com/wp-content/uploads/2019/09/one-image.bmp 200w, https://example.com/wp-content/uploads/2019/09/one-image-60x60.bmp 60w" data-lazy-sizes="(max-width: 200px) 100vw, 200px" />
			<noscript>
				<img width="200" height="200" src="https://example.com/wp-content/uploads/2019/09/one-image.bmp" class="attachment-thumbnail portrait wp-post-image" alt="" itemprop="contentUrl" srcset="https://example.com/wp-content/uploads/2019/09/one-image.bmp 200w, https://example.com/wp-content/uploads/2019/09/one-image-60x60.bmp 60w" sizes="(max-width: 200px) 100vw, 200px" />
			</noscript>
			<img src="https://example.com/wp-content/uploads/2017/02/stats-php.bmp?idsite=1" alt="" />
		</body>
		</html><!-- Rocket no webp -->',
	],
	// HTML contains no images.
	[
		'<!DOCTYPE html>
		<html lang="en-US" dir="ltr">
		<head>
		</head>
		<body>
		</body>
		</html>',
		'<!DOCTYPE html>
		<html lang="en-US" dir="ltr">
		<head>
		</head>
		<body>
		</body>
		</html><!-- Rocket no webp -->',
	],
	// Images have empty attributes.
	[
		'<!DOCTYPE html>
		<html lang="en-US" dir="ltr">
		<head>
			<meta property="og:image" content=""/>
			<link rel="image_src" href="" />
			<link rel="apple-touch-icon" href="" />
			<link rel="icon" type="image/png" href="" sizes="32x32" />
			<meta name="msapplication-TileImage" content="" />
		</head>
		<body>
			<img width="200" height="200" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-lazy-src="" class="attachment-thumbnail portrait wp-post-image" alt="" itemprop="contentUrl" data-lazy-srcset="" data-foobar="https://example.com/wp-content/uploads/2019/09/one-image.png" data-lazy-sizes="(max-width: 200px) 100vw, 200px" />
			<noscript>
				<img width="200" height="200" src="" class="attachment-thumbnail portrait wp-post-image" alt="" itemprop="contentUrl" srcset="" sizes="(max-width: 200px) 100vw, 200px" />
			</noscript>
			<img src="" alt="" />
		</body>
		</html>',
		'<!DOCTYPE html>
		<html lang="en-US" dir="ltr">
		<head>
			<meta property="og:image" content=""/>
			<link rel="image_src" href="" />
			<link rel="apple-touch-icon" href="" />
			<link rel="icon" type="image/png" href="" sizes="32x32" />
			<meta name="msapplication-TileImage" content="" />
		</head>
		<body>
			<img width="200" height="200" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-lazy-src="" class="attachment-thumbnail portrait wp-post-image" alt="" itemprop="contentUrl" data-lazy-srcset="" data-foobar="https://example.com/wp-content/uploads/2019/09/one-image.png" data-lazy-sizes="(max-width: 200px) 100vw, 200px" />
			<noscript>
				<img width="200" height="200" src="" class="attachment-thumbnail portrait wp-post-image" alt="" itemprop="contentUrl" srcset="" sizes="(max-width: 200px) 100vw, 200px" />
			</noscript>
			<img src="" alt="" />
		</body>
		</html><!-- Rocket no webp -->',
	],
];
