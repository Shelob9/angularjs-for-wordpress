<?php
/**
 * Plugin Name: AngularJS for WordPress
 * Plugin URI: http://www.roysivan.com/angularjs-for-wordpress
 * Description: This plugin will allow you to easily load WordPress content client-side using AngularJS. JSON REST API required.
 * Version: 2.1
 * Author: Roy Sivan
 * Author URI: http://www.roysivan.com
 * License: GPL2
 */

require_once('includes/metaBox.php');
require_once('includes/contentFilter.php');
require_once('includes/shortcodes.php');

define('WordPressAngularJS', '2.0');

class WordPressAngularJS {
	function WordPressAngularJS(){
		global $wpdb;
		add_action( 'wp_enqueue_scripts', array( $this, 'angularScripts' ) );
		add_filter( 'json_insert_post', array( $this, 'post_add_tax' ), 10, 3 );
	}

	function angularScripts() {
		// Angular Core
		wp_enqueue_script('angular-core', plugin_dir_url( __FILE__ ).'js/angular.min.js', array('jquery'), null, false);
		wp_enqueue_script('angular-sanitize', plugin_dir_url( __FILE__ ).'js/angular-sanitize.min.js', array('jquery'), null, false);
		wp_enqueue_script('html-janitor', plugin_dir_url( __FILE__ ).'js/html-janitor.js', array('jquery'), null, false);
		
		wp_enqueue_script('angular-app', plugin_dir_url( __FILE__ ).'js/angular-app.js', array('html-janitor'), null, false);

		// Angular Factories
		wp_enqueue_script('angular-factories', plugin_dir_url( __FILE__ ).'js/angular-factories.js', array('angular-app'), null, false);

		// Angular Directives
		wp_enqueue_script('angular-post-directives', plugin_dir_url( __FILE__ ).'js/angular-posts-directives.js', array('angular-factories'), null, false);

		// Template Directory
		$template_directory = array(
			'list_detail' => plugin_dir_url( __FILE__ ).'angularjs-templates/list-detail.html',
			'single_detail' => plugin_dir_url( __FILE__ ).'angularjs-templates/single-detail.html',
			'new_post' => plugin_dir_url( __FILE__ ).'angularjs-templates/new-post.html',
			'post_content' => plugin_dir_url( __FILE__ ).'angularjs-templates/post-content.html',
		);

		// TEMPLATE OVERRIDES
		if(file_exists(get_stylesheet_directory().'/angularjs-templates/list-detail.html')) {
			$template_directory['list_detail'] = get_stylesheet_directory_uri().'/angularjs-templates/list-detail.html';
		}

		if(file_exists(get_stylesheet_directory().'/angularjs-templates/single-detail.html')) {
			$template_directory['single_detail'] = get_stylesheet_directory_uri().'/angularjs-templates/single-detail.html';
		}
		if(file_exists(get_stylesheet_directory().'/angularjs-templates/new-post.html')) {
			$template_directory['new_post'] = get_stylesheet_directory_uri().'/angularjs-templates/new-post.html';
		}
		if(file_exists(get_stylesheet_directory().'/angularjs-templates/post-content.html')) {
			$template_directory['post_content'] = get_stylesheet_directory_uri().'/angularjs-templates/post-content.html';
		}

		
		$angularjs_for_wp_localize = array( 
			'site' => get_bloginfo('wpurl'), 
			'nonce' => wp_create_nonce( 'wp_json' ), 
			'template_directory' => $template_directory 
		);
		
		if( function_exists( 'json_url' ) ) {
			$angularjs_for_wp_localize['base'] = json_url();
		}
		
		if( function_exists( 'rest_get_url_prefix' ) ) {
			$angularjs_for_wp_localize['base'] = get_bloginfo( 'wpurl') . '/' . rest_get_url_prefix() . '/wp/v2';
		}
		
		// Localize Variables
		wp_localize_script( 
			'angular-core', 
			'wpAngularVars', 
			$angularjs_for_wp_localize 
		);
	}
	
	function post_add_tax( $post, $data, $update ) {
		foreach( $data['post_taxonomies'] as $term => $tax ){
			wp_set_post_terms( $post['ID'], array( intval( $term ) ), $tax, true );	        
	    }	    
	}
}

/** JSON REST API CHECK **/
function angularjs_plugin_dep() {
    if ( ! defined( 'REST_API_VERSION' ) ) {
        function wpsd_admin_notice() {
            printf( '<div class="error"><p>%s</p></div>', __( 'Activate the WP REST API plugin.  It
            is required.' ) );
        }
        add_action( 'admin_notices', 'angular_wpapi_error' );
    }
}

function angular_wpapi_error(){
	echo '<div class="error"><p><strong>JSON REST API</strong> must be installed and activated for the <strong>AngularJS for WP</strong> plugin to work properly - <a href="https://wordpress.org/plugins/json-rest-api/" target="_blank">Install Plugin</a></p></div>';
}

add_action( 'admin_init', 'angularjs_plugin_dep', 99 );

new WordPressAngularJS();
?>
