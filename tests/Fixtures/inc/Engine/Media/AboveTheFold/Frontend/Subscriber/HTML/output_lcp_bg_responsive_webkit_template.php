<?php
/**
 * Template Name: lcp_bg_responsive_template
 * Template Description: test template that loads bg image
 */ ?>

<!DOCTYPE html>
<html>
<head>
	<?php wp_head() ?>
	<title>lcp_bg_responsive_template</title><link rel="preload" as="image" imagesrcset="https://fastly.picsum.photos/id/976/200/300.jpg?hmac=s1Uz9fgJv32r8elfaIYn7pLpQXps7X9oYNwC5XirhO8 1dppx,https://rocketlabsqa.ovh/wp-content/rocket-test-data/images/fixtheissue.jpg 2dppx" fetchpriority="high">
	<style>
		.webkit-BG-images-external-domain {

			padding: 15px;
			background: -webkit-image-set(
				URL("https://fastly.picsum.photos/id/976/200/300.jpg?hmac=s1Uz9fgJv32r8elfaIYn7pLpQXps7X9oYNwC5XirhO8") 1x,
				url("https://rocketlabsqa.ovh/wp-content/rocket-test-data/images/fixtheissue.jpg") 2x);
		}

		.webkit2-BG-images-internal-domain {
			padding: 15px;
			height: 500;
			background-image: -webkit-image-set(
				url("/wp-content/rocket-test-data/image/test3.webp") 1x,
				url("https://new.rocketlabsqa.ovh/wp-content/rocket-test-data/image/test3.webp") 2x);
		}

		.webkit3-BG-images-internal-domain {
			padding: 15px;
			background: -webkit-image-set(
				"/wp-content/rocket-test-data/images/lcp/testwebp.webp" 1x,
				"https://new.rocketlabsqa.ovh/wp-content/rocket-test-data/images/lcp/testavif.avif" 2x);
		}
	</style>
</head>
<body>
<div>
	<p  class='webkit-BG-images-external-domain'> (webkit-BG-images-external-domain)Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi gravida diam in tristique feugiat. In in metus faucibus, condimentum turpis ac, congue mauris. Integer ac varius erat, nec imperdiet odio. Maecenas sit amet dapibus risus. Pellentesque scelerisque cursus lacus a tristique. Maecenas bibendum, erat vitae interdum hendrerit, nibh diam convallis risus, nec sollicitudin est neque eu nunc. Aenean condimentum viverra est a congue. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Donec viverra erat mauris, scelerisque interdum nisl hendrerit eu.</p>
</div>
<div>
	<p  class='webkit2-BG-images-internal-domain'> (webkit2-BG-images-external-domain)Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi gravida diam in tristique feugiat. In in metus faucibus, condimentum turpis ac, congue mauris. Integer ac varius erat, nec imperdiet odio. Maecenas sit amet dapibus risus. Pellentesque scelerisque cursus lacus a tristique. Maecenas bibendum, erat vitae interdum hendrerit, nibh diam convallis risus, nec sollicitudin est neque eu nunc. Aenean condimentum viverra est a congue. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Donec viverra erat mauris, scelerisque interdum nisl hendrerit eu.</p>
</div>

<div>
	<p  class='webkit3-BG-images-internal-domain'> (webkit3-BG-images-external-domain)Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi gravida diam in tristique feugiat. In in metus faucibus, condimentum turpis ac, congue mauris. Integer ac varius erat, nec imperdiet odio. Maecenas sit amet dapibus risus. Pellentesque scelerisque cursus lacus a tristique. Maecenas bibendum, erat vitae interdum hendrerit, nibh diam convallis risus, nec sollicitudin est neque eu nunc. Aenean condimentum viverra est a congue. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Donec viverra erat mauris, scelerisque interdum nisl hendrerit eu.</p>
</div>

</main><!-- #main -->
</div><!-- #primary -->
</div><!-- .wrap -->

<?php wp_footer() ?>
</body>
</html>
