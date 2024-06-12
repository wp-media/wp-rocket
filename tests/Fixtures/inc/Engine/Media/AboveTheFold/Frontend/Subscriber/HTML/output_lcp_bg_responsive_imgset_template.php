<?php
/**
 * Template Name: lcp_bg_responsive_template
 * Template Description: test template that loads bg image
 */ ?>

<!DOCTYPE html>
<html>
<head>
	<?php wp_head() ?>
	<title>lcp_bg_responsive_template</title><link rel="preload" as="image" imagesrcset="http://example.org/wp-content/rocket-test-data/images/lcp/testavif.avif 1dppx,http://example.org/wp-content/rocket-test-data/images/lcp/testwebp.webp 2dppx" fetchpriority="high">
	<style>
		.imgset-css-background-images{
			width: 100%;
			height: 400px;
			background-image:image-set(
				url("/wp-content/rocket-test-data/images/lcp/testavif.avif") 1x,
				url("/wp-content/rocket-test-data/images/lcp/testwebp.webp") 2x);
			background-color: #cccccc;
		}
		.imgsetwithwebkit-css-background-images{
			width: 100%;
			height: 400px;
			background-image: url("/wp-content/rocket-test-data/images/lcp/testjpg.jpg");
			background-image: -webkit-image-set(
				url("/wp-content/rocket-test-data/images/lcp/testjpeg.jpeg") 1x,
				url("/wp-content/rocket-test-data/images/lcp/testPng.png") 2x);
			background-image: image-set(
				URL("https://fastly.picsum.photos/id/976/200/300.jpg?hmac=s1Uz9fgJv32r8elfaIYn7pLpQXps7X9oYNwC5XirhO8") 1x,
				url("https://rocketlabsqa.ovh/wp-content/rocket-test-data/images/fixtheissue.jpg") 2x);
		}

	</style>
</head>
<body>

<div>
	<p  class='imgset-css-background-images'>(img-set-css-background-images)Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi gravida diam in tristique feugiat. In in metus faucibus, condimentum turpis ac, congue mauris. Integer ac varius erat, nec imperdiet odio. Maecenas sit amet dapibus risus. Pellentesque scelerisque cursus lacus a tristique. Maecenas bibendum, erat vitae interdum hendrerit, nibh diam convallis risus, nec sollicitudin est neque eu nunc. Aenean condimentum viverra est a congue. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Donec viverra erat mauris, scelerisque interdum nisl hendrerit eu.</p>
</div>

<div>
	<p  class='imgsetwithwebkit-css-background-images'>(img-set-css-background-images)Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi gravida diam in tristique feugiat. In in metus faucibus, condimentum turpis ac, congue mauris. Integer ac varius erat, nec imperdiet odio. Maecenas sit amet dapibus risus. Pellentesque scelerisque cursus lacus a tristique. Maecenas bibendum, erat vitae interdum hendrerit, nibh diam convallis risus, nec sollicitudin est neque eu nunc. Aenean condimentum viverra est a congue. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Donec viverra erat mauris, scelerisque interdum nisl hendrerit eu.</p>
</div>
</main><!-- #main -->
</div><!-- #primary -->
</div><!-- .wrap -->

<?php wp_footer() ?>
</body>
</html>
