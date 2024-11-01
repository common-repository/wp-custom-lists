<?php

if ( ! defined( 'ABSPATH' ) )
    exit;

if ( ! class_exists( '_WP_Editors' ) )
    require( ABSPATH . WPINC . '/class-wp-editor.php' );

function wp_753158_list_plugin_translation() {
    $strings = array(
        'insert_list' => __('Insert list', 'wp-list-cpt')
    );
    $locale = _WP_Editors::$mce_locale;
    $translated = 'tinyMCE.addI18n("' . $locale . '.wplistlang", ' . json_encode( $strings ) . ");\n";

     return $translated;
}

$strings = wp_753158_list_plugin_translation();