<?php
function candidate_form_shortcode() {
    ob_start(); ?>
    
    <?php if ( isset($_GET['submitted']) ): ?>
        <p class="success-msg">✅ Thank you! Your application has been submitted.</p>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="candidate-form">
        <?php wp_nonce_field('candidate_form_action', 'candidate_nonce'); ?>

        <label>First Name</label>
        <input type="text" name="first_name" required>

        <label>Last Name</label>
        <input type="text" name="last_name" required>

        <label>Other Names</label>
        <input type="text" name="other_names">

        <label>Age</label>
        <input type="number" name="age">

        <label>Address Line 1</label>
        <input type="text" name="address1">

        <label>Address Line 2</label>
        <input type="text" name="address2">

        <label>City</label>
        <input type="text" name="city">

        <label>State</label>
        <input type="text" name="state">

        <label>Zip Code</label>
        <input type="text" name="zip">

        <label>Phone</label>
        <input type="text" name="phone">

        <label>Email</label>
        <input type="email" name="email" required>

        <label>Choose From Available Positions</label>
        <select name="position">
            <option value="Driver">Driver</option>
            <option value="Sales">Sales</option>
            <option value="Technician">Technician</option>
        </select>

        <label>Driver License</label>
        <input type="file" name="license_file">

        <label>Resume</label>
        <input type="file" name="resume_file">

        <!-- Math puzzle security -->
        <label>Solve this: <span id="puzzle"></span></label>
        <input type="number" name="captcha" id="captcha" required>

        <button type="submit">Submit Application</button>
    </form>
    
    <?php return ob_get_clean();
}
add_shortcode('candidate_form', 'candidate_form_shortcode');
