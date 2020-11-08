<?php

/**
 * @wordpress-plugin
 * Plugin Name:       Kntnt Post Author from ACF `author` field.
 * Plugin URI:        https://www.kntnt.com/
 * Description:       Sets author of a post to the value of an ACF field named `author`. If the field has multiple values, its first value is used.
 * Version:           1.0.0
 * Author:            Thomas Barregren
 * Author URI:        https://www.kntnt.com/
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 */


defined( 'ABSPATH' ) || die;

add_action( 'acf/save_post', function ( $post_id ) {
    $author = get_field( 'author', $post_id, false );
    if ( is_array( $author ) ) {
        $author = $author[0];
    }
    wp_update_post( [ 'ID' => $post_id, 'post_author' => $author ] );
}, 100, 1 );