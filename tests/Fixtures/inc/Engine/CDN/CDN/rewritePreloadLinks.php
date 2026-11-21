<?php

$single_cname = [
	'cdn'             => 1,
	'cnames'          => [ 'https://cdn.example.org' ],
	'zones'           => [ 'all' ],
	'relative_paths'  => true,
	'reject_files'    => [],
	'backtrack_limit' => null,
];

$tag = '<link rel="preload" data-rocket-preload as="image" ';
$end = ' fetchpriority="high">';

return [
	'shouldRewriteHrefAndSrcsetForImgSrcset'          => [
		'config'   => $single_cname,
		'html'     => '<title>t</title>' . $tag . 'href="http://example.org/wp-content/uploads/a-1024.jpg" imagesrcset="http://example.org/wp-content/uploads/a-300.jpg 300w, http://example.org/wp-content/uploads/a-1024.jpg 1024w" imagesizes="100vw"' . $end . '<img src="x">',
		'expected' => '<title>t</title>' . $tag . 'href="https://cdn.example.org/wp-content/uploads/a-1024.jpg" imagesrcset="https://cdn.example.org/wp-content/uploads/a-300.jpg 300w, https://cdn.example.org/wp-content/uploads/a-1024.jpg 1024w" imagesizes="100vw"' . $end . '<img src="x">',
	],
	'shouldRewriteHrefForImg'                         => [
		'config'   => $single_cname,
		'html'     => $tag . 'href="http://example.org/wp-content/uploads/a.jpg"' . $end,
		'expected' => $tag . 'href="https://cdn.example.org/wp-content/uploads/a.jpg"' . $end,
	],
	'shouldRewriteCommaJoinedBgImgSet'                => [
		'config'   => $single_cname,
		'html'     => $tag . 'imagesrcset="http://example.org/wp-content/uploads/a.jpg,http://example.org/wp-content/uploads/b.jpg"' . $end,
		'expected' => $tag . 'imagesrcset="https://cdn.example.org/wp-content/uploads/a.jpg,https://cdn.example.org/wp-content/uploads/b.jpg"' . $end,
	],
	'shouldRewritePictureLinkWithMediaAndSizes'       => [
		'config'   => $single_cname,
		'html'     => $tag . 'media="(max-width: 600px)" imagesrcset="http://example.org/wp-content/uploads/p-600.webp 600w, http://example.org/wp-content/uploads/p-300.webp 300w" imagesizes="(max-width: 600px) 100vw, 600px"' . $end,
		'expected' => $tag . 'media="(max-width: 600px)" imagesrcset="https://cdn.example.org/wp-content/uploads/p-600.webp 600w, https://cdn.example.org/wp-content/uploads/p-300.webp 300w" imagesizes="(max-width: 600px) 100vw, 600px"' . $end,
	],
	'shouldKeepDescriptorOfPictureHrefWithImagesZone' => [
		'config'   => array_merge( $single_cname, [ 'zones' => [ 'images' ] ] ),
		'html'     => $tag . 'href="http://example.org/wp-content/uploads/a.jpg 2x"' . $end,
		'expected' => $tag . 'href="https://cdn.example.org/wp-content/uploads/a.jpg 2x"' . $end,
	],
	'shouldKeepEntitiesInHref'                        => [
		'config'   => $single_cname,
		'html'     => $tag . 'href="http://example.org/wp-content/uploads/a.jpg?w=1&#038;h=2"' . $end,
		'expected' => $tag . 'href="https://cdn.example.org/wp-content/uploads/a.jpg?w=1&#038;h=2"' . $end,
	],
	'shouldKeepCommasInsideAbsoluteUrlQuery'          => [
		'config'   => $single_cname,
		'html'     => $tag . 'imagesrcset="http://example.org/wp-content/uploads/a.jpg?x=1,2 300w, http://example.org/wp-content/uploads/b.jpg 600w"' . $end,
		'expected' => $tag . 'imagesrcset="https://cdn.example.org/wp-content/uploads/a.jpg?x=1,2 300w, https://cdn.example.org/wp-content/uploads/b.jpg 600w"' . $end,
	],
	'shouldPickSameCnameAsBodyMarkup'                 => [
		'config'   => array_merge(
			$single_cname,
			[
				'cnames' => [ 'https://cdn1.example.org', 'https://cdn2.example.org' ],
				'zones'  => [ 'all', 'all' ],
			]
		),
		// crc32 modulo 2 gives index 1 for a-300.jpg and index 0 for a-1024.jpg, as rewrite() does.
		'html'     => $tag . 'href="http://example.org/wp-content/uploads/a-1024.jpg 2x" imagesrcset="http://example.org/wp-content/uploads/a-300.jpg 300w, http://example.org/wp-content/uploads/a-1024.jpg 1024w"' . $end,
		'expected' => $tag . 'href="https://cdn1.example.org/wp-content/uploads/a-1024.jpg 2x" imagesrcset="https://cdn2.example.org/wp-content/uploads/a-300.jpg 300w, https://cdn1.example.org/wp-content/uploads/a-1024.jpg 1024w"' . $end,
	],
	'shouldNotTouchFontPreload'                       => [
		'config'   => $single_cname,
		'html'     => '<link rel="preload" data-rocket-preload as="font" href="http://example.org/wp-content/uploads/a.woff2">',
		'expected' => '<link rel="preload" data-rocket-preload as="font" href="http://example.org/wp-content/uploads/a.woff2">',
	],
	'shouldNotTouchLinkWithoutPreloadMarker'          => [
		'config'   => $single_cname,
		'html'     => '<link rel="preload" as="image" href="http://example.org/wp-content/uploads/a.jpg">',
		'expected' => '<link rel="preload" as="image" href="http://example.org/wp-content/uploads/a.jpg">',
	],
	'shouldNotRewriteWhenCdnDisabled'                 => [
		'config'   => array_merge( $single_cname, [ 'cdn' => 0 ] ),
		'html'     => $tag . 'href="http://example.org/wp-content/uploads/a.jpg" imagesrcset="http://example.org/wp-content/uploads/a.jpg 300w"' . $end,
		'expected' => $tag . 'href="http://example.org/wp-content/uploads/a.jpg" imagesrcset="http://example.org/wp-content/uploads/a.jpg 300w"' . $end,
	],
	'shouldEndOnCdnWhenHrefIsAlreadyOnCdnAndSrcsetOnOrigin' => [
		'config'   => $single_cname,
		'html'     => $tag . 'href="https://cdn.example.org/wp-content/uploads/a.jpg" imagesrcset="http://example.org/wp-content/uploads/a.jpg 300w"' . $end,
		'expected' => $tag . 'href="https://cdn.example.org/wp-content/uploads/a.jpg" imagesrcset="https://cdn.example.org/wp-content/uploads/a.jpg 300w"' . $end,
	],
	'shouldNotRewriteHrefOutsideAllowedPaths'         => [
		'config'   => $single_cname,
		'html'     => $tag . 'href="http://example.org/other/a.jpg"' . $end,
		'expected' => $tag . 'href="http://example.org/other/a.jpg"' . $end,
	],
	'shouldRewriteSrcsetCandidateOutsideAllowedPathsLikeBodySrcset' => [
		'config'   => $single_cname,
		'html'     => $tag . 'imagesrcset="http://example.org/other/a.jpg 300w, /images/b.jpg 600w"' . $end,
		'expected' => $tag . 'imagesrcset="https://cdn.example.org/other/a.jpg 300w, https://cdn.example.org/images/b.jpg 600w"' . $end,
	],
	'shouldNotRewriteSrcsetCandidatesOnOtherHosts'    => [
		'config'   => $single_cname,
		'html'     => $tag . 'imagesrcset="//cdn.example.org/wp-content/uploads/a.jpg 300w, https://other.com/b.jpg 600w, http://example.org/wp-content/uploads/c.jpg 900w"' . $end,
		'expected' => $tag . 'imagesrcset="//cdn.example.org/wp-content/uploads/a.jpg 300w, https://other.com/b.jpg 600w, https://cdn.example.org/wp-content/uploads/c.jpg 900w"' . $end,
	],
	'shouldKeepUnrewritableCandidatesOnOriginIndependently' => [
		'config'   => array_merge( $single_cname, [ 'reject_files' => [ '/wp-content/uploads/b.jpg' ] ] ),
		'html'     => $tag . 'imagesrcset="http://example.org/wp-content/uploads/a.jpg 300w, http://example.org/wp-content/uploads/b.jpg 600w"' . $end,
		'expected' => $tag . 'imagesrcset="https://cdn.example.org/wp-content/uploads/a.jpg 300w, http://example.org/wp-content/uploads/b.jpg 600w"' . $end,
	],
	'shouldNotTouchDataUriCandidate'                  => [
		'config'   => $single_cname,
		'html'     => $tag . 'imagesrcset="data:image/png;base64,AAAA 1x, http://example.org/wp-content/uploads/a.jpg 2x"' . $end,
		'expected' => $tag . 'imagesrcset="data:image/png;base64,AAAA 1x, https://cdn.example.org/wp-content/uploads/a.jpg 2x"' . $end,
	],
	'shouldRewriteRelativeHrefAndSrcset'              => [
		'config'   => $single_cname,
		'html'     => $tag . 'href="/wp-content/uploads/a.jpg" imagesrcset="/wp-content/uploads/a.jpg 300w"' . $end,
		'expected' => $tag . 'href="https://cdn.example.org/wp-content/uploads/a.jpg" imagesrcset="https://cdn.example.org/wp-content/uploads/a.jpg 300w"' . $end,
	],
	'shouldNotRewriteRelativeHrefWhenRelativePathsDisabled' => [
		'config'   => array_merge( $single_cname, [ 'relative_paths' => false ] ),
		'html'     => $tag . 'href="/wp-content/uploads/a.jpg"' . $end,
		'expected' => $tag . 'href="/wp-content/uploads/a.jpg"' . $end,
	],
	'shouldRewriteEveryPreloadTag'                    => [
		'config'   => $single_cname,
		'html'     => $tag . 'href="http://example.org/wp-content/uploads/a.jpg"' . $end . $tag . 'href="http://example.org/wp-content/uploads/b.jpg"' . $end,
		'expected' => $tag . 'href="https://cdn.example.org/wp-content/uploads/a.jpg"' . $end . $tag . 'href="https://cdn.example.org/wp-content/uploads/b.jpg"' . $end,
	],
	'shouldNotPrefixSrcsetCandidatesAlreadyOnPathPrefixedHomeHostCname' => [
		'config'   => array_merge( $single_cname, [ 'cnames' => [ 'http://example.org/cdn' ] ] ),
		'html'     => $tag . 'imagesrcset="http://example.org/cdn/wp-content/uploads/a.jpg 300w, http://example.org/wp-content/uploads/b.jpg 600w"' . $end,
		'expected' => $tag . 'imagesrcset="http://example.org/cdn/wp-content/uploads/a.jpg 300w, http://example.org/cdn/wp-content/uploads/b.jpg 600w"' . $end,
	],
	'shouldReturnOriginalHtmlWhenRegexFails'          => [
		'config'   => array_merge( $single_cname, [ 'backtrack_limit' => 1 ] ),
		'html'     => str_repeat( '<div class="x">', 200 ) . $tag . 'href="http://example.org/wp-content/uploads/a.jpg"' . $end,
		'expected' => str_repeat( '<div class="x">', 200 ) . $tag . 'href="http://example.org/wp-content/uploads/a.jpg"' . $end,
	],
];
