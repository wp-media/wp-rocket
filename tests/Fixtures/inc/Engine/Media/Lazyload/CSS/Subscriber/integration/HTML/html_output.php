<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>HTML 5 Boilerplate</title>
	<!-- external CSS background images here min and non-min file -->
	<link rel="stylesheet" href="http://example.org/wp-content/cache/wp-rocket/background-css//wp-content/rocket-test-data/styles/lazyload_css_background_images.css?test=1">
	<link rel="stylesheet" href="https://new.rocketlabsqa.ovh/wp-content/rocket-test-data/styles/lazyload_css_background_images.min.css">

	<!-- CSS loaded by js, probably won't be processed -->
	<script>
		var style = document.createElement('style');
		style.innerHTML = `
        .javascript-background-image {
            width: 100%;
            height: 400px;
            background-image: url('/wp-content/rocket-test-data/images/paper.jpeg');
            background-repeat: no-repeat;
            background-size: cover;
        }
        `;
		document.head.appendChild(style);
	</script>


<style id="wpr-lazyload-bg"></style>
<noscript>
<style id="wpr-lazyload-bg-nostyle">:root{--wpr-bg-hash: url("https://new.rocketlabsqa.ovh/wp-content/rocket-test-data/images/paper.jpeg");}:root{--wpr-bg-hash: url('/wp-content/rocket-test-data/images/test.png');}:root{--wpr-bg-hash: url( "/wp-content/rocket-test-data/images/paper.jpeg" );}:root{--wpr-bg-hash: url(/wp-content/rocket-test-data/images/paper.jpeg);}:root{--wpr-bg-hash: url('/test.png');}:root{--wpr-bg-hash: url('https://upload.wikimedia.org/wikipedia/commons/1/11/Test-Logo.svg');}:root{--wpr-bg-hash: url("/wp-content/rocket-test-data/images/paper.jpeg");}:root{--wpr-bg-hash: url('https://new.rocketlabsqa.ovh/wp-content/rocket-test-data/images/test.png');}:root{--wpr-bg-hash: url( "/wp-content/rocket-test-data/images/paper.jpeg" );}:root{--wpr-bg-hash: url("/wp-content/rocket-test-data/images/paper.jpeg");}:root{--wpr-bg-hash: url('/wp-content/rocket-test-data/images/test.png');}:root{--wpr-bg-hash: url('/wp-content/rocket-test-data/images/testnotExist.png');}:root{--wpr-bg-hash: url(/wp-content/rocket-test-data/images/butterfly.avif);}:root{--wpr-bg-hash: url(/wp-content/rocket-test-data/images/file_example_TIFF_1MB.tiff);}</style>
</noscript>
<script type="application/javascript">const rocket_pairs = [{"selector":".external-css-background-image","style":":root{--wpr-bg-hash: url(\"https:\/\/new.rocketlabsqa.ovh\/wp-content\/rocket-test-data\/images\/paper.jpeg\");}"},{"selector":".external-css-background-images","style":":root{--wpr-bg-hash: url('\/wp-content\/rocket-test-data\/images\/test.png');}"},{"selector":".external-css-background-images","style":":root{--wpr-bg-hash: url( \"\/wp-content\/rocket-test-data\/images\/paper.jpeg\" );}"},{"selector":".external-css-background-image-gradient","style":":root{--wpr-bg-hash: url(\/wp-content\/rocket-test-data\/images\/paper.jpeg);}"},{"selector":".external-css-background","style":":root{--wpr-bg-hash: url('\/test.png');}"},{"selector":".external-css-backgroundsvg","style":":root{--wpr-bg-hash: url('https:\/\/upload.wikimedia.org\/wikipedia\/commons\/1\/11\/Test-Logo.svg');}"},{"selector":".internal-css-background-image","style":":root{--wpr-bg-hash: url(\"\/wp-content\/rocket-test-data\/images\/paper.jpeg\");}"},{"selector":".internal-css-background-images","style":":root{--wpr-bg-hash: url('https:\/\/new.rocketlabsqa.ovh\/wp-content\/rocket-test-data\/images\/test.png');}"},{"selector":".internal-css-background-images","style":":root{--wpr-bg-hash: url( \"\/wp-content\/rocket-test-data\/images\/paper.jpeg\" );}"},{"selector":".internal-css-background-image-gradient","style":":root{--wpr-bg-hash: url(\"\/wp-content\/rocket-test-data\/images\/paper.jpeg\");}"},{"selector":".internal-css-background","style":":root{--wpr-bg-hash: url('\/wp-content\/rocket-test-data\/images\/test.png');}"},{"selector":".internal-css-background404","style":":root{--wpr-bg-hash: url('\/wp-content\/rocket-test-data\/images\/testnotExist.png');}"},{"selector":"#internal-BG-images","style":":root{--wpr-bg-hash: url(\/wp-content\/rocket-test-data\/images\/butterfly.avif);}"},{"selector":"#internal-BG-images","style":":root{--wpr-bg-hash: url(\/wp-content\/rocket-test-data\/images\/file_example_TIFF_1MB.tiff);}"}];</script></head>
<body>
<div>
	<h2>Background images from internal CSS</h2>
</div>
<div class='internal-css-background-image background-cover'>
	<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi gravida diam in tristique feugiat. In in metus faucibus, condimentum turpis ac, congue mauris. Integer ac varius erat, nec imperdiet odio. Maecenas sit amet dapibus risus. Pellentesque scelerisque cursus lacus a tristique. Maecenas bibendum, erat vitae interdum hendrerit, nibh diam convallis risus, nec sollicitudin est neque eu nunc. Aenean condimentum viverra est a congue. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Donec viverra erat mauris, scelerisque interdum nisl hendrerit eu.</p>
</div>
<div>
	<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris ut nulla ut lorem fringilla molestie ut non urna. Ut accumsan luctus lacinia. Praesent eleifend dolor leo. Ut erat risus, laoreet ac mi nec, varius efficitur sapien. In vitae sapien placerat, vestibulum ligula ac, vestibulum mi. Nulla eget scelerisque nisl. Aenean eu metus velit. Aliquam vel iaculis neque.</p>
	<p>Suspendisse potenti. Morbi laoreet odio in nunc iaculis, fermentum lobortis risus pharetra. Morbi scelerisque, libero at accumsan fermentum, purus felis blandit urna, consectetur porta sapien magna at augue. Praesent pulvinar velit mi, at consequat dui dictum ac. Pellentesque varius augue lorem, quis fringilla nisl gravida ac. Sed ac urna id enim gravida blandit. Sed cursus ligula sem, et imperdiet erat convallis a.</p>
	<p>Nullam rhoncus justo nibh, in finibus eros vehicula eget. Phasellus elementum nibh ut ipsum congue, id iaculis quam venenatis. Ut in nibh sit amet mi tincidunt efficitur bibendum at sapien. Duis euismod tempus ipsum efficitur condimentum. In hac habitasse platea dictumst. Fusce vestibulum, elit eget varius lacinia, velit tortor rutrum felis, sed scelerisque nunc tortor a est. Phasellus aliquam magna felis, id placerat ex varius vel.</p>
	<p>Morbi cursus sapien velit, posuere commodo libero congue nec. Interdum et malesuada fames ac ante ipsum primis in faucibus. Ut lorem arcu, interdum maximus lacus laoreet, euismod placerat nibh. Donec ac mauris eros. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Suspendisse potenti. Interdum et malesuada fames ac ante ipsum primis in faucibus. Cras eu mi non sem egestas pharetra.</p>
	<p>Etiam scelerisque placerat ex, ac efficitur lacus. Etiam vitae pharetra elit, non venenatis ante. Maecenas iaculis venenatis iaculis. Interdum et malesuada fames ac ante ipsum primis in faucibus. Quisque a ultricies enim, at efficitur metus. Donec quis consectetur leo. Duis tincidunt quis dolor ac condimentum. Nulla a augue id massa laoreet suscipit. Donec at leo efficitur, aliquet massa vel, tincidunt dolor. Sed vulputate purus vel hendrerit egestas. Donec in tempor eros, ut consectetur libero. Integer volutpat, nulla at ullamcorper ornare, massa ante ultricies tortor, a dignissim diam justo vitae leo.</p>
</div>
<div>
	<p  class='internal-css-background-images background-no-repeat'>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi gravida diam in tristique feugiat. In in metus faucibus, condimentum turpis ac, congue mauris. Integer ac varius erat, nec imperdiet odio. Maecenas sit amet dapibus risus. Pellentesque scelerisque cursus lacus a tristique. Maecenas bibendum, erat vitae interdum hendrerit, nibh diam convallis risus, nec sollicitudin est neque eu nunc. Aenean condimentum viverra est a congue. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Donec viverra erat mauris, scelerisque interdum nisl hendrerit eu.</p>
</div>
<div>
	<p  class='internal-css-background-image_gradient'>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi gravida diam in tristique feugiat. In in metus faucibus, condimentum turpis ac, congue mauris. Integer ac varius erat, nec imperdiet odio. Maecenas sit amet dapibus risus. Pellentesque scelerisque cursus lacus a tristique. Maecenas bibendum, erat vitae interdum hendrerit, nibh diam convallis risus, nec sollicitudin est neque eu nunc. Aenean condimentum viverra est a congue. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Donec viverra erat mauris, scelerisque interdum nisl hendrerit eu.</p>
</div>
<div class='internal-css-background-image background-no-repeat'>
	<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi gravida diam in tristique feugiat. In in metus faucibus, condimentum turpis ac, congue mauris. Integer ac varius erat, nec imperdiet odio. Maecenas sit amet dapibus risus. Pellentesque scelerisque cursus lacus a tristique. Maecenas bibendum, erat vitae interdum hendrerit, nibh diam convallis risus, nec sollicitudin est neque eu nunc. Aenean condimentum viverra est a congue. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Donec viverra erat mauris, scelerisque interdum nisl hendrerit eu.</p>
</div>
<div>
	<p  class='internal-css-background-images background-no-repeat'>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi gravida diam in tristique feugiat. In in metus faucibus, condimentum turpis ac, congue mauris. Integer ac varius erat, nec imperdiet odio. Maecenas sit amet dapibus risus. Pellentesque scelerisque cursus lacus a tristique. Maecenas bibendum, erat vitae interdum hendrerit, nibh diam convallis risus, nec sollicitudin est neque eu nunc. Aenean condimentum viverra est a congue. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Donec viverra erat mauris, scelerisque interdum nisl hendrerit eu.</p>
</div>
<div>
	<p  class='internal-css-background-image-gradient'>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi gravida diam in tristique feugiat. In in metus faucibus, condimentum turpis ac, congue mauris. Integer ac varius erat, nec imperdiet odio. Maecenas sit amet dapibus risus. Pellentesque scelerisque cursus lacus a tristique. Maecenas bibendum, erat vitae interdum hendrerit, nibh diam convallis risus, nec sollicitudin est neque eu nunc. Aenean condimentum viverra est a congue. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Donec viverra erat mauris, scelerisque interdum nisl hendrerit eu.</p>
</div>
<div>
	<p  class='internal-css-background'>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi gravida diam in tristique feugiat. In in metus faucibus, condimentum turpis ac, congue mauris. Integer ac varius erat, nec imperdiet odio. Maecenas sit amet dapibus risus. Pellentesque scelerisque cursus lacus a tristique. Maecenas bibendum, erat vitae interdum hendrerit, nibh diam convallis risus, nec sollicitudin est neque eu nunc. Aenean condimentum viverra est a congue. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Donec viverra erat mauris, scelerisque interdum nisl hendrerit eu.</p>
</div>

<div>
	<p  class='internal-css-background404'>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi gravida diam in tristique feugiat. In in metus faucibus, condimentum turpis ac, congue mauris. Integer ac varius erat, nec imperdiet odio. Maecenas sit amet dapibus risus. Pellentesque scelerisque cursus lacus a tristique. Maecenas bibendum, erat vitae interdum hendrerit, nibh diam convallis risus, nec sollicitudin est neque eu nunc. Aenean condimentum viverra est a congue. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Donec viverra erat mauris, scelerisque interdum nisl hendrerit eu.</p>
</div>
<div>
	<p  class='internal-css-backgroundsvg'>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi gravida diam in tristique feugiat. In in metus faucibus, condimentum turpis ac, congue mauris. Integer ac varius erat, nec imperdiet odio. Maecenas sit amet dapibus risus. Pellentesque scelerisque cursus lacus a tristique. Maecenas bibendum, erat vitae interdum hendrerit, nibh diam convallis risus, nec sollicitudin est neque eu nunc. Aenean condimentum viverra est a congue. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Donec viverra erat mauris, scelerisque interdum nisl hendrerit eu.</p>
</div>

<div id="internal-BG-images">
	<h1>Lorem Ipsum Dolor</h1>
	<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat.</p>
</div>




<div>
	<h2>Background images from external CSS</h2>
</div>
<div class='external-css-background-image background-cover'>
	<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi gravida diam in tristique feugiat. In in metus faucibus, condimentum turpis ac, congue mauris. Integer ac varius erat, nec imperdiet odio. Maecenas sit amet dapibus risus. Pellentesque scelerisque cursus lacus a tristique. Maecenas bibendum, erat vitae interdum hendrerit, nibh diam convallis risus, nec sollicitudin est neque eu nunc. Aenean condimentum viverra est a congue. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Donec viverra erat mauris, scelerisque interdum nisl hendrerit eu.</p>
</div>
<div>
	<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris ut nulla ut lorem fringilla molestie ut non urna. Ut accumsan luctus lacinia. Praesent eleifend dolor leo. Ut erat risus, laoreet ac mi nec, varius efficitur sapien. In vitae sapien placerat, vestibulum ligula ac, vestibulum mi. Nulla eget scelerisque nisl. Aenean eu metus velit. Aliquam vel iaculis neque.</p>
	<p>Suspendisse potenti. Morbi laoreet odio in nunc iaculis, fermentum lobortis risus pharetra. Morbi scelerisque, libero at accumsan fermentum, purus felis blandit urna, consectetur porta sapien magna at augue. Praesent pulvinar velit mi, at consequat dui dictum ac. Pellentesque varius augue lorem, quis fringilla nisl gravida ac. Sed ac urna id enim gravida blandit. Sed cursus ligula sem, et imperdiet erat convallis a.</p>
	<p>Nullam rhoncus justo nibh, in finibus eros vehicula eget. Phasellus elementum nibh ut ipsum congue, id iaculis quam venenatis. Ut in nibh sit amet mi tincidunt efficitur bibendum at sapien. Duis euismod tempus ipsum efficitur condimentum. In hac habitasse platea dictumst. Fusce vestibulum, elit eget varius lacinia, velit tortor rutrum felis, sed scelerisque nunc tortor a est. Phasellus aliquam magna felis, id placerat ex varius vel.</p>
	<p>Morbi cursus sapien velit, posuere commodo libero congue nec. Interdum et malesuada fames ac ante ipsum primis in faucibus. Ut lorem arcu, interdum maximus lacus laoreet, euismod placerat nibh. Donec ac mauris eros. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Suspendisse potenti. Interdum et malesuada fames ac ante ipsum primis in faucibus. Cras eu mi non sem egestas pharetra.</p>
	<p>Etiam scelerisque placerat ex, ac efficitur lacus. Etiam vitae pharetra elit, non venenatis ante. Maecenas iaculis venenatis iaculis. Interdum et malesuada fames ac ante ipsum primis in faucibus. Quisque a ultricies enim, at efficitur metus. Donec quis consectetur leo. Duis tincidunt quis dolor ac condimentum. Nulla a augue id massa laoreet suscipit. Donec at leo efficitur, aliquet massa vel, tincidunt dolor. Sed vulputate purus vel hendrerit egestas. Donec in tempor eros, ut consectetur libero. Integer volutpat, nulla at ullamcorper ornare, massa ante ultricies tortor, a dignissim diam justo vitae leo.</p>
</div>
<div>
	<p  class='external-css-background-images background-no-repeat'>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi gravida diam in tristique feugiat. In in metus faucibus, condimentum turpis ac, congue mauris. Integer ac varius erat, nec imperdiet odio. Maecenas sit amet dapibus risus. Pellentesque scelerisque cursus lacus a tristique. Maecenas bibendum, erat vitae interdum hendrerit, nibh diam convallis risus, nec sollicitudin est neque eu nunc. Aenean condimentum viverra est a congue. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Donec viverra erat mauris, scelerisque interdum nisl hendrerit eu.</p>
</div>
<div>
	<p  class='external-css-background-image_gradient'>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi gravida diam in tristique feugiat. In in metus faucibus, condimentum turpis ac, congue mauris. Integer ac varius erat, nec imperdiet odio. Maecenas sit amet dapibus risus. Pellentesque scelerisque cursus lacus a tristique. Maecenas bibendum, erat vitae interdum hendrerit, nibh diam convallis risus, nec sollicitudin est neque eu nunc. Aenean condimentum viverra est a congue. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Donec viverra erat mauris, scelerisque interdum nisl hendrerit eu.</p>
</div>
<div class='external-css-background-image background-no-repeat'>
	<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi gravida diam in tristique feugiat. In in metus faucibus, condimentum turpis ac, congue mauris. Integer ac varius erat, nec imperdiet odio. Maecenas sit amet dapibus risus. Pellentesque scelerisque cursus lacus a tristique. Maecenas bibendum, erat vitae interdum hendrerit, nibh diam convallis risus, nec sollicitudin est neque eu nunc. Aenean condimentum viverra est a congue. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Donec viverra erat mauris, scelerisque interdum nisl hendrerit eu.</p>
</div>
<div>
	<p  class='external-css-background-images background-no-repeat'>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi gravida diam in tristique feugiat. In in metus faucibus, condimentum turpis ac, congue mauris. Integer ac varius erat, nec imperdiet odio. Maecenas sit amet dapibus risus. Pellentesque scelerisque cursus lacus a tristique. Maecenas bibendum, erat vitae interdum hendrerit, nibh diam convallis risus, nec sollicitudin est neque eu nunc. Aenean condimentum viverra est a congue. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Donec viverra erat mauris, scelerisque interdum nisl hendrerit eu.</p>
</div>
<div>
	<p  class='external-css-background-image-gradient'>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi gravida diam in tristique feugiat. In in metus faucibus, condimentum turpis ac, congue mauris. Integer ac varius erat, nec imperdiet odio. Maecenas sit amet dapibus risus. Pellentesque scelerisque cursus lacus a tristique. Maecenas bibendum, erat vitae interdum hendrerit, nibh diam convallis risus, nec sollicitudin est neque eu nunc. Aenean condimentum viverra est a congue. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Donec viverra erat mauris, scelerisque interdum nisl hendrerit eu.</p>
</div>
<div>
	<p  class='external-css-background'>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi gravida diam in tristique feugiat. In in metus faucibus, condimentum turpis ac, congue mauris. Integer ac varius erat, nec imperdiet odio. Maecenas sit amet dapibus risus. Pellentesque scelerisque cursus lacus a tristique. Maecenas bibendum, erat vitae interdum hendrerit, nibh diam convallis risus, nec sollicitudin est neque eu nunc. Aenean condimentum viverra est a congue. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Donec viverra erat mauris, scelerisque interdum nisl hendrerit eu.</p>
</div>


<div class="javascript-background-image">
	<h2>JavaScript added background image</h2>
</div>



<!-- internal CSS -->
<style>
	body{
		width:40%;
		margin-left: auto;
		margin-right: auto;
	}
	div{
		margin-top: 1em;
		margin-bottom: 1em;
	}
	p {
		font-size: 0.85em;
		color: black;
		background-image: none;
		background-color: transparent;
	}
	.internal-css-background-image{
		width: 100%;
		height: 400px;
		background-image: var(--wpr-bg-hash);
		background-color: #cccccc;
	}
	.internal-css-background-images{
		width: 100%;
		height: 400px;
		background-image: var(--wpr-bg-hash), var(--wpr-bg-hash);
		background-color: #cccccc;
	}
	.internal-css-background-image-gradient{
		width: 100%;
		height: 400px;
		background-image: linear-gradient(rgba(0, 0, 255, 0.5), rgba(255, 255, 0, 0.5)), var(--wpr-bg-hash);
	}
	.internal-css-background{
		background: var(--wpr-bg-hash);
	}
	.internal-css-background404{
		background: var(--wpr-bg-hash);
	}
	.background-no-repeat{
		background-repeat: no-repeat;
	}
	.background-cover{
		background-size: cover;
	}
	@media only screen and (max-width: 600px) {
		body {
			width: 80%;
		}
	}

	#internal-BG-images {
		background: var(--wpr-bg-hash) right bottom no-repeat, var(--wpr-bg-hash) left top repeat;
		padding: 15px;
	}
</style>


<!-- inline background images -->
<div style="background-image: url('https://new.rocketlabsqa.ovh/wp-content/rocket-test-data/images/fixtheissue.jpg');">
	You can specify background images<br>
	for any visible HTML element.<br>
	In this example, the background image<br>
	is specified for a div element.<br>
	By default, the background-image<br>
	will repeat itself in the direction(s)<br>
	where it is smaller than the element<br>
	where it is specified. (Try resizing the<br>
	browser window to see how the<br>
	background image behaves.
</div>

<div style="background-image: url('/wp-content/rocket-test-data/images/Przechwytywanie.PNG')">

	<div style="background-image: url('https://fastly.picsum.photos/id/976/200/300.jpg?hmac=s1Uz9fgJv32r8elfaIYn7pLpQXps7X9oYNwC5XirhO8');">
		You can specify background images<br>
		for any visible HTML element.<br>
		In this example, the background image<br>
		is specified for a p element.<br>
		By default, the background-image<br>
		will repeat itself in the direction(s)<br>
		where it is smaller than the element<br>
		where it is specified. (Try resizing the<br>
		browser window to see how the<br>
		background image behaves.
	</div>
</body>
</html>
