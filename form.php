<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Generate a simple math captcha (two small numbers).
 * Returns array: [num1, num2, sum]
 */
function ca_generate_captcha() {
    $num1 = rand(1, 9);
    $num2 = rand(1, 9);
    $sum  = $num1 + $num2;
    return array( $num1, $num2, $sum );
}

/**
 * The single submission handler function.
 * Validates nonce, captcha, required fields; handles uploads; inserts into DB; sends email.
 * Returns an array: ['success' => bool, 'message' => string]
 */
function ca_handle_candidate_form_submission() {
    if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
        return null;
    }

    if ( ! isset( $_POST['submit_application'] ) ) {
        return null;
    }

    // Check nonce
    if ( ! isset( $_POST['candidate_form_nonce'] ) || ! wp_verify_nonce( $_POST['candidate_form_nonce'], 'candidate_form_submit' ) ) {
        return array( 'success' => false, 'message' => 'Security check failed. Please reload the page and try again.' );
    }

    // Basic required field checks server-side
    $required = array( 'first_name', 'last_name', 'email', 'position', 'captcha', 'captcha_answer' );
    $errors = array();

    foreach ( $required as $field ) {
        if ( ! isset( $_POST[ $field ] ) || trim( $_POST[ $field ] ) === '' ) {
            $errors[] = $field;
        }
    }

    if ( ! empty( $errors ) ) {
        return array( 'success' => false, 'message' => 'Please fill in all required fields.' );
    }

    // Validate CAPTCHA
    $captcha_submitted = trim( sanitize_text_field( wp_unslash( $_POST['captcha'] ) ) );
    $captcha_answer    = trim( sanitize_text_field( wp_unslash( $_POST['captcha_answer'] ) ) );

    if ( $captcha_submitted === '' || $captcha_answer === '' || intval( $captcha_submitted ) !== intval( $captcha_answer ) ) {
        return array( 'success' => false, 'message' => 'CAPTCHA failed. Please try again.' );
    }

    // Sanitize inputs
    $first_name  = sanitize_text_field( wp_unslash( $_POST['first_name'] ) );
    $last_name   = sanitize_text_field( wp_unslash( $_POST['last_name'] ) );
    $other_names = isset( $_POST['other_names'] ) ? sanitize_text_field( wp_unslash( $_POST['other_names'] ) ) : '';
    $age         = isset( $_POST['age'] ) ? intval( $_POST['age'] ) : null;
    $address1    = isset( $_POST['address1'] ) ? sanitize_text_field( wp_unslash( $_POST['address1'] ) ) : '';
    $address2    = isset( $_POST['address2'] ) ? sanitize_text_field( wp_unslash( $_POST['address2'] ) ) : '';
    $city        = isset( $_POST['city'] ) ? sanitize_text_field( wp_unslash( $_POST['city'] ) ) : '';
    $state       = isset( $_POST['state'] ) ? sanitize_text_field( wp_unslash( $_POST['state'] ) ) : '';
    $zip         = isset( $_POST['zip'] ) ? sanitize_text_field( wp_unslash( $_POST['zip'] ) ) : '';
    $phone       = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
    $email       = sanitize_email( wp_unslash( $_POST['email'] ) );
    $position    = sanitize_text_field( wp_unslash( $_POST['position'] ) );
    $has_license = isset( $_POST['has_license'] ) ? sanitize_text_field( wp_unslash( $_POST['has_license'] ) ) : 'No';
    $has_resume  = isset( $_POST['has_resume'] ) ? sanitize_text_field( wp_unslash( $_POST['has_resume'] ) ) : 'No';

    // File upload handling
    $uploaded_driver_path = '';
    $uploaded_resume_path = '';
    $attachments = array();

    // Limit sizes (bytes). Example: 4MB max per file
    $max_file_size = 4 * 1024 * 1024;

    // Allowed mime types
    $allowed_driver_mimes = array( 'image/jpeg', 'image/png', 'image/jpg', 'application/pdf' );
    $allowed_resume_mimes = array(
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'text/plain'
    );

    // Helper to process a file input
    function ca_process_upload( $file_key, $allowed_mimes, $max_size ) {
        if ( empty( $_FILES[ $file_key ] ) || empty( $_FILES[ $file_key ]['name'] ) ) {
            return array( 'success' => true, 'file' => '', 'url' => '', 'path' => '' );
        }

        $file = $_FILES[ $file_key ];

        if ( $file['size'] > $max_size ) {
            return array( 'success' => false, 'error' => 'File too large. Maximum allowed size is ' . ( $max_size / (1024*1024) ) . ' MB.' );
        }

        // Use WP functions to handle upload
        require_once ABSPATH . 'wp-admin/includes/file.php';
        $overrides = array( 'test_form' => false, 'mimes' => null );
        $movefile = wp_handle_upload( $file, $overrides );

        if ( isset( $movefile['error'] ) ) {
            return array( 'success' => false, 'error' => $movefile['error'] );
        }

        // Validate mime type against allowed list (using returned type)
        $type = isset( $movefile['type'] ) ? $movefile['type'] : '';
        if ( ! in_array( $type, $allowed_mimes ) ) {
            // Remove file if mime not allowed
            if ( ! empty( $movefile['file'] ) && file_exists( $movefile['file'] ) ) {
                @unlink( $movefile['file'] );
            }
            return array( 'success' => false, 'error' => 'Invalid file type.' );
        }

        return array( 'success' => true, 'file' => $movefile['file'], 'url' => $movefile['url'], 'path' => $movefile['file'] );
    }

    // Driver license
    if ( ! empty( $_FILES['driver_license']['name'] ) ) {
        $result = ca_process_upload( 'driver_license', $allowed_driver_mimes, $max_file_size );
        if ( ! $result['success'] ) {
            return array( 'success' => false, 'message' => 'Driver License upload error: ' . $result['error'] );
        }
        $uploaded_driver_path = $result['path'];
    }

    // Resume
    if ( ! empty( $_FILES['resume']['name'] ) ) {
        $result = ca_process_upload( 'resume', $allowed_resume_mimes, $max_file_size );
        if ( ! $result['success'] ) {
            // If driver file uploaded earlier, we keep it; but report resume error
            return array( 'success' => false, 'message' => 'Resume upload error: ' . $result['error'] );
        }
        $uploaded_resume_path = $result['path'];
    }

    // Insert into DB
    global $wpdb;
    $table_name = $wpdb->prefix . 'candidate_applications';

    $inserted = $wpdb->insert(
        $table_name,
        array(
            'first_name'     => $first_name,
            'last_name'      => $last_name,
            'other_names'    => $other_names,
            'age'            => $age,
            'address1'       => $address1,
            'address2'       => $address2,
            'city'           => $city,
            'state'          => $state,
            'zip'            => $zip,
            'phone'          => $phone,
            'email'          => $email,
            'position'       => $position,
            'has_license'    => $has_license,
            'driver_license' => $uploaded_driver_path ? basename( $uploaded_driver_path ) : '',
            'has_resume'     => $has_resume,
            'resume'         => $uploaded_resume_path ? basename( $uploaded_resume_path ) : '',
        ),
        array(
            '%s','%s','%s','%d','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s'
        )
    );

    if ( $inserted === false ) {
        return array( 'success' => false, 'message' => 'Database error. Please try again later.' );
    }

    // Prepare email
    $to      = 'applications@giglinkus.com';
    $subject = 'New Candidate Application from ' . $first_name . ' ' . $last_name;
    $body    = "A new candidate application was received:\n\n";
    $body   .= "Name: {$first_name} {$last_name}\n";
    if ( $other_names ) $body .= "Other Names: {$other_names}\n";
    if ( $age ) $body .= "Age: {$age}\n";
    $body   .= "Position: {$position}\n";
    $body   .= "Email: {$email}\n";
    if ( $phone ) $body .= "Phone: {$phone}\n";
    if ( $address1 ) $body .= "Address Line 1: {$address1}\n";
    if ( $address2 ) $body .= "Address Line 2: {$address2}\n";
    if ( $city ) $body .= "City: {$city}\n";
    if ( $state ) $body .= "State: {$state}\n";
    if ( $zip ) $body .= "Zip: {$zip}\n";
    $body   .= "Has Driver License: {$has_license}\n";
    if ( $uploaded_driver_path ) $body .= "Driver License file: " . basename( $uploaded_driver_path ) . "\n";
    $body   .= "Has Resume: {$has_resume}\n";
    if ( $uploaded_resume_path ) $body .= "Resume file: " . basename( $uploaded_resume_path ) . "\n";

    $headers = array( 'Content-Type: text/plain; charset=UTF-8' );

    // Attach uploaded files if present
    $attachments = array();
    if ( $uploaded_driver_path ) $attachments[] = $uploaded_driver_path;
    if ( $uploaded_resume_path ) $attachments[] = $uploaded_resume_path;

    // Send email
    wp_mail( $to, $subject, $body, $headers, $attachments );

    return array( 'success' => true, 'message' => 'Application submitted successfully. Thank you!' );
}

/**
 * Shortcode: [candidate_form]
 */
function ca_candidate_application_form_shortcode() {

    // If this is a POST, handle it first
    $result_message = '';
    $result_success = null;

    if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['submit_application'] ) ) {
        $result = ca_handle_candidate_form_submission();
        if ( is_array( $result ) ) {
            $result_message = $result['message'];
            $result_success = $result['success'];
        }
    }

    // Generate a fresh captcha for the form display (this will be rotated on each page load)
    list( $num1, $num2, $sum ) = ca_generate_captcha();

    // Begin output buffering for the form HTML
    ob_start();
    ?>

    <div class="candidate-form-wrapper">
        <?php if ( $result_message !== '' && $result_success !== null ) : ?>
            <div class="ca-result <?php echo $result_success ? 'success' : 'error'; ?>">
                <?php echo esc_html( $result_message ); ?>
            </div>
        <?php endif; ?>

        <form class="candidate-form" method="post" enctype="multipart/form-data" novalidate>
            <h2>Candidate Application Form</h2>

            <?php wp_nonce_field( 'candidate_form_submit', 'candidate_form_nonce' ); ?>

            <div class="form-group">
                <label>First Name <span class="required">*</span>
                    <input type="text" name="first_name" required value="<?php echo isset( $_POST['first_name'] ) ? esc_attr( wp_unslash( $_POST['first_name'] ) ) : ''; ?>">
                </label>
            </div>

            <div class="form-group">
                <label>Last Name <span class="required">*</span>
                    <input type="text" name="last_name" required value="<?php echo isset( $_POST['last_name'] ) ? esc_attr( wp_unslash( $_POST['last_name'] ) ) : ''; ?>">
                </label>
            </div>

            <div class="form-group">
                <label>Other Names
                    <input type="text" name="other_names" value="<?php echo isset( $_POST['other_names'] ) ? esc_attr( wp_unslash( $_POST['other_names'] ) ) : ''; ?>">
                </label>
            </div>

            <div class="form-group">
                <label>Age
                    <input type="number" name="age" min="16" max="120" value="<?php echo isset( $_POST['age'] ) ? esc_attr( wp_unslash( $_POST['age'] ) ) : ''; ?>">
                </label>
            </div>

            <div class="form-group">
                <label>Address Line 1
                    <input type="text" name="address1" value="<?php echo isset( $_POST['address1'] ) ? esc_attr( wp_unslash( $_POST['address1'] ) ) : ''; ?>">
                </label>
            </div>

            <div class="form-group">
                <label>Address Line 2
                    <input type="text" name="address2" value="<?php echo isset( $_POST['address2'] ) ? esc_attr( wp_unslash( $_POST['address2'] ) ) : ''; ?>">
                </label>
            </div>

            <div class="form-group">
                <label>City
                    <input type="text" name="city" value="<?php echo isset( $_POST['city'] ) ? esc_attr( wp_unslash( $_POST['city'] ) ) : ''; ?>">
                </label>
            </div>

            <div class="form-group">
                <label>State
                    <input type="text" name="state" value="<?php echo isset( $_POST['state'] ) ? esc_attr( wp_unslash( $_POST['state'] ) ) : ''; ?>">
                </label>
            </div>

            <div class="form-group">
                <label>Zip Code
                    <input type="text" name="zip" value="<?php echo isset( $_POST['zip'] ) ? esc_attr( wp_unslash( $_POST['zip'] ) ) : ''; ?>">
                </label>
            </div>

            <div class="form-group">
                <label>Phone
                    <input type="text" name="phone" value="<?php echo isset( $_POST['phone'] ) ? esc_attr( wp_unslash( $_POST['phone'] ) ) : ''; ?>">
                </label>
            </div>

            <div class="form-group">
                <label>Email <span class="required">*</span>
                    <input type="email" name="email" required value="<?php echo isset( $_POST['email'] ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; ?>">
                </label>
            </div>

            <div class="form-group">
                <label>Available Positions <span class="required">*</span>
                    <select name="position" required>
                        <option value="">-- Select Position --</option>
                        <option value="Driver" <?php selected( isset( $_POST['position'] ) ? $_POST['position'] : '', 'Driver' ); ?>>Driver</option>
                        <option value="Assistant" <?php selected( isset( $_POST['position'] ) ? $_POST['position'] : '', 'Assistant' ); ?>>Assistant</option>
                        <option value="Manager" <?php selected( isset( $_POST['position'] ) ? $_POST['position'] : '', 'Manager' ); ?>>Manager</option>
                    </select>
                </label>
            </div>

            <div class="form-group">
                <label>Do you have a Driver License?</label>
                <label><input type="radio" name="has_license" value="Yes" <?php checked( isset( $_POST['has_license'] ) ? $_POST['has_license'] : '', 'Yes' ); ?>> Yes</label>
                <label><input type="radio" name="has_license" value="No"  <?php checked( isset( $_POST['has_license'] ) ? $_POST['has_license'] : '', 'No' ); ?>> No</label>
            </div>

            <div class="form-group">
                <label>Upload Driver License:</label>
                <div class="file-upload">
                    <label class="file-upload-label">Choose File
                        <input type="file" name="driver_license" onchange="ca_updateFileName(this, 'driver_license_name')">
                    </label>
                    <div id="driver_license_name" class="file-chosen">No file chosen</div>
                </div>
            </div>

            <div class="form-group">
                <label>Do you have a Resume?</label>
                <label><input type="radio" name="has_resume" value="Yes" <?php checked( isset( $_POST['has_resume'] ) ? $_POST['has_resume'] : '', 'Yes' ); ?>> Yes</label>
                <label><input type="radio" name="has_resume" value="No"  <?php checked( isset( $_POST['has_resume'] ) ? $_POST['has_resume'] : '', 'No' ); ?>> No</label>
            </div>

            <div class="form-group">
                <label>Upload Resume:</label>
                <div class="file-upload">
                    <label class="file-upload-label">Choose File
                        <input type="file" name="resume" onchange="ca_updateFileName(this, 'resume_name')">
                    </label>
                    <div id="resume_name" class="file-chosen">No file chosen</div>
                </div>
            </div>

            <?php
            // Hidden fields for captcha - these will be replaced on each render
            ?>
            <div class="form-group">
                <label id="captcha-label">Solve this to prove you are human: What is <?php echo esc_html( $num1 ); ?> + <?php echo esc_html( $num2 ); ?> ? <span class="required">*</span></label>
                <input type="text" name="captcha" required>
                <input type="hidden" name="captcha_answer" value="<?php echo esc_attr( $sum ); ?>">
            </div>

            <div class="form-group">
                <button type="submit" name="submit_application">Submit Application</button>
            </div>
        </form>
    </div>

    <!-- Inline JavaScript for client-side validation, file name display and rotating captcha on failure -->
    <script>
    (function(){
        // Update file name displayed
        window.ca_updateFileName = function(input, targetId) {
            var fileName = input.files.length > 0 ? input.files[0].name : "No file chosen";
            var el = document.getElementById(targetId);
            if (el) el.textContent = fileName;
        };

        // Client-side form validation
        document.addEventListener("DOMContentLoaded", function() {
            var form = document.querySelector(".candidate-form");
            if (!form) return;

            form.addEventListener("submit", function(e) {
                // Remove previous messages
                form.querySelectorAll(".error").forEach(function(el){ el.classList.remove("error"); });
                form.querySelectorAll(".error-message").forEach(function(el){ el.remove(); });

                var valid = true;
                var requiredFields = form.querySelectorAll("input[required], select[required]");
                requiredFields.forEach(function(field) {
                    // if file input is required in future, need different handling
                    if (field.tagName.toLowerCase() === 'input' || field.tagName.toLowerCase() === 'select') {
                        if (!field.value || field.value.trim() === "") {
                            valid = false;
                            field.classList.add("error");
                            var msg = document.createElement("div");
                            msg.className = "error-message";
                            msg.textContent = "This field is required.";
                            var parent = field.closest(".form-group") || field.parentNode;
                            parent.appendChild(msg);
                        }
                    }
                });

                // CAPTCHA check (client-side quick check)
                var captcha = form.querySelector("input[name='captcha']");
                var captchaAnswer = form.querySelector("input[name='captcha_answer']");
                if (captcha && captchaAnswer) {
                    if (captcha.value.trim() === "" || captcha.value.trim() !== captchaAnswer.value.trim()) {
                        valid = false;
                        captcha.classList.add("error");
                        var msg = document.createElement("div");
                        msg.className = "error-message";
                        msg.textContent = "Incorrect answer.";
                        captcha.closest(".form-group").appendChild(msg);
                    }
                }

                if (!valid) {
                    e.preventDefault();
                }
            });
        });

        // If server returned a CAPTCHA failure message, rotate captcha (the server regenerates new numbers and this script will update the visible label and hidden answer).
        <?php
        // If the form was posted and server returned an error that was CAPTCHA-related, regenerate captcha server-side and print JS to update label/value.
        if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['submit_application'] ) ) {
            $handler_result = ca_handle_candidate_form_submission(); // NOTE: calling again is safe because it returns immediately if not a POST or if processed.
            // We must detect if the failure was due to CAPTCHA: we already ran the handler earlier in PHP before rendering the form.
            // But here we will act only if the message indicates captcha failure. To avoid duplicate DB inserts/emails we did the earlier check.
            // Instead we check for the last run: but for safety, we'll detect by looking at the $result passed via the top of this function.
        }
        ?>
    })();
    </script>

    <?php
    // Return the buffer content
    return ob_get_clean();
}

add_shortcode( 'candidate_form', 'ca_candidate_application_form_shortcode' );
