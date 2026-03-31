<?php
$current_page = basename($_SERVER['PHP_SELF']);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$sidebar_user_image = '../assets/images/dashboard_user_img.jpg'; // Default

if (isset($_SESSION['user_id'])) {
    // Always fetch the latest profile image from DB to ensure updates are reflected immediately
    // even if $user variable exists from parent, it might be stale or missing the new field
    $u_id_sidebar = $_SESSION['user_id'];

    // We use a different variable name to avoid conflict with page-specific $conn or $stmt
    // Assuming $conn is available from parent include. If not, we might need to require db.php
    // checking if $conn exists, if not try to include it.
    if (!isset($conn)) {
        if (file_exists('../admin/db.php'))
            require_once '../admin/db.php';
        else if (file_exists('../../admin/db.php'))
            require_once '../../admin/db.php';
    }

    if (isset($conn) && $conn) {
        $stmt_sidebar = $conn->prepare("SELECT profile_image FROM users WHERE id = ?");
        if ($stmt_sidebar) {
            $stmt_sidebar->bind_param("i", $u_id_sidebar);
            $stmt_sidebar->execute();
            $res_sidebar = $stmt_sidebar->get_result();
            if ($row_sidebar = $res_sidebar->fetch_assoc()) {
                if (!empty($row_sidebar['profile_image'])) {
                    $potential_image = '../assets/uploads/users/' . $row_sidebar['profile_image'];
                    // Optional: Check if file exists roughly? No, expensive. Just believe DB.
                    $sidebar_user_image = $potential_image;
                }
            }
            $stmt_sidebar->close();
        }
    }
}
?>
<div class="dashboard_sidebar">
    <div class="dashboard_sidebar_area">
        <div class="dashboard_sidebar_user">
            <div class="img">
                <img src="<?php echo htmlspecialchars($sidebar_user_image); ?>" alt="dashboard" class="img-fluid w-100"
                    id="sidebar_user_img" style="height: 100px; width: 100px; object-fit: cover; border-radius: 50%;">
                <label for="profile_photo"><i class="far fa-camera"></i></label>
                <input type="file" id="profile_photo" hidden="" accept="image/*">
            </div>
            <h3><?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'User'; ?></h3>
            <p>Welcome back</p>
        </div>
        <div class="dashboard_sidebar_menu">
            <ul>
                <li>
                    <p>Dashboard</p>
                </li>
                <li>
                    <a class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12" />
                            </svg>
                        </span>
                        Overview
                    </a>
                </li>
                <li>
                    <a class="<?php echo $current_page == 'orders.php' ? 'active' : ''; ?>" href="orders.php">
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                            </svg>
                        </span>
                        Orders
                    </a>
                </li>
                <li>
                    <p>Account Settings</p>
                </li>
                <li>
                    <a class="<?php echo $current_page == 'profile.php' ? 'active' : ''; ?>" href="profile.php">
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                            </svg>
                        </span>
                        Personal Info
                    </a>
                </li>
                <li>
                    <a class="<?php echo $current_page == 'address.php' ? 'active' : ''; ?>" href="address.php">
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                            </svg>
                        </span>
                        Address
                    </a>
                </li>
                <li>
                    <a class="<?php echo $current_page == 'change_password.php' ? 'active' : ''; ?>"
                        href="change_password.php">
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                            </svg>
                        </span>
                        Change Password
                    </a>
                </li>
                <li>
                    <a href="logout.php">
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15m-3 0-3-3m0 0 3-3m-3 3H15" />
                            </svg>
                        </span>
                        Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const profileInput = document.getElementById('profile_photo');
        const profileImg = document.getElementById('sidebar_user_img');

        if (profileInput) {
            profileInput.addEventListener('change', function (e) {
                const file = e.target.files[0];
                if (!file) return;

                // Size check: 100KB
                if (file.size > 100 * 1024) {
                    alert('Please select an image smaller than 100KB.');
                    this.value = ''; // Clear input
                    return;
                }

                const formData = new FormData();
                formData.append('profile_photo', file);

                // Fetch upload
                fetch('upload_profile_image_ajax.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            // Update image source with timestamp to force reload
                            profileImg.src = data.image_url + '?t=' + new Date().getTime();
                            // Optional: show toast/notification
                        } else {
                            alert(data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred during upload.');
                    });
            });
        }
    });
</script>