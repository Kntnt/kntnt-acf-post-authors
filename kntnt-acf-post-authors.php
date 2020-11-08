<?php

/**
 * @wordpress-plugin
 * Plugin Name:       Kntnt Post Author from ACF `author` field.
 * Plugin URI:        https://www.kntnt.com/
 * Description:       Sets author of a post to the value of an ACF field named `author`. If the field has multiple values, its first value is used.
 * Version:           1.1.0
 * Author:            Thomas Barregren
 * Author URI:        https://www.kntnt.com/
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 */


namespace Kntnt\ACF_Post_Authors;

defined( 'ABSPATH' ) && new Plugin;

final class Plugin {

    public static function authors( $post_id = null ) {
        $authors = [];
        if ( $post_id = $post_id ?: get_the_ID() ) {
            $user_ids = get_field( 'authors', $post_id, false );
            foreach ( $user_ids as $user_id ) {
                $authors[ $user_id ] = get_user_by( 'id', $user_id );
            }
        }
        return $authors;
    }

    public static function byline( $post_id = null ) {
        $authors = [];
        foreach ( self::authors( $post_id ) as $author ) {
            $url = get_author_posts_url( $author->ID, $author->user_nicename );
            $authors[] = "<a href=\"$url\">$author->display_name</a>";
        }
        $last_author = array_pop( $authors );
        if ( count( $authors ) ) {
            $authors = join( ', ', $authors );
            $authors = sprintf( _x( "%s and %s", 'List of authors', 'kntnt-acf-post-authors' ), $authors, $last_author );
        }
        else {
            $authors = $last_author;
        }
        return "<div class=\"kntnt-acf-post-authors\">$authors</div>";
    }

    public function __construct() {
        add_action( 'plugins_loaded', [ $this, 'run' ] );
    }

    public function run() {
        if ( function_exists( 'acf_add_local_field_group' ) ) {
            $this->add_author_field();
            add_action( 'do_meta_boxes', [ $this, 'remove_meta_box' ], 5, 2 );
            add_action( 'acf/save_post', [ $this, 'save_post' ], 20, 1 );
            add_action( 'acf/load_value/name=authors', [ $this, 'load_value' ], 5, 3 );
        }
    }

    public function remove_meta_box( $screen, $context ) {
        if ( 'post' == $screen ) {
            remove_meta_box( 'authordiv', $screen, $context );
        }
    }

    public function save_post( $post_id ) {
        $authors = get_field( 'authors', $post_id, false );
        wp_update_post( [ 'ID' => $post_id, 'post_author' => $authors[0] ] );
    }

    public function load_value( $value, $post_id, $field ) {
        if ( ! $value ) {
            $value[] = get_post_field( 'post_author', $post_id );
        }
        return $value;
    }

    private function add_author_field() {
        acf_add_local_field_group( [
            'key' => 'group_5fa70454e0090',
            'title' => __( 'Authors', 'kntnt-acf-post-authors' ),
            'fields' => [
                [
                    'key' => 'field_5fa704729505e',
                    'label' => __( 'Authors', 'kntnt-acf-post-authors' ),
                    'name' => 'authors',
                    'type' => 'user',
                    'instructions' => __( 'Main author first followed by any co-authors.', 'kntnt-acf-post-authors' ),
                    'required' => 1,
                    'conditional_logic' => 0,
                    'wrapper' => [
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ],
                    'role' => apply_filters( 'kntnt-acf-post-authors-roles', [
                        'administrator',
                        'editor',
                        'author',
                        'contributor',
                    ] ),
                    'allow_null' => 0,
                    'multiple' => 1,
                    'return_format' => 'array',
                ],
            ],
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => ' == ',
                        'value' => 'post',
                    ],
                ],
            ],
            'menu_order' => 20,
            'position' => 'side',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'field',
            'hide_on_screen' => '',
            'active' => true,
            'description' => '',
        ] );
    }
}
