<?php
/**
 * Plugin Name:     Holidays
 * Plugin URI:      PLUGIN SITE HERE
 * Description:     Adds Custom Post Type <em>holidays</em> with a new user role <em>tourist</em>
 * Author:          Bego Mario Garde
 * Author URI:      https://pixolin.de
 * Text Domain:     holidays
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Holidays
 */

// Make sure this file is only run from within WordPress.
defined( 'ABSPATH' ) || die();

/**
 * Registers a new post type
 *
 * @uses $wp_post_types Inserts new post type object into the list
 *
 * @param string  Post type key, must not exceed 20 characters
 * @param array|string  See optional args description above.
 * @return object|WP_Error the registered post type object, or an error object
 */
function pix_register_holidays() {

	$labels = array(
		'name'               => __( 'Holidays', 'text-domain' ),
		'singular_name'      => __( 'Holiday', 'text-domain' ),
		'add_new'            => _x( 'Add New Holiday', 'text-domain', 'text-domain' ),
		'add_new_item'       => __( 'Add New Holiday', 'text-domain' ),
		'edit_item'          => __( 'Edit Holiday', 'text-domain' ),
		'new_item'           => __( 'New Holiday', 'text-domain' ),
		'view_item'          => __( 'View Holiday', 'text-domain' ),
		'search_items'       => __( 'Search Holidays', 'text-domain' ),
		'not_found'          => __( 'No Holidays found', 'text-domain' ),
		'not_found_in_trash' => __( 'No Holidays found in Trash', 'text-domain' ),
		'parent_item_colon'  => __( 'Parent Holiday:', 'text-domain' ),
		'menu_name'          => __( 'Holidays', 'text-domain' ),
	);

	$args = array(
		'labels'              => $labels,
		'hierarchical'        => false,
		'description'         => 'description',
		'taxonomies'          => array(),
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_admin_bar'   => true,
		'menu_position'       => 20,
		'menu_icon'           => 'dashicons-palmtree',
		'show_in_nav_menus'   => true,
		'publicly_queryable'  => true,
		'exclude_from_search' => false,
		'has_archive'         => true,
		'query_var'           => true,
		'can_export'          => true,
		'rewrite'             => true,
		'capability_type'     => array( 'holidays', 'holidays' ),
		'supports'            => array(
			'title',
			'editor',
			'author',
			'thumbnail',
			'excerpt',
		),
	);

	register_post_type( 'holidays', $args );
}
add_action( 'init', 'pix_register_holidays' );

/**
 * Adds user role "tourist" upon plugin activation
 */
function pix_add_tourist_role() {
	add_role(
		'tourist',
		'Tourist',
		array(
			'read'          => true,
			'edit_posts'    => false,
			'delete_posts'  => false,
			'publish_posts' => false,
			'upload_files'  => true,
		)
	);
}
register_activation_hook( __FILE__, 'pix_add_tourist_role' );


add_action( 'admin_init', 'pix_add_role_caps', 999 );
function pix_add_role_caps() {

	// Add the roles you'd like to administer the custom post types
	$roles = array( 'tourist', 'editor', 'administrator' );

	// Loop through each role and assign capabilities
	foreach ( $roles as $the_role ) {

		$usrrole = get_role( $the_role );

		$usrrole->add_cap( 'read' );
		$usrrole->add_cap( 'read_holidays' );
		$usrrole->add_cap( 'read_private_holidays' );
		$usrrole->add_cap( 'edit_holidays' );

		$usrrole->add_cap( 'publish_holidays' );
		$usrrole->add_cap( 'delete_private_holidays' );

	}

	$roles = array( 'editor', 'administrator' );

	foreach ( $roles as $the_role ) {
		$usrrole = get_role( $the_role );

		$usrrole->add_cap( 'edit_others_holidays' );
		$usrrole->add_cap( 'delete_others_holidays' );
	}

	// Don't let tourists edit other tourist's posts
	$role = get_role( 'tourist' );
	$role->remove_cap( 'edit_others_posts' );

}

function posts_for_current_author( $query ) {
	global $pagenow;

	if ( 'edit.php' != $pagenow || ! $query->is_admin ) {
		return $query;
	}

	if ( ! current_user_can( 'edit_others_posts' ) ) {
		global $user_ID;
		$query->set( 'author', $user_ID );
	}
	return $query;
}
add_filter( 'pre_get_posts', 'posts_for_current_author' );
