<?php

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );
?>
<a href="<?php echo esc_attr( $data['url'] ); ?>" <?php echo $data['attributes']; ?>><?php echo esc_html( $data['label'] ); ?></a>
