<?php
/**
 * Template Name: lcp_picture
 * Template Description: test template that loads bg image
 */ ?>

<!DOCTYPE html>
<html>
<head>
	<?php wp_head() ?>
	<title>lcp_responsive_bg</title><link rel="preload" as="image" href="https://imagify.rocketlabsqa.ovh/wp-content/uploads/2024/05/home-new-bg-free-img-—-kopia-1024x576.jpg.avif" fetchpriority="high">
</head>
<body>

<div>
	<picture decoding="async">
		<source type="image/avif" srcset="https://imagify.rocketlabsqa.ovh/wp-content/uploads/2024/05/home-new-bg-free-img-—-kopia-1024x576.jpg.avif">
		<source type="image/webp" srcset="https://imagify.rocketlabsqa.ovh/wp-content/uploads/2024/05/home-new-bg-free-img-—-kopia-1024x576.jpg.webp">
		<img decoding="async" src="https://imagify.rocketlabsqa.ovh/wp-content/uploads/2024/05/home-new-bg-free-img-—-kopia-1024x576.jpg">
	</picture>
</div>

</main><!-- #main -->
</div><!-- #primary -->
</div><!-- .wrap -->

<?php wp_footer() ?>
</body>
</html>
