<?php

return [
	'original' => '<img class="size-full wp-image-906 aligncenter" title="Image Alignment 580x300" alt="Image Alignment 580x300" src="http://example.org/wp-content/uploads/2013/03/image-alignment-580x300.jpg" width="580" height="300" />
	<img class="size-full wp-image-904 alignleft" title="Image Alignment 150x150" alt="Image Alignment 150x150" src="http://example.org/wp-content/uploads/2013/03/image-alignment-150x150.jpg" width="150" height="150" />
	<img class="alignnone  wp-image-907" title="Image Alignment 1200x400" alt="Image Alignment 1200x400" src="http://example.org/wp-content/uploads/2013/03/image-alignment-1200x4002.jpg" width="1200" height="400" />
	<img class="size-full wp-image-905 alignright" title="Image Alignment 300x200" alt="Image Alignment 300x200" src="http://example.org/wp-content/uploads/2013/03/image-alignment-300x200.jpg" width="300" height="200" />
	<figure id="attachment_906" aria-describedby="caption-attachment-906" style="width: 580px" class="wp-caption aligncenter"><img class="size-full wp-image-906  " title="Image Alignment 580x300" alt="Image Alignment 580x300" src="http://example.org/wp-content/uploads/2013/03/image-alignment-580x300.jpg" width="580" height="300" /><figcaption id="caption-attachment-906" class="wp-caption-text">Look at 580&#215;300 getting some <a title="Image Settings" href="http://en.support.wordpress.com/images/image-settings/">caption</a> love.</figcaption></figure>
	<figure id="attachment_904" aria-describedby="caption-attachment-904" style="width: 150px" class="wp-caption alignleft"><img class="size-full wp-image-904  " title="Image Alignment 150x150" alt="Image Alignment 150x150" src="http://example.org/wp-content/uploads/2013/03/image-alignment-150x150.jpg" width="150" height="150" /><figcaption id="caption-attachment-904" class="wp-caption-text">Itty-bitty caption.</figcaption></figure>
	<figure id="attachment_907" aria-describedby="caption-attachment-907" style="width: 1200px" class="wp-caption alignnone"><img class=" wp-image-907" title="Image Alignment 1200x400" alt="Image Alignment 1200x400" src="http://example.org/wp-content/uploads/2013/03/image-alignment-1200x4002.jpg" width="1200" height="400" /><figcaption id="caption-attachment-907" class="wp-caption-text">Massive image comment for your eyeballs.</figcaption></figure>
	<figure id="attachment_905" aria-describedby="caption-attachment-905" style="width: 300px" class="wp-caption alignright"><img class="size-full wp-image-905 " title="Image Alignment 300x200" alt="Image Alignment 300x200" src="http://example.org/wp-content/uploads/2013/03/image-alignment-300x200.jpg" width="300" height="200" /><figcaption id="caption-attachment-905" class="wp-caption-text">Feels good to be right all the time.</figcaption></figure>
	<figure class="post-thumbnail">
		<img width="1568" height="1046" src="http://example.org/wp-content/uploads/2018/03/sticker-mule-189122-unsplash-1568x1046.jpg" class="attachment-post-thumbnail size-post-thumbnail wp-post-image" alt="" srcset="http://example.org/wp-content/uploads/2018/03/sticker-mule-189122-unsplash-1568x1046.jpg 1568w, http://example.org/wp-content/uploads/2018/03/sticker-mule-189122-unsplash-300x200.jpg 300w, http://example.org/wp-content/uploads/2018/03/sticker-mule-189122-unsplash-768x513.jpg 768w, http://example.org/wp-content/uploads/2018/03/sticker-mule-189122-unsplash-1024x683.jpg 1024w" sizes="(max-width: 1568px) 100vw, 1568px" />
	</figure>
	<figure class="post-thumbnail">
		<img width="1568" height="1046" src="http://example.org/wp-content/uploads/2018/03/sticker-mule-189122-unsplash-1568x1046.jpg" class="attachment-post-thumbnail size-post-thumbnail wp-post-image" alt="" srcset="http://example.org/wp-content/uploads/2018/03/sticker-mule-189122-unsplash-1568x1046.jpg 1568w, http://example.org/wp-content/uploads/2018/03/sticker-mule-189122-unsplash-300x200.jpg 300w, http://example.org/wp-content/uploads/2018/03/sticker-mule-189122-unsplash-768x513.jpg 768w, http://example.org/wp-content/uploads/2018/03/sticker-mule-189122-unsplash-1024x683.jpg 1024w, http://example.org/wp-content/uploads/2018/03/sticker-mule-189122-unsplash-1568x1046.jpg 1568w" sizes="(max-width: 1568px) 100vw, 1568px" />
	</figure>
	<picture>
		<source srcset="/images/site/logo/logo-acommeassure.webp" type="image/webp">
		<source srcset="/images/site/logo/logo-acommeassure.png 100w" type="image/png">
		<img
			width="300"
			height="60"
			class="logoSite show-for-large"
			src="/images/site/logo/logo-acommeassure.png"
			title="Accueil AcommeAssure"
			alt="logo dacommeassure"
		/>
	</picture>
	<img src="/wp-content/themes/test/img/test.png" loading="lazy" width="900" srcset="/wp-content/themes/test/img/test-2.png 500w, /wp-content/themes/test/img/test-2.png 800w" sizes="(max-width: 767px) 37vw, (max-width: 991px) 34vw, 37vw" alt="">
	<img src="/wp-content/rocket-test-data/images/test3.gif" loading="lazy" srcset="/wp-content/rocket-test-data/images/test1.jpeg, https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQlriCLaxuV9pFTwhP2tpXTbdT5v5-uhwes6A&usqp=CAU" sizes="(max-width: 767px) 37vw, (max-width: 991px) 34vw, 37vw" alt="Absolute&rel-scrset-images">',
	'expected' => '<img class="size-full wp-image-906 aligncenter" title="Image Alignment 580x300" alt="Image Alignment 580x300" src="http://example.org/wp-content/uploads/2013/03/image-alignment-580x300.jpg" width="580" height="300" />
	<img class="size-full wp-image-904 alignleft" title="Image Alignment 150x150" alt="Image Alignment 150x150" src="http://example.org/wp-content/uploads/2013/03/image-alignment-150x150.jpg" width="150" height="150" />
	<img class="alignnone  wp-image-907" title="Image Alignment 1200x400" alt="Image Alignment 1200x400" src="http://example.org/wp-content/uploads/2013/03/image-alignment-1200x4002.jpg" width="1200" height="400" />
	<img class="size-full wp-image-905 alignright" title="Image Alignment 300x200" alt="Image Alignment 300x200" src="http://example.org/wp-content/uploads/2013/03/image-alignment-300x200.jpg" width="300" height="200" />
	<figure id="attachment_906" aria-describedby="caption-attachment-906" style="width: 580px" class="wp-caption aligncenter"><img class="size-full wp-image-906  " title="Image Alignment 580x300" alt="Image Alignment 580x300" src="http://example.org/wp-content/uploads/2013/03/image-alignment-580x300.jpg" width="580" height="300" /><figcaption id="caption-attachment-906" class="wp-caption-text">Look at 580&#215;300 getting some <a title="Image Settings" href="http://en.support.wordpress.com/images/image-settings/">caption</a> love.</figcaption></figure>
	<figure id="attachment_904" aria-describedby="caption-attachment-904" style="width: 150px" class="wp-caption alignleft"><img class="size-full wp-image-904  " title="Image Alignment 150x150" alt="Image Alignment 150x150" src="http://example.org/wp-content/uploads/2013/03/image-alignment-150x150.jpg" width="150" height="150" /><figcaption id="caption-attachment-904" class="wp-caption-text">Itty-bitty caption.</figcaption></figure>
	<figure id="attachment_907" aria-describedby="caption-attachment-907" style="width: 1200px" class="wp-caption alignnone"><img class=" wp-image-907" title="Image Alignment 1200x400" alt="Image Alignment 1200x400" src="http://example.org/wp-content/uploads/2013/03/image-alignment-1200x4002.jpg" width="1200" height="400" /><figcaption id="caption-attachment-907" class="wp-caption-text">Massive image comment for your eyeballs.</figcaption></figure>
	<figure id="attachment_905" aria-describedby="caption-attachment-905" style="width: 300px" class="wp-caption alignright"><img class="size-full wp-image-905 " title="Image Alignment 300x200" alt="Image Alignment 300x200" src="http://example.org/wp-content/uploads/2013/03/image-alignment-300x200.jpg" width="300" height="200" /><figcaption id="caption-attachment-905" class="wp-caption-text">Feels good to be right all the time.</figcaption></figure>
	<figure class="post-thumbnail">
		<img width="1568" height="1046" src="http://example.org/wp-content/uploads/2018/03/sticker-mule-189122-unsplash-1568x1046.jpg" class="attachment-post-thumbnail size-post-thumbnail wp-post-image" alt="" srcset="http://cdn.example.org/wp-content/uploads/2018/03/sticker-mule-189122-unsplash-1568x1046.jpg 1568w, http://cdn.example.org/wp-content/uploads/2018/03/sticker-mule-189122-unsplash-300x200.jpg 300w, http://cdn.example.org/wp-content/uploads/2018/03/sticker-mule-189122-unsplash-768x513.jpg 768w, http://cdn.example.org/wp-content/uploads/2018/03/sticker-mule-189122-unsplash-1024x683.jpg 1024w" sizes="(max-width: 1568px) 100vw, 1568px" />
	</figure>
	<figure class="post-thumbnail">
		<img width="1568" height="1046" src="http://example.org/wp-content/uploads/2018/03/sticker-mule-189122-unsplash-1568x1046.jpg" class="attachment-post-thumbnail size-post-thumbnail wp-post-image" alt="" srcset="http://cdn.example.org/wp-content/uploads/2018/03/sticker-mule-189122-unsplash-1568x1046.jpg 1568w, http://cdn.example.org/wp-content/uploads/2018/03/sticker-mule-189122-unsplash-300x200.jpg 300w, http://cdn.example.org/wp-content/uploads/2018/03/sticker-mule-189122-unsplash-768x513.jpg 768w, http://cdn.example.org/wp-content/uploads/2018/03/sticker-mule-189122-unsplash-1024x683.jpg 1024w, http://cdn.example.org/wp-content/uploads/2018/03/sticker-mule-189122-unsplash-1568x1046.jpg 1568w" sizes="(max-width: 1568px) 100vw, 1568px" />
	</figure>
	<picture>
		<source srcset="http://cdn.example.org/images/site/logo/logo-acommeassure.webp" type="image/webp">
		<source srcset="http://cdn.example.org/images/site/logo/logo-acommeassure.png 100w" type="image/png">
		<img
			width="300"
			height="60"
			class="logoSite show-for-large"
			src="/images/site/logo/logo-acommeassure.png"
			title="Accueil AcommeAssure"
			alt="logo dacommeassure"
		/>
	</picture>
	<img src="/wp-content/themes/test/img/test.png" loading="lazy" width="900" srcset="http://cdn.example.org/wp-content/themes/test/img/test-2.png 500w, http://cdn.example.org/wp-content/themes/test/img/test-2.png 800w" sizes="(max-width: 767px) 37vw, (max-width: 991px) 34vw, 37vw" alt="">
	<img src="/wp-content/rocket-test-data/images/test3.gif" loading="lazy" srcset="http://cdn.example.org/wp-content/rocket-test-data/images/test1.jpeg, https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQlriCLaxuV9pFTwhP2tpXTbdT5v5-uhwes6A&usqp=CAU" sizes="(max-width: 767px) 37vw, (max-width: 991px) 34vw, 37vw" alt="Absolute&rel-scrset-images">',
	'test_data' => [
		'testShouldRewriteSrcsetWithCDN' => [
			'options'  => [
				'cdn' => [
					'default' => 0,
					'value' => 1,
				],
				'cdn_cnames' => [
					'default' => [],
					'value' => [
						'cdn.example.org',
					],
				],
				'cdn_reject_files' => [
					'default' => [],
					'value' => [],
				],
				'cdn_zone' => [
					'default' => [],
					'value' => [
						'all',
					],
				],
			],
		],
		'testShouldRewriteSrcsetWithCDNWhenZoneIsImages' => [
			'options'  => [
				'cdn' => [
					'default' => 0,
					'value' => 1,
				],
				'cdn_cnames' => [
					'default' => [],
					'value' => [
						'cdn.example.org',
					],
				],
				'cdn_reject_files' => [
					'default' => [],
					'value' => [],
				],
				'cdn_zone' => [
					'default' => [],
					'value' => [
						'images',
					],
				],
			],
		],
	],
];
