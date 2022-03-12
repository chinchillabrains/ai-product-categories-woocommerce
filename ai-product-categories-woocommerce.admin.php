<?php

if ( ! defined('ABSPATH') ) {
    die( 'ABSPATH is not defined! "Script didn\' run on Wordpress."' );
}
if ( !is_admin() ) {
    die('Not enough privileges');
}

?>
<form method="post" action="options.php">
    <?php 
        // settings_fields( AIPC_TEXTDOMAIN . '_settings' );
    ?>
</form>