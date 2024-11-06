<?php
/**
 * Template Name: lcp_single_bg
 * Template Description: test template that loads bg image
 */ ?>

<!DOCTYPE html>
<html>
<head>
	<?php wp_head() ?>
	<title>lcp_single_bg</title><link rel="preload" data-rocket-preload as="image" href="http://example.org/wp-content/rocket-test-data/images/lcp/testavif.avif" fetchpriority="high">
	<style>
		.img-single-css-background-image{
			width: 100%;
			height: 400px;
			background-image: url("/wp-content/rocket-test-data/images/lcp/testavif.avif");
			background-color: #cccccc;
		}
	</style>
</head>
<body>

<div>
	<p  class='img-single-css-background-image'>(img-single-css-background-image)Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi gravida diam in tristique feugiat. In in metus faucibus, condimentum turpis ac, congue mauris. Integer ac varius erat, nec imperdiet odio. Maecenas sit amet dapibus risus. Pellentesque scelerisque cursus lacus a tristique. Maecenas bibendum, erat vitae interdum hendrerit, nibh diam convallis risus, nec sollicitudin est neque eu nunc. Aenean condimentum viverra est a congue. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Donec viverra erat mauris, scelerisque interdum nisl hendrerit eu.</p>
</div>

</main><!-- #main -->
</div><!-- #primary -->
</div><!-- .wrap -->

<?php wp_footer() ?>
</body>
</html>
