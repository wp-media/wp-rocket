<?php

// TO DO - Modification du cookie des commentaires
add_filter( 'comment_cookie_lifetime', create_function( '', 'return 300;' ) );