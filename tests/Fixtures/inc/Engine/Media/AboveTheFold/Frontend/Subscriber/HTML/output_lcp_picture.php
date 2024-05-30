<?php
/**
 * Template Name: lcp_picture
 * Template Description: test template that loads bg image
 */ ?>

<!DOCTYPE html>
<html>
<head>
	<?php wp_head() ?>
	<title>lcp_responsive_bg</title><link rel="preload" as="image" href="small_cat.jpg" media="(max-width: 400px)"  fetchpriority="high"><link rel="preload" as="image" href="medium_cat.jpg" media="(min-width: 400.1px) and (max-width: 800px)"  fetchpriority="high"><link rel="preload" as="image" href="large_cat.jpg" media="(min-width: 800.1px)" fetchpriority="high">
</head>
<body>

<div>
	<picture>
		<source srcset="small_cat.jpg" media="(max-width: 400px)">
		<source srcset="medium_cat.jpg" media="(max-width: 800px)">
		<img fetchpriority="high" src="large_cat.jpg">
	</picture>
</div>

</main><!-- #main -->
</div><!-- #primary -->
</div><!-- .wrap -->

<?php wp_footer() ?>
</body>
</html>
