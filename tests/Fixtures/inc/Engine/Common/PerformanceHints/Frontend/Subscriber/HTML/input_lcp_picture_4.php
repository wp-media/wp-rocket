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
	<picture class="core-image">
		<source type="image/webp" media='(max-width: 500px)' srcset="https://variance.pl/wp-content/uploads/2024/05/Kwiatowy-Ksiezyc-400x600.webp">
		<source type="image/webp" media='(min-width: 501px) and (max-width: 768px)' srcset="https://variance.pl/wp-content/uploads/2024/05/Kwiatowy-Ksiezyc-768x513.webp">
		<img class="skip-lazy" src="https://variance.pl/wp-content/uploads/2024/05/Kwiatowy-Ksiezyc-1348x900.webp" alt="Kwiatowy księżyc" title="Kwiatowy księżyc">
	</picture>
</div>

</main><!-- #main -->
</div><!-- #primary -->
</div><!-- .wrap -->

<?php wp_footer() ?>
</body>
</html>
