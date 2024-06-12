<?php
/**
 * Template Name: lcp_picture
 * Template Description: test template that loads bg image
 */ ?>

<!DOCTYPE html>
<html>
<head>
	<?php wp_head() ?>
	<title>lcp_responsive_bg</title>
</head>
<body>

<div>
	<picture>
		<source srcset="small_cat.jpg" media="(max-width: 400px)">
		<source srcset="medium_cat.jpg" media="(max-width: 800px)">
		<img src="large_cat.jpg">
	</picture>
</div>

</main><!-- #main -->
</div><!-- #primary -->
</div><!-- .wrap -->

<?php wp_footer() ?>
</body>
</html>
