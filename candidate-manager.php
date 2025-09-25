<?php
declare(strict_types=1);

/**
 * Plugin Name: Candidate Manager
 * Plugin URI: https://github.com/tsbproject/Candidate-Manager-WordPress-Plugin
 * Description: A professional candidate application management plugin for WordPress with form submissions, resume uploads, and admin dashboard.
 * Version: 1.0.0
 * Author: Tsbproject
 * Author URI: https://github.com/tsbproject/Candidate-Manager-WordPress-Plugin
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: candidate-manager
 *
 * PHP version 8.1
 *
 * @category  WordPress_Plugin
 * @package   CandidateManager
 * @author    Tsbproject <https://github.com/tsbproject/Candidate-Manager-WordPress-Plugin>
 * @license   GPL2 https://www.gnu.org/licenses/gpl-2.0.html
 * @link      https://github.com/tsbproject/Candidate-Manager-WordPress-Plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define constants
define('CANDIDATE_MANAGER_PATH', plugin_dir_path(__FILE__));
define('CANDIDATE_MANAGER_URL', plugin_dir_url(__FILE__));

// Always load install.php before register_activation_hook
require_once CANDIDATE_MANAGER_PATH . 'includes/install.php';

// Activation Hook → Now WordPress will find the function
register_activation_hook(__FILE__, 'candidate_manager_install');

// Load other includes
require_once CANDIDATE_MANAGER_PATH . 'includes/shortcode.php';
require_once CANDIDATE_MANAGER_PATH . 'includes/dashboard.php';
require_once CANDIDATE_MANAGER_PATH . 'includes/form-handler.php';


// Load CSS/JS
/**
 * Enqueue Candidate Manager plugin CSS and JS assets.
 *
 * @return void
 */
function Candidate_Manager_assets() {
    wp_enqueue_style( 'candidate-manager-style', CANDIDATE_MANAGER_URL . 'assets/css/styles.css' );
    wp_enqueue_script( 'candidate-manager-js', CANDIDATE_MANAGER_URL . 'assets/js/script.js', array('jquery'), false, true );
}
add_action( 'wp_enqueue_scripts', 'Candidate_Manager_assets' );
add_action( 'admin_enqueue_scripts', 'Candidate_Manager_assets' );
