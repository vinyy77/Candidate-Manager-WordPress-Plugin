<?php
declare(strict_types=1);

namespace CandidateManager;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function handle_candidate_form_submission(): void {
    if ( ! isset($_POST['candidate_nonce']) ||
         ! wp_verify_nonce( sanitize_text_field($_POST['candidate_nonce']), 'candidate_form_action' ) ) {
        return;
    }

    // Validate captcha
    $user_answer    = intval($_POST['captcha'] ?? -1);
    $correct_answer = intval($_POST['captcha_sum'] ?? -2); // matches the shortcode hidden field

    if ( $user_answer !== $correct_answer ) {
        wp_safe_redirect( add_query_arg( 'captcha_error', 'true', wp_get_referer() ?: home_url() ) );
        exit;
    }

    

    global $wpdb;
    $table_name = $wpdb->prefix . 'candidates';

    // Sanitize fields
    $first_name  = sanitize_text_field($_POST['first_name'] ?? '');
    $last_name   = sanitize_text_field($_POST['last_name'] ?? '');
    $other_names = sanitize_text_field($_POST['other_names'] ?? '');
    $age         = intval($_POST['age'] ?? 0);
    $address1    = sanitize_text_field($_POST['address1'] ?? '');
    $address2    = sanitize_text_field($_POST['address2'] ?? '');
    $city        = sanitize_text_field($_POST['city'] ?? '');
    $state       = sanitize_text_field($_POST['state'] ?? '');
    $zip         = sanitize_text_field($_POST['zip'] ?? '');
    $phone       = sanitize_text_field($_POST['phone'] ?? '');
    $email       = sanitize_email($_POST['email'] ?? '');
    $position    = sanitize_text_field($_POST['position'] ?? '');

    // Handle uploads
    $license_file = '';
    if ( ! empty($_FILES['license_file']['name']) ) {
        $license_file = handle_file_upload($_FILES['license_file']);
    }

    $resume_file = '';
    if ( ! empty($_FILES['resume_file']['name']) ) {
        $resume_file = handle_file_upload($_FILES['resume_file'], true);
    }

    // Save candidate
    $wpdb->insert(
        $table_name,
        [
            'first_name'   => $first_name,
            'last_name'    => $last_name,
            'other_names'  => $other_names,
            'age'          => $age,
            'address1'     => $address1,
            'address2'     => $address2,
            'city'         => $city,
            'state'        => $state,
            'zip'          => $zip,
            'phone'        => $phone,
            'email'        => $email,
            'position'     => $position,
            'license_file' => $license_file,
            'resume_file'  => $resume_file,
        ],
        [
            '%s','%s','%s','%d','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s'
        ]
    );

    // Debug: show DB error if it fails
    if ( $wpdb->last_error ) {
        // Log it
        error_log('DB Insert Error: ' . $wpdb->last_error);

        // Also show it on screen (for debugging only)
        wp_die('Database Insert Error: ' . esc_html($wpdb->last_error));
    }

    // ✅ Redirect with "submitted=1" for success message
    wp_safe_redirect( add_query_arg( 'submitted', '1', wp_get_referer() ?: home_url() ) );
    exit;
}

// Logged-in users
add_action('admin_post_candidate_form', __NAMESPACE__ . '\\handle_candidate_form_submission');

// Not logged-in users
add_action('admin_post_nopriv_candidate_form', __NAMESPACE__ . '\\handle_candidate_form_submission');

/**
 * Handle file uploads with validation.
 */
function handle_file_upload(array $file, bool $required = false): string {
    if ( empty($file['name']) ) {
        return '';
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';

    $allowed = [ 'pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png' ];
    $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if ( ! in_array($ext, $allowed, true) ) {
        return '';
    }

    $upload = wp_handle_upload($file, [ 'test_form' => false ]);

    return $upload['url'] ?? '';
}
