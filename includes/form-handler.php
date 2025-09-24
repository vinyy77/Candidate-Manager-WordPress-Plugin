<?php
function handle_candidate_form_submission() {
    if ( isset($_POST['candidate_nonce']) && wp_verify_nonce($_POST['candidate_nonce'], 'candidate_form_action') ) {
        global $wpdb;
        $table = $wpdb->prefix . "candidates";

        // Collect data
        $full_name = sanitize_text_field($_POST['first_name'] . " " . $_POST['last_name'] . " " . $_POST['other_names']);
        $email = sanitize_email($_POST['email']);
        $phone = sanitize_text_field($_POST['phone']);
        $age = intval($_POST['age']);
        $address1 = sanitize_text_field($_POST['address1']);
        $address2 = sanitize_text_field($_POST['address2']);
        $city = sanitize_text_field($_POST['city']);
        $state = sanitize_text_field($_POST['state']);
        $zip = sanitize_text_field($_POST['zip']);
        $position = sanitize_text_field($_POST['position']);

        // File uploads
        $license_file = "";
        $resume_file = "";

        if ( ! function_exists('wp_handle_upload') ) require_once(ABSPATH . 'wp-admin/includes/file.php');

        if ( !empty($_FILES['license_file']['name']) ) {
            $upload = wp_handle_upload($_FILES['license_file'], array('test_form' => false));
            if ( isset($upload['url']) ) $license_file = $upload['url'];
        }

        if ( !empty($_FILES['resume_file']['name']) ) {
            $upload = wp_handle_upload($_FILES['resume_file'], array('test_form' => false));
            if ( isset($upload['url']) ) $resume_file = $upload['url'];
        }

        // Save to DB
        $wpdb->insert($table, [
            'full_name' => $full_name,
            'email' => $email,
            'phone' => $phone,
            'age' => $age,
            'address1' => $address1,
            'address2' => $address2,
            'city' => $city,
            'state' => $state,
            'zip' => $zip,
            'position' => $position,
            'license_file' => $license_file,
            'resume_file' => $resume_file
        ]);

        // Send email notification
        wp_mail("applications@giglinkus.com", "New Candidate Application", "A new candidate has applied: $full_name, $email");

        wp_redirect(add_query_arg('submitted', 'true', wp_get_referer()));
        exit;
    }
}
add_action('init', 'handle_candidate_form_submission');
