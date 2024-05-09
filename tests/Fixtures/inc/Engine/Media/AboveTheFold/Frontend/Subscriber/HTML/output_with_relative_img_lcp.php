<?php
/**
 * Template Name: lcp_responsive_bg
 * Template Description: test template that loads bg image
 */ ?>

<!DOCTYPE html>
<html>
<head>
	<?php wp_head() ?>
	<title>Relative test</title><link rel="preload" as="image" href="http://example.org/wp-content/uploads/sample_relative_image.jpg" fetchpriority="high">
</head>
<body>

	<img fetchpriority="high" src="/wp-content/uploads/sample_relative_image.jpg" alt="Relative url">


<?php wp_footer() ?>
</body>
</html>
