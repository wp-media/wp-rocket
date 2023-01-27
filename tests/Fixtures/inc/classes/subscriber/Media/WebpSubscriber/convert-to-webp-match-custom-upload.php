<?php
return [
	[
		'<!DOCTYPE html>
		<html lang="en-US" dir="ltr">
		<head>
			<meta property="og:image" content="https://example.com/wp-content/uploads/2019/09/one-image.png"/>
			<link rel="image_src" href="https://example.com/wp-content/uploads/2019/09/one-image.png" />
			<link rel="apple-touch-icon" href="https://cdn-example.net/wp-content/uploads/2017/02/apple-touch-icon.png" />
			<link rel="icon" type="image/png" href="https://example.com/wp-content/uploads/2017/02/favicon-32x32.png" sizes="32x32" />
			<link rel="icon" type="image/png" href="https://example.com/favicon-64x64.png" sizes="64x64" />
			<meta name="msapplication-TileImage" content="https://example.com/wp-content/uploads/2017/02/mstile-144x144.png" />
		</head>
		<body>
			<img width="200" height="200" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-lazy-src="https://example.com/wp-content/uploads/2019/09/one-image.png" class="attachment-thumbnail portrait wp-post-image" alt="" itemprop="contentUrl" data-lazy-srcset="https://example.com/wp-content/uploads/2019/09/one-image.png 200w, https://example.com/wp-content/uploads/2019/09/one-image-60x60.png 60w" data-lazy-sizes="(max-width: 200px) 100vw, 200px" />
			<noscript>
				<img width="200" height="200" src="https://example.com/wp-content/uploads/2019/09/one-image.png" class="attachment-thumbnail portrait wp-post-image" alt="" itemprop="contentUrl" srcset="https://example.com/wp-content/uploads/2019/09/one-image.png 200w, https://example.com/wp-content/uploads/2019/09/one-image-60x60.png 60w" sizes="(max-width: 200px) 100vw, 200px" />
			</noscript>
			<img src="https://example.com/wp-content/uploads/2017/02/stats-php.gif?idsite=1" alt="" />
		</body>
		</html>',
		'<!DOCTYPE html>
		<html lang="en-US" dir="ltr">
		<head>
			<meta property="og:image" content="https://example.com/wp-content/uploads/2019/09/one-image.png"/>
			<link rel="image_src" href="https://example.com/wp-content/uploads/2019/09/one-image.webp" />
			<link rel="apple-touch-icon" href="https://cdn-example.net/wp-content/uploads/2017/02/apple-touch-icon.png.webp" />
			<link rel="icon" type="image/png" href="https://example.com/wp-content/uploads/2017/02/favicon-32x32.webp" sizes="32x32" />
			<link rel="icon" type="image/png" href="https://example.com/favicon-64x64.png" sizes="64x64" />
			<meta name="msapplication-TileImage" content="https://example.com/wp-content/uploads/2017/02/mstile-144x144.png" />
		</head>
		<body>
			<img width="200" height="200" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-lazy-src="https://example.com/wp-content/uploads/2019/09/one-image.webp" class="attachment-thumbnail portrait wp-post-image" alt="" itemprop="contentUrl" data-lazy-srcset="https://example.com/wp-content/uploads/2019/09/one-image.webp 200w,https://example.com/wp-content/uploads/2019/09/one-image-60x60.png 60w" data-lazy-sizes="(max-width: 200px) 100vw, 200px" />
			<noscript>
				<img width="200" height="200" src="https://example.com/wp-content/uploads/2019/09/one-image.webp" class="attachment-thumbnail portrait wp-post-image" alt="" itemprop="contentUrl" srcset="https://example.com/wp-content/uploads/2019/09/one-image.webp 200w,https://example.com/wp-content/uploads/2019/09/one-image-60x60.png 60w" sizes="(max-width: 200px) 100vw, 200px" />
			</noscript>
			<img src="https://example.com/wp-content/uploads/2017/02/stats-php.gif.webp?idsite=1" alt="" />
		</body>
		</html><!-- Rocket has webp -->',
	],
];
