<?php
/**
 * Template Name: lcp_bg_responsive_template
 * Template Description: test template that loads bg image
 */ ?>

<!DOCTYPE html>
<html>
<head>
	<?php wp_head() ?>
	<title>lcp_layered_bg</title><link rel="preload" rocket-preload as="image" href="https://fastly.picsum.photos/id/976/200/300.jpg?hmac=s1Uz9fgJv32r8elfaIYn7pLpQXps7X9oYNwC5XirhO8" fetchpriority="high"><link rel="preload" rocket-preload as="image" href="https://rocketlabsqa.ovh/wp-content/rocket-test-data/images/fixtheissue.jpg" fetchpriority="high">
	<style>
		.bg-layared-css-background-images{
			width: 100%;
			height: 400px;
			background-image: url("/wp-content/rocket-test-data/images/lcp/testavif.avif"), url("/wp-content/rocket-test-data/images/lcp/testwebp.webp");
			background-color: #cccccc;
		}

	</style>
</head>
<body>

<div>
	<p  class='bg-layared-css-background-images'>(bg-layared-css-background-images)Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi gravida diam in tristique feugiat. In in metus faucibus, condimentum turpis ac, congue mauris. Integer ac varius erat, nec imperdiet odio. Maecenas sit amet dapibus risus. Pellentesque scelerisque cursus lacus a tristique. Maecenas bibendum, erat vitae interdum hendrerit, nibh diam convallis risus, nec sollicitudin est neque eu nunc. Aenean condimentum viverra est a congue. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Donec viverra erat mauris, scelerisque interdum nisl hendrerit eu.</p>
</div>

</main><!-- #main -->
</div><!-- #primary -->
</div><!-- .wrap -->

<?php wp_footer() ?>
</body>
</html>
