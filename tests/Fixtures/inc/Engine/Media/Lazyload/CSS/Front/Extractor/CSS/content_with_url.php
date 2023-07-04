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
background-image: url("/wp-content/rocket-test-data/images/paper.jpeg");
background-color: #cccccc;
}
.internal-css-background-images{
width: 100%;
height: 400px;
background-image: url('https://new.rocketlabsqa.ovh/wp-content/rocket-test-data/images/test.png'), url( "/wp-content/rocket-test-data/images/paper.jpeg" );
background-color: #cccccc;
}
.internal-css-background-image-gradient{
width: 100%;
height: 400px;
background-image: linear-gradient(rgba(0, 0, 255, 0.5), rgba(255, 255, 0, 0.5)), url("/wp-content/rocket-test-data/images/paper.jpeg");
}
.internal-css-background{
background: url('/wp-content/rocket-test-data/images/test.png');
}
.internal-css-background404{
background: url('/wp-content/rocket-test-data/images/testnotExist.png');
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
background: url(/wp-content/rocket-test-data/images/butterfly.avif) right bottom no-repeat, url(/wp-content/rocket-test-data/images/file_example_TIFF_1MB.tiff) left top repeat;
padding: 15px;
}

#internal-BG-images2{padding: 15px;}#internal-BG-images{background:url(/wp-content/rocket-test-data/images/butterfly.avif) right bottom no-repeat, url(/wp-content/rocket-test-data/images/file_example_TIFF_1MB.tiff) left top repeat;padding:15px;}

.external-css-background-image{
width: 100%;
height: 400px;
background-image: url("https://new.rocketlabsqa.ovh/wp-content/rocket-test-data/images/paper.jpeg");
background-color: #cccccc;
}
.external-css-background-images{
width: 100%;
height: 400px;
background-image: url('/wp-content/rocket-test-data/images/test.png'), url( "/wp-content/rocket-test-data/images/paper.jpeg" );
background-color: #3dd83d;
}
.external-css-background-image-gradient{
width: 100%;
height: 400px;
background-image: linear-gradient(rgba(0, 0, 255, 0.5), rgba(255, 255, 0, 0.5)), url(/wp-content/rocket-test-data/images/paper.jpeg);
}
.external-css-background{
background: url('/test.png') no-repeat;
background-color: #4fc1dd;
}

.external-css-backgroundsvg{
background: url('https://upload.wikimedia.org/wikipedia/commons/1/11/Test-Logo.svg') no-repeat;
background-color: #c63dd8;
}
