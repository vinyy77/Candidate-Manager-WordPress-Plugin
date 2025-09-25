<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

// === Candidate Dashboard Page === //
function candidate_dashboard_menu() {
    add_menu_page(
        "Candidate Applications",
        "Candidates",
        "manage_options",
        "candidate-dashboard",
        "candidate_dashboard_page",
        "dashicons-id-alt",
        26
    );
}
add_action("admin_menu", "candidate_dashboard_menu");

// === Enqueue Styles & Scripts for Dashboard === //
function candidate_dashboard_assets($hook) {
    if ($hook !== 'toplevel_page_candidate-dashboard') {
        return;
    }

    wp_enqueue_style(
        'candidate-dashboard-style',
        plugins_url('assets/css/dashboard.css', dirname(__FILE__)),
        [],
        '1.0.0'
    );

    wp_enqueue_script(
        'candidate-dashboard-script',
        plugins_url('assets/js/dashboard.js', dirname(__FILE__)),
        ['jquery'],
        '1.0.0',
        true
    );
}
add_action('admin_enqueue_scripts', 'candidate_dashboard_assets');

// === Dashboard Page Content === //
function candidate_dashboard_page() {
    global $wpdb;
    $table = $wpdb->prefix . "candidates";

    // Handle delete
    if (isset($_GET['delete']) && check_admin_referer('candidate_delete_' . intval($_GET['delete']))) {
        $wpdb->delete($table, ["id" => intval($_GET['delete'])]);
        echo "<div class='notice notice-success is-dismissible'><p>✅ Candidate deleted successfully.</p></div>";
    }

    // Fetch candidates
    $candidates = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC");
    ?>
    <div class="wrap candidate-dashboard">
        <h1 class="page-title">📋 Candidate Applications</h1>
        <table class="candidate-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Position</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($candidates): foreach ($candidates as $c): ?>
                <?php
                    // Build full name safely
                    $name_parts = array_filter([
                        $c->first_name ?? '',
                        $c->last_name ?? '',
                        $c->other_names ?? ''
                    ]);
                    $full_name = implode(' ', $name_parts);
                ?>
                <tr>
                    <td><?= esc_html($c->id) ?></td>
                    <td><?= esc_html($full_name) ?></td>
                    <td><?= esc_html($c->email) ?></td>
                    <td><?= esc_html($c->position) ?></td>
                    <td><?= esc_html(date("M d, Y H:i", strtotime($c->created_at))) ?></td>
                    <td>
                        <a href="#" class="btn-view"
                           data-id="<?= esc_attr($c->id) ?>"
                           data-name="<?= esc_attr($full_name) ?>"
                           data-email="<?= esc_attr($c->email) ?>"
                           data-position="<?= esc_attr($c->position) ?>"
                           data-age="<?= esc_attr($c->age) ?>"
                           data-phone="<?= esc_attr($c->phone) ?>"
                           data-address1="<?= esc_attr($c->address1) ?>"
                           data-address2="<?= esc_attr($c->address2) ?>"
                           data-city="<?= esc_attr($c->city) ?>"
                           data-state="<?= esc_attr($c->state) ?>"
                           data-zip="<?= esc_attr($c->zip) ?>"
                           data-license="<?= esc_attr($c->license_file) ?>"
                           data-resume="<?= esc_attr($c->resume_file) ?>">View</a>
                        <a href="<?= wp_nonce_url(admin_url('admin.php?page=candidate-dashboard&delete=' . intval($c->id)), 'candidate_delete_' . intval($c->id)) ?>"
                           onclick="return confirm('Delete this candidate?');"
                           class="btn-delete">Delete</a>
                    </td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="6" style="text-align:center;">No applications found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal -->
    <div id="candidateModal" class="modal-overlay">
        <div class="modal-content">
            <span class="modal-close">&times;</span>
            <h2>Candidate Details</h2>
            <div class="modal-body">
                <p><strong>Full Name:</strong> <span id="m-name"></span></p>
                <p><strong>Email:</strong> <span id="m-email"></span></p>
                <p><strong>Phone:</strong> <span id="m-phone"></span></p>
                <p><strong>Age:</strong> <span id="m-age"></span></p>
                <p><strong>Position:</strong> <span id="m-position"></span></p>
                <p><strong>Address:</strong> <span id="m-address"></span></p>
                <p><strong>City:</strong> <span id="m-city"></span></p>
                <p><strong>State:</strong> <span id="m-state"></span></p>
                <p><strong>Zip:</strong> <span id="m-zip"></span></p>
                <p><strong>Driver License:</strong> <a href="#" id="m-license" target="_blank">View File</a></p>
                <p><strong>Resume:</strong> <a href="#" id="m-resume" target="_blank">View File</a></p>
            </div>
        </div>
    </div>
    <?php
}
