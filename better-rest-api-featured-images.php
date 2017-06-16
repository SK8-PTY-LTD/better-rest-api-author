<?php
/**
 * Better REST API Author
 *
 * @package             Better_REST_API_Featured_Images
 * @author              Braad Martin <wordpress@braadmartin.com>
 * @license             GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name:         Better REST API Author
 * Plugin URI:          https://wordpress.org/plugins/better-rest-api-author/
 * Description:         Adds a top-level field with featured image data including available sizes and URLs to the post object returned by the REST API.
 * Version:             1.2.1
 * Author:              Braad Martin
 * Author URI:          http://braadmartin.com
 * License:             GPL-2.0+
 * License URI:         http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:         better-rest-api-author
 * Domain Path:         /languages
 */

add_action( 'plugins_loaded', 'better_rest_api_author_load_translations' );
/**
 * Load translation files.
 *
 * @since  1.2.0
 */
function better_rest_api_author_load_translations() {
    load_plugin_textdomain( 'better-rest-api-author', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
}

add_action( 'init', 'better_rest_api_author_init', 12 );
/**
 * Register our enhanced better_author field to all public post types
 * that support post thumbnails.
 *
 * @since  1.0.0
 */
function better_rest_api_author_init() {

	$post_types = get_post_types( array( 'public' => true ), 'objects' );

	foreach ( $post_types as $post_type ) {

		$post_type_name     = $post_type->name;
		$show_in_rest       = ( isset( $post_type->show_in_rest ) && $post_type->show_in_rest ) ? true : false;
		$supports_author = post_type_supports( $post_type_name, 'author' );

		// Only proceed if the post type is set to be accessible over the REST API
		// and supports featured images.
// 		if ( $show_in_rest && $supports_author ) {

			// Compatibility with the REST API v2 beta 9+
			if ( function_exists( 'register_rest_field' ) ) {
				register_rest_field( $post_type_name,
					'better_author',
					array(
						'get_callback' => 'better_rest_api_author_get_field',
						'schema'       => null,
					)
				);
			} elseif ( function_exists( 'register_api_field' ) ) {
				register_api_field( $post_type_name,
					'better_author',
					array(
						'get_callback' => 'better_rest_api_author_get_field',
						'schema'       => null,
					)
				);
			}
// 		}
	}
}

/**
 * Return the better_author field.
 *
 * @since   1.0.0
 *
 * @param   object  $object      The response object.
 * @param   string  $field_name  The name of the field to add.
 * @param   object  $request     The WP_REST_Request object.
 *
 * @return  object|null
 */
function better_rest_api_author_get_field( $object, $field_name, $request ) {

	// Only proceed if the post has a featured image.
	if ( ! empty( $object['author'] ) ) {
		$author_id = (int)$object['author'];
	} else {
		return null;
	}

	$author = get_userdata( $author_id );

	if ( ! $author ) {
		return null;
	}

	// // This is taken from WP_REST_Attachments_Controller::prepare_item_for_response().
	// $featured_image['id']            = $author_id;
	// $featured_image['alt_text']      = get_post_meta( $author_id, '_wp_attachment_image_alt', true );
	// $featured_image['caption']       = $author->post_excerpt;
	// $featured_image['description']   = $author->post_content;
	// $featured_image['media_type']    = wp_attachment_is_image( $author_id ) ? 'image' : 'file';
	// $featured_image['media_details'] = wp_get_attachment_metadata( $author_id );
	// $featured_image['post']          = ! empty( $author->post_parent ) ? (int) $author->post_parent : null;
	// $featured_image['source_url']    = wp_get_attachment_url( $author_id );

	// if ( empty( $featured_image['media_details'] ) ) {
	// 	$featured_image['media_details'] = new stdClass;
	// } elseif ( ! empty( $featured_image['media_details']['sizes'] ) ) {
	// 	$img_url_basename = wp_basename( $featured_image['source_url'] );
	// 	foreach ( $featured_image['media_details']['sizes'] as $size => &$size_data ) {
	// 		$author_src = wp_get_attachment_image_src( $author_id, $size );
	// 		if ( ! $author_src ) {
	// 			continue;
	// 		}
	// 		$size_data['source_url'] = $author_src[0];
	// 	}
	// } elseif ( is_string( $featured_image['media_details'] ) ) {
	// 	// This was added to work around conflicts with plugins that cause
	// 	// wp_get_attachment_metadata() to return a string.
	// 	$featured_image['media_details'] = new stdClass();
	// 	$featured_image['media_details']->sizes = new stdClass();
	// } else {
	// 	$featured_image['media_details']['sizes'] = new stdClass;
	// }

	return apply_filters( 'better_rest_api_featured_image', $author, $author_id );
}
