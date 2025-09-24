<?php
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

// === Dashboard Page Content === //
function candidate_dashboard_page() {
    global $wpdb;
    $table = $wpdb->prefix . "candidates";

    // Handle delete action
    if (isset($_GET['delete'])) {
        $delete_id = intval($_GET['delete']);
        $wpdb->delete($table, ["id" => $delete_id]);
        echo "<div class='notice notice-success'><p>Candidate deleted successfully.</p></div>";
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
                <?php if ($candidates): ?>
                    <?php foreach ($candidates as $candidate): ?>
                        <tr>
                            <td><?php echo esc_html($candidate->id); ?></td>
                            <td><?php echo esc_html($candidate->full_name); ?></td>
                            <td><?php echo esc_html($candidate->email); ?></td>
                            <td><?php echo esc_html($candidate->position); ?></td>
                            <td><?php echo esc_html(date("M d, Y H:i", strtotime($candidate->created_at))); ?></td>
                            <td>
                                <a href="#" 
                                   class="btn-view" 
                                   data-id="<?php echo $candidate->id; ?>"
                                   data-name="<?php echo esc_attr($candidate->full_name); ?>"
                                   data-email="<?php echo esc_attr($candidate->email); ?>"
                                   data-position="<?php echo esc_attr($candidate->position); ?>"
                                   data-age="<?php echo esc_attr($candidate->age); ?>"
                                   data-phone="<?php echo esc_attr($candidate->phone); ?>"
                                   data-address1="<?php echo esc_attr($candidate->address1); ?>"
                                   data-address2="<?php echo esc_attr($candidate->address2); ?>"
                                   data-city="<?php echo esc_attr($candidate->city); ?>"
                                   data-state="<?php echo esc_attr($candidate->state); ?>"
                                   data-zip="<?php echo esc_attr($candidate->zip); ?>"
                                   data-license="<?php echo esc_attr($candidate->license_file); ?>"
                                   data-resume="<?php echo esc_attr($candidate->resume_file); ?>"
                                >View</a>
                                
                                <a href="<?php echo admin_url('admin.php?page=candidate-dashboard&delete=' . $candidate->id); ?>" 
                                   onclick="return confirm('Are you sure you want to delete this candidate?');" 
                                   class="btn-delete">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align:center;">No applications found.</td>
                    </tr>
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

    <style>
        /* Table Styling */
        .candidate-dashboard .page-title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #23282d;
        }

        .candidate-table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 8px rgba(0,0,0,0.08);
        }

        .candidate-table thead {
            background: #0073aa;
            color: #fff;
        }

        .candidate-table th, .candidate-table td {
            padding: 12px 15px;
            text-align: left;
        }

        .candidate-table tr:nth-child(even) {
            background: #f9f9f9;
        }

        .candidate-table tr:hover {
            background: #f1f1f1;
        }

        .btn-view, .btn-delete {
            padding: 6px 12px;
            text-decoration: none;
            font-size: 13px;
            border-radius: 4px;
            margin-right: 6px;
        }

        .btn-view {
            background: #2271b1;
            color: #fff;
        }

        .btn-view:hover {
            background: #135e96;
        }

        .btn-delete {
            background: #d63638;
            color: #fff;
        }

        .btn-delete:hover {
            background: #a82a2b;
        }

        /* Modal Styling */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.6);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: #fff;
            padding: 25px;
            border-radius: 8px;
            width: 500px;
            max-width: 90%;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            animation: fadeIn 0.3s ease-in-out;
        }

        .modal-content h2 {
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 8px;
        }

        .modal-content p {
            margin: 8px 0;
            font-size: 14px;
        }

        .modal-close {
            float: right;
            font-size: 22px;
            cursor: pointer;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const modal = document.getElementById("candidateModal");
        const closeBtn = document.querySelector(".modal-close");

        // Open modal
        document.querySelectorAll(".btn-view").forEach(btn => {
            btn.addEventListener("click", function(e) {
                e.preventDefault();

                document.getElementById("m-name").innerText = this.dataset.name;
                document.getElementById("m-email").innerText = this.dataset.email;
                document.getElementById("m-phone").innerText = this.dataset.phone || "N/A";
                document.getElementById("m-age").innerText = this.dataset.age || "N/A";
                document.getElementById("m-position").innerText = this.dataset.position;
                document.getElementById("m-address").innerText = this.dataset.address1 + " " + (this.dataset.address2 || "");
                document.getElementById("m-city").innerText = this.dataset.city;
                document.getElementById("m-state").innerText = this.dataset.state;
                document.getElementById("m-zip").innerText = this.dataset.zip;
                
                // Files
                document.getElementById("m-license").href = this.dataset.license || "#";
                document.getElementById("m-resume").href = this.dataset.resume || "#";

                modal.style.display = "flex";
            });
        });

        // Close modal
        closeBtn.addEventListener("click", function() {
            modal.style.display = "none";
        });

        window.addEventListener("click", function(e) {
            if (e.target === modal) {
                modal.style.display = "none";
            }
        });
    });
    </script>
    <?php
}
