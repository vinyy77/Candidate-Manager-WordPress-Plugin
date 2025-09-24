<?php
/*
Plugin Name: Candidate Manager
Description: Candidate application form with admin dashboard.
Version: 1.0
Author: Tsbproject
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// Define constants
define( 'CANDIDATE_MANAGER_PATH', plugin_dir_path( __FILE__ ) );
define( 'CANDIDATE_MANAGER_URL', plugin_dir_url( __FILE__ ) );

// Includes
require_once CANDIDATE_MANAGER_PATH . 'includes/install.php';
require_once CANDIDATE_MANAGER_PATH . 'includes/form-handler.php';
require_once CANDIDATE_MANAGER_PATH . 'includes/shortcode.php';
require_once CANDIDATE_MANAGER_PATH . 'includes/dashboard.php';

// Activation hook → Create database
register_activation_hook( __FILE__, 'candidate_manager_install' );

// Load CSS/JS
function candidate_manager_assets() {
    wp_enqueue_style( 'candidate-manager-style', CANDIDATE_MANAGER_URL . 'assets/css/styles.css' );
    wp_enqueue_script( 'candidate-manager-js', CANDIDATE_MANAGER_URL . 'assets/js/script.js', array('jquery'), false, true );
}
add_action( 'wp_enqueue_scripts', 'candidate_manager_assets' );
add_action( 'admin_enqueue_scripts', 'candidate_manager_assets' );
