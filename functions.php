<?php

spl_autoload_register(function ($class) {

    // project-specific namespace prefix
    $prefix = 'cf47\\theme\\realtyspace\\child';

    // base directory for the namespace prefix
    $base_dir = __DIR__ . '/lib';

    // does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // no, move to the next registered autoloader
        return;
    }

    // get the relative class name
    $relative_class = substr($class, $len);

    // replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators in the relative class name, append
    // with .php
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    // if the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});

add_action('cf47_app_callback', function (\cf47\themecore\Application $app) {
    $app->register_module(new cf47\theme\realtyspace\child\Init());
});

// Override secondary styles and scripts
add_action('wp_enqueue_scripts', function () {
    $version = filemtime(get_stylesheet_directory() . '/' . 'script.js');
    wp_enqueue_script('cf47rs-script', get_stylesheet_directory_uri() . '/script.js', ['jquery'], $version);
});

// Load child theme translation files
add_action('after_setup_theme', function () {
    load_child_theme_textdomain('realtyspace', get_stylesheet_directory() . '/languages');
});

// Override main theme style
add_action('wp_enqueue_scripts', function () {
//     Please uncomment which you need
//    wp_deregister_style('cf47rs-theme');
//    wp_enqueue_style(
//        'cf47rs-theme', get_stylesheet_directory_uri() . '/theme-default.css',
//        [
//            'cf47rs-vendors',
//            'cf47rs-standartwp',
//        ],
//        filemtime(get_stylesheet_directory() . '/theme-default.css')
//    );
}, 20);

// Override secondary styles and scripts
add_action('wp_enqueue_scripts', function () {
//    wp_enqueue_style( 'cf47rs-standartwp', get_stylesheet_directory_uri() . '/wordpress.css' );
//    wp_enqueue_style( 'cf47rs-vendors', get_stylesheet_directory_uri() . '/vendors.css' );
//    wp_enqueue_script( 'cf47rs-vendors', get_stylesheet_directory_uri() . '/vendor.js' );
//    wp_enqueue_script( 'cf47rs-app', get_stylesheet_directory_uri() . '/app.js' );
});

// /**
//  * This hook is fired once WP, all plugins, and the theme are fully loaded and instantiated.
//  *
//  * Ajax requests should use wp-admin/admin-ajax.php. admin-ajax.php can handle requests for
//  * users not logged in.
//  *
//  * @link https://codex.wordpress.org/AJAX_in_Plugins
//  *
//  * @since 3.0.0
//  */
// do_action( 'wp_loaded' );
//
//
//
// /**
//  * Modify REST API content for pages to force
//  * shortcodes to render since Visual Composer does not
//  * do this
//  */
// add_action('rest_api_init', function ()
// {
//    register_rest_field(
//           'page',
//           'content',
//           array(
//                  'get_callback'    => 'convert_do_shortcodes',
//                  'update_callback' => null,
//                  'schema'          => null,
//           )
//        );
// });
//
// function convert_do_shortcodes( $object, $field_name, $request )
// {
//    WPBMap::addAllMappedShortcodes(); // This does all the work
//
//    global $post;
//    $post = get_post ($object['id']);
//    $output['rendered'] = apply_filters( 'the_content', $post->post_content );
//
//    return $output;
// }

/**
* Add Custom Log function for objects
* @author Jack
* @see http://www.stumiller.me/sending-output-to-the-wordpress-debug-log/
*/
if (!function_exists('write_log')) {
    function write_log ( $log )  {
        if ( true === WP_DEBUG ) {
            if ( is_array( $log ) || is_object( $log ) ) {
                error_log( print_r( $log, true ) );
            } else {
                error_log( $log );
            }
        }
    }
}

/**
* Add phone to user.
* Commented out due to field automatically added by Twilio Core plugin
* @author Jack
* @see https://coderwall.com/p/g72jfg/adding-a-phone-numer-field-to-wordpress-user-profile
* @see http://justintadlock.com/archives/2009/09/10/adding-and-using-custom-user-profile-fields
*/
// add_action( 'personal_options_update', 'my_save_extra_profile_fields' );
// add_action( 'edit_user_profile_update', 'my_save_extra_profile_fields' );

// function my_save_extra_profile_fields( $user_id ) {

// if ( !current_user_can( 'edit_user', $user_id ) )
//     return false;

// update_user_meta( $user_id, 'phone', $_POST['phone'] );
// }

/**
* Add mobile to REST Response
* @author Jack
* @see https://developer.wordpress.org/rest-api/extending-the-rest-api/modifying-responses/
*/
add_action( 'rest_api_init', function() {
    register_rest_field( 'user', 'mobile', array(
        'get_callback' => function( $user_arr ) {
            $user_obj = get_userdata( $user_arr['id'] );
            return (string) $user_obj->mobile;
        },
        'update_callback' => function( $value, $object, $field_name ) {
            $ret = update_user_meta( $object->ID, $field_name, $value );
            if ( false === $ret ) {
                return new WP_Error( 'rest_user_mobile_failed', __( 'Failed to update user mobile.' ), array( 'status' => 500 ) );
            }
            return true;
        },
        'schema' => array(
            'description' => __( 'User mobile.' ),
            'type'        => 'string'
        ),
    ) );
} );

// The object type. For custom post types, this is 'post';
// for custom comment types, this is 'comment'. For user meta,
// this is 'user'.
// $object_type = 'user';
// $args1 = array( // Validate and sanitize the meta value.
//     // Note: currently (4.7) one of 'string', 'boolean', 'integer',
//     // 'number' must be used as 'type'. The default is 'string'.
//     'type'         => 'string',
//     // Shown in the schema for the meta key.
//     'description'  => 'This is the meta for phone.',
//     // Return a single value of the type.
//     'single'       => true,
//     // Show in the WP REST API response. Default: false.
//     'show_in_rest' => true,
// );
// register_meta( $object_type, 'phone', $args1 );




/**
* Custom REST API
* @author Jack
* @see http://v2.wp-api.org/extending/adding/
*/

/**
* Get an array of User Favorites
* @param $user_id int, defaults to current user
* @param $site_id int, defaults to current blog/site
* @param $filters array of post types/taxonomies
* @return array
*/
function rest_get_user_favorites( $data ) {

  $user_id = null;

  if (!is_user_logged_in()) {
    return "User is not logged in";
  } else {
    $user_id = get_current_user_id();
  }

  $idList = get_user_favorites($user_id, $site_id, $filters = ["cf47rs_property"]);

  if (empty($idList)) {
    return $idList;
  }

  // $filter = array('post_type' => 'cf47rs_property',
  //                 'post__in' => $idList);

  $favouriteArray = get_posts($filter);

  return array_values($idList);
}
add_action( 'rest_api_init', function () {
  register_rest_route( 'favorites/v1', '/my', array(
    'methods' => 'GET',
    'callback' => 'rest_get_user_favorites',
    'args' => array(
    ),
  ) );
} );

/**
* Get the total favorite count for a post
* Post ID not required if inside the loop
* @param int $post_id
*/
function rest_get_favorites_count( $data ) {

  $post_id = $data['id'];
  $favouriteNumber = get_favorites_count($post_id);

  return $favouriteNumber;
}
add_action( 'rest_api_init', function () {
  register_rest_route( 'favorites/v1', '/(?P<id>\d+)', array(
    'methods' => 'GET',
    'callback' => 'rest_get_favorites_count',
    'args' => array(
      'id' => array(
        'validate_callback' => function($param, $request, $key) {
          return is_numeric( $param );
        }
      ),
    ),
  ) );
} );

/**
* Get the total favorite count for a post
* Post ID not required if inside the loop
* @param int $post_id
*/
function rest_user_favorites_post( $request ) {

  $parameters = $request->get_json_params();

  $post_id = $parameters['id'];
  $status = $parameters['status'];
  $site_id = $parameters['site_id'];

  $new_favourite_count = user_favorites_posts($post_id, $status, $site_id);

  return $new_favourite_count;
}
add_action( 'rest_api_init', function () {
  register_rest_route( 'favorites/v1', '/(?P<id>\d+)', array(
    'methods' => 'POST',
    'callback' => 'rest_user_favorites_post',
    'args' => array(
      'id' => array(
        'validate_callback' => function($param, $request, $key) {
          return is_numeric( $param );
        }
      ),
    ),
  ) );
} );

/**
* Remove CSS JS version number
* @author Jack
*/
add_filter( 'style_loader_src',  'sdt_remove_ver_css_js', 9999, 2 );
add_filter( 'script_loader_src', 'sdt_remove_ver_css_js', 9999, 2 );

function sdt_remove_ver_css_js( $src, $handle )
{
    $handles_with_version = [ 'style' ]; // <-- Adjust to your needs!

    if ( strpos( $src, 'ver=' ) && ! in_array( $handle, $handles_with_version, true ) )
        $src = remove_query_arg( 'ver', $src );

    return $src;
}

/**
* Get a 6 digit verification code
*
* @author Jack
* @see http://stackoverflow.com/questions/5464906/how-can-i-generate-a-6-digit-unique-number
* @see https://github.com/mohsinoffline/wp-twilio-core
*/
function rest_get_verification_code( WP_REST_Request $request ) {

  if ( false == is_user_logged_in() ) {
    return false;
  }

  $parameters = $request->get_json_params();

  $mobile = $parameters['mobile'];
  $six_digit_random_number = mt_rand(100000, 999999);

  $mesasge = 'Your OZII verification code is ' . $six_digit_random_number;

  $args = array(
    'number_to' => $mobile,
    'message' => $mesasge
    );
  $result = twl_send_sms( $args );

  return $six_digit_random_number;
}
add_action( 'rest_api_init', function () {
  register_rest_route( 'smsverify/v1/', '/send', array(
    'methods' => 'POST',
    'callback' => 'rest_get_verification_code'
  ) );
} );

function prefix_add_taxonimies_to_api() {
    $args = array(
        ‘public’   => true,
        ‘_builtin’ => false
    );
    $taxonomies = get_taxonomies($args, ‘objects’);
    foreach($taxonomies as $taxonomy) {
        $taxonomy->show_in_rest = true;
    }
}
add_action(‘init’, ‘prefix_add_taxonimies_to_api’, 30);

/**
* @author Jack
* @see https://www.gavick.com/blog/wordpress-automatically-set-post-featured-image
* @description Automatically set the first image as the featured image
*/
function auto_featured_image() {
    global $post;
    write_log("A");
    if (!has_post_thumbnail($post->ID)) {
    write_log("B");
        $attached_image = get_children( "post_parent=$post->ID&amp;post_type=attachment&amp;post_mime_type=image&amp;numberposts=1" );
         
      if ($attached_image) {
    write_log("C");
              foreach ($attached_image as $attachment_id => $attachment) {
    write_log("D");
                   set_post_thumbnail($post->ID, $attachment_id);
              }
         }
    }
}
// Use it temporary to generate all featured images
add_action('the_post', 'auto_featured_image');
// Used for new posts
add_action('save_post', 'auto_featured_image');
add_action('draft_to_publish', 'auto_featured_image');
add_action('new_to_publish', 'auto_featured_image');
add_action('pending_to_publish', 'auto_featured_image');
add_action('future_to_publish', 'auto_featured_image');
