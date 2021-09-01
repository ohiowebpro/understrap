<?php
/**
 * UnderStrap functions and definitions
 *
 * @package UnderStrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// UnderStrap's includes directory.
$understrap_inc_dir = 'inc';

// Array of files to include.
$understrap_includes = array(
	'/theme-settings.php',                  // Initialize theme default settings.
	'/setup.php',                           // Theme setup and custom theme supports.
	'/widgets.php',                         // Register widget area.
	'/enqueue.php',                         // Enqueue scripts and styles.
	'/template-tags.php',                   // Custom template tags for this theme.
	'/pagination.php',                      // Custom pagination for this theme.
	'/hooks.php',                           // Custom hooks.
	'/extras.php',                          // Custom functions that act independently of the theme templates.
	'/customizer.php',                      // Customizer additions.
	'/custom-comments.php',                 // Custom Comments file.
	'/class-wp-bootstrap-navwalker.php',    // Load custom WordPress nav walker. Trying to get deeper navigation? Check out: https://github.com/understrap/understrap/issues/567.
	'/editor.php',                          // Load Editor functions.
	'/deprecated.php',                      // Load deprecated functions.
);

// Load WooCommerce functions if WooCommerce is activated.
if ( class_exists( 'WooCommerce' ) ) {
	$understrap_includes[] = '/woocommerce.php';
}

// Load Jetpack compatibility file if Jetpack is activiated.
if ( class_exists( 'Jetpack' ) ) {
	$understrap_includes[] = '/jetpack.php';
}

// Include files.
foreach ( $understrap_includes as $file ) {
	require_once get_theme_file_path( $understrap_inc_dir . $file );
}




####################################################################################
# Theme Image sizes needed

//add_image_size( 'med-short', 600, 245, true );
//add_image_size( 'page-header', 1600, 440, true );
//add_image_size( 'home-header', 1600, 675, true );

####################################################################################

####################################################################################
# Block access to usernames with these 2 functions

function redirect_to_home_if_author_parameter() {

	$is_author_set = get_query_var( 'author', '' );
	if ( $is_author_set != '' && !is_admin()) {
		wp_redirect( home_url(), 301 );
		exit;
	}
}
add_action( 'template_redirect', 'redirect_to_home_if_author_parameter' );
function disable_rest_endpoints ( $endpoints ) {
	if ( isset( $endpoints['/wp/v2/users'] ) ) {
		unset( $endpoints['/wp/v2/users'] );
	}
	if ( isset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] ) ) {
		unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
	}
	return $endpoints;
}
add_filter( 'rest_endpoints', 'disable_rest_endpoints');

####################################################################################

####################################################################################
// Theme Colors for picker
// Check out new way using json file?

//function mytheme_setup_theme_supported_features() {
//	add_theme_support( 'editor-color-palette', array(
//		array(
//			'name' => __( 'White', 'owp-custom' ),
//			'slug' => 'kcd-white',
//			'color' => '#ffffff',
//		),
//
//
//	) );
//}
//add_action( 'after_setup_theme', 'mytheme_setup_theme_supported_features' );

####################################################################################

####################################################################################
//* Loading editor styles for the block editor (Gutenberg)

add_theme_support( 'align-wide' ); //wide image support
add_theme_support('editor-styles'); //editor styles added
add_theme_support( 'wp-block-styles' ); //add block style css to front end
function site_block_editor_styles() {
	$custom_editor  = get_template_directory() . '/css/custom-editor-style.min.css';
	if (file_exists($custom_editor)) {
		$filetime = filemtime( $custom_editor );
		wp_enqueue_style( 'site-block-editor-styles', get_theme_file_uri( '/css/custom-editor-style.min.css' ), false, $filetime, 'all' );
	}
}
add_action( 'enqueue_block_editor_assets', 'site_block_editor_styles' );

####################################################################################

####################################################################################
// REMOVE WP EMOJI

remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');


####################################################################################

####################################################################################
// Remove generator version

function wpbeginner_remove_version() {
	return '';
}
add_filter('the_generator', 'wpbeginner_remove_version');

####################################################################################

####################################################################################
// Move scripts

function my_deregister_scripts(){
	wp_deregister_script( 'wp-embed' );
}
add_action( 'wp_footer', 'my_deregister_scripts' );

####################################################################################

####################################################################################
// Recommend plugins for this theme

add_action('admin_notices', 'showAdminMessages');

function showAdminMessages() {
	$plugin_messages = array();

	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

	$aRequired_plugins = array(
		array('name'=>'Advanced Custom Fields Pro', 'download'=>'https://www.advancedcustomfields.com/pro/', 'path'=>'advanced-custom-fields-pro/acf.php'),
		array('name'=>'Bootstrap Blocks', 'download'=>'https://wordpress.org/plugins/wp-bootstrap-blocks/', 'path'=>'wp-bootstrap-blocks/wp-bootstrap-blocks.php'),
		//array('name'=>'CPT UI', 'download'=>'https://wordpress.org/plugins/custom-post-type-ui/', 'path'=>'custom-post-type-ui/custom-post-type-ui.php'),
		//array('name'=>'SVG Support', 'download'=>'https://wordpress.org/plugins/svg-support/', 'path'=>'svg-support/svg-support.php'),
		//array('name'=>'Post Types Order', 'download'=>'https://wordpress.org/plugins/post-types-order/', 'path'=>'post-types-order/post-types-order.php'),
		//array('name'=>'FancyBox for WP', 'download'=>'https://wordpress.org/plugins/fancybox-for-wordpress/', 'path'=>'fancybox-for-wordpress/fancybox.php'),
	);
	foreach($aRequired_plugins as $aPlugin) {
		// Check if plugin exists
		if(!is_plugin_active( $aPlugin['path'] )) {
			$plugin_messages[] = '<div class="notice notice-error"> <p>This theme recommends you to install the <a href="/wp-admin/plugin-install.php?s='.urlencode($aPlugin['name']).'&amp;tab=search&amp;type=term">'.$aPlugin['name'].'</a> plugin.  <a target="_blank" href="'.$aPlugin['download'].'">View site.</a></p></div>';
		}
	}
	if(count($plugin_messages) > 0) {

		foreach($plugin_messages as $message) {
			echo '

                '.$message.'
            ';
		}
	}
}

####################################################################################

####################################################################################
//Edit link for modules for use in templates

if (!function_exists('swg_edit')) {
	function swg_edit($text, $url) {
		if (current_user_can('manage_options')) {
			return '<p class="text-left swg_edit"><a href="' . $url . '">[' . $text . ']</a></p>';
		}
		return false;
	}
}

####################################################################################
