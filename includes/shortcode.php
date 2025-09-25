<?php
declare(strict_types=1);

namespace CandidateManager;

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Render the candidate form HTML and return as string.
 *
 * @return string
 */
function candidate_form_shortcode(): string {
    ob_start();
    ?>
   <?php if ( isset($_GET['submitted']) ): ?>
    <div id="form-success" class="form-success" style="padding:10px; background:#e6ffed; border:1px solid #2ecc71; margin-bottom:15px;">
        ✅ Thank you! Your application has been submitted.
    </div>
    <script>
        setTimeout(() => {
            const el = document.getElementById("form-success");
            if (el) el.style.display = "none";
        }, 5000); // 5 seconds
    </script>
<?php elseif ( isset($_GET['captcha_error']) ): ?>
    <div class="form-error" style="padding:10px; background:#ffe6e6; border:1px solid #e74c3c; margin-bottom:15px;">
        ❌ Incorrect captcha answer. Please try again.
    </div>
<?php elseif ( isset($_GET['db_error']) ): ?>
    <div class="form-error" style="padding:10px; background:#ffe6e6; border:1px solid #e74c3c; margin-bottom:15px;">
        ❌ Sorry, there was a problem saving your application. Please try again later.
    </div>
<?php endif; ?>


    <form method="post" enctype="multipart/form-data" 
      action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" 
      class="candidate-form">

        <input type="hidden" name="action" value="candidate_form">
        <?php wp_nonce_field('candidate_form_action', 'candidate_nonce'); ?>

        <div class="form-row">
            <div class="form-group">
                <label>First Name <span>*</span></label>
                <input type="text" name="first_name" required>
            </div>
            <div class="form-group">
                <label>Last Name <span>*</span></label>
                <input type="text" name="last_name" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Other Names</label>
                <input type="text" name="other_names">
            </div>
            <div class="form-group">
                <label>Age</label>
                <input type="number" name="age" min="18" max="99">
            </div>
        </div>

        <div class="form-group">
            <label>Address Line 1</label>
            <input type="text" name="address1">
        </div>

        <div class="form-group">
            <label>Address Line 2</label>
            <input type="text" name="address2">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>City</label>
                <input type="text" name="city">
            </div>
            <div class="form-group">
                <label>State</label>
                <input type="text" name="state">
            </div>
            <div class="form-group">
                <label>Zip Code</label>
                <input type="text" name="zip">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" name="phone">
            </div>
            <div class="form-group">
                <label>Email <span>*</span></label>
                <input type="email" name="email" required>
            </div>
        </div>

        <div class="form-group">
            <label for="position">Position Applied For</label>
            <select id="position" name="position" required>
                <option value="">-- Select Position --</option>

                <optgroup label="Healthcare">
                    <option value="Doctor">Doctor</option>
                    <option value="Nurse">Nurse</option>
                    <option value="Pharmacist">Pharmacist</option>
                    <option value="Caregiver">Caregiver</option>
                </optgroup>

                <optgroup label="Education">
                    <option value="Teacher">Teacher</option>
                    <option value="Lecturer">Lecturer</option>
                    <option value="Tutor">Tutor</option>
                    <option value="School Administrator">School Administrator</option>
                </optgroup>

                <optgroup label="Information Technology">
                    <option value="Software Developer">Software Developer</option>
                    <option value="Web Designer">Web Designer</option>
                    <option value="IT Support">IT Support</option>
                    <option value="Data Analyst">Data Analyst</option>
                    <option value="Cybersecurity Specialist">Cybersecurity Specialist</option>
                </optgroup>

                <optgroup label="Business & Finance">
                    <option value="Accountant">Accountant</option>
                    <option value="Banker">Banker</option>
                    <option value="Financial Analyst">Financial Analyst</option>
                    <option value="Business Development">Business Development</option>
                    <option value="Customer Service">Customer Service</option>
                </optgroup>

                <optgroup label="Skilled Trades">
                    <option value="Driver">Driver</option>
                    <option value="Electrician">Electrician</option>
                    <option value="Plumber">Plumber</option>
                    <option value="Technician">Technician</option>
                    <option value="Mechanic">Mechanic</option>
                </optgroup>

                <optgroup label="Creative & Media">
                    <option value="Graphic Designer">Graphic Designer</option>
                    <option value="Photographer">Photographer</option>
                    <option value="Content Writer">Content Writer</option>
                    <option value="Social Media Manager">Social Media Manager</option>
                    <option value="Marketing Specialist">Marketing Specialist</option>
                </optgroup>

                <optgroup label="Other">
                    <option value="Intern">Intern</option>
                    <option value="Volunteer">Volunteer</option>
                    <option value="Other">Other</option>
                </optgroup>
            </select>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Driver License</label>
                <input type="file" name="license_file">
            </div>
            <div class="form-group">
                <label>Resume <span>*</span></label>
                <input type="file" name="resume_file" required>
            </div>
        </div>

        <!-- Security Puzzle -->
        <div class="form-group">
            <label>Solve this: <span id="puzzle"></span></label>
            <input type="number" name="captcha" id="captcha" required>
        </div>
        <input type="hidden" name="captcha_sum" id="captcha_sum" value="">

        <button type="submit" class="btn-submit">Submit Application</button>
    </form>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const puzzleEl = document.getElementById("puzzle");
        const hidden   = document.getElementById("captcha_sum");
        const a = Math.floor(Math.random() * 10) + 1;
        const b = Math.floor(Math.random() * 10) + 1;
        puzzleEl.textContent = a + " + " + b;
        hidden.value = a + b;
    });
    </script>
    <?php
    return (string) ob_get_clean();
}

/**
 * Register shortcodes on init.
 */
add_action('init', function (): void {
    $callback = __NAMESPACE__ . '\\candidate_form_shortcode';
    add_shortcode('candidate_form', $callback);
    add_shortcode('candidate-form', $callback);
    add_shortcode('candidate-form-short', $callback);

    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('[CandidateManager] shortcodes registered: candidate_form, candidate-form, candidate-form-short');
    }
});
