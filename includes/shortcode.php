<?php
function candidate_form_shortcode() {
    ob_start(); ?>
    
    <?php if ( isset($_GET['submitted']) ): ?>
        <div class="form-success">✅ Thank you! Your application has been submitted.</div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="candidate-form">
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

        <button type="submit" class="btn-submit">Submit Application</button>
    </form>
    
    <?php return ob_get_clean();
}
add_shortcode('candidate_form', 'candidate_form_shortcode');
