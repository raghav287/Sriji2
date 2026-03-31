<div class="app-header header sticky">
    <div class="container-fluid main-container">
        <div class="d-flex">
            <a aria-label="Hide Sidebar" class="app-sidebar__toggle" data-bs-toggle="sidebar"
                href="javascript:void(0)"></a>
            <!-- sidebar-toggle-->
            <a class="logo-horizontal " href="index.php">
                <img src="assets/images/brand/logo-white.png" class="header-brand-img desktop-logo" alt="logo">
                <img src="assets/images/brand/logo-dark.png" class="header-brand-img light-logo1" alt="logo">
            </a>

            <div class="d-flex order-lg-2 ms-auto header-right-icons">
                <!-- SEARCH -->
                <button class="navbar-toggler navresponsive-toggler d-lg-none ms-auto" type="button"
                    data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent-4"
                    aria-controls="navbarSupportedContent-4" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon fe fe-more-vertical"></span>
                </button>
                <div class="navbar navbar-collapse responsive-navbar p-0">
                    <div class="collapse navbar-collapse" id="navbarSupportedContent-4">
                        <div class="d-flex order-lg-2">
                            <div class="d-flex">
                                <a class="nav-link icon theme-layout nav-link-bg layout-setting">
                                    <span class="dark-layout"><i class="fe fe-moon"></i></span>
                                    <span class="light-layout"><i class="fe fe-sun"></i></span>
                                </a>
                            </div>
                            <!-- Theme-Layout -->

                            <div class="dropdown d-flex">
                                <a class="nav-link icon full-screen-link nav-link-bg">
                                    <i class="fe fe-minimize fullscreen-button"></i>
                                </a>
                            </div>
                            <!-- FULL-SCREEN -->

                            <!-- NOTIFICATIONS -->
                            <div class="dropdown d-flex notifications">
                                <a class="nav-link icon" data-bs-toggle="dropdown">
                                    <i class="fe fe-bell"></i>
                                    <?php
                                    $sql_count = "SELECT COUNT(*) as count FROM notifications WHERE is_read = 0";
                                    $res_count = $conn->query($sql_count);
                                    $unread_count = 0;
                                    if ($res_count && $row_count = $res_count->fetch_assoc()) {
                                        $unread_count = $row_count['count'];
                                    }

                                    if ($unread_count > 0) {
                                        echo '<span class="badge bg-secondary header-badge">' . $unread_count . '</span>';
                                    }
                                    ?>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                                    <div class="drop-heading border-bottom">
                                        <div class="d-flex">
                                            <h6 class="mt-1 mb-0 fs-16 fw-semibold text-dark">Notifications
                                                <?php if ($unread_count > 0)
                                                    echo '<span class="badge bg-secondary fs-14 ms-auto">' . $unread_count . ' New</span>'; ?>
                                            </h6>
                                        </div>
                                    </div>
                                    <div class="notifications-menu" style="max-height: 300px; overflow-y: auto;">
                                        <?php
                                        $sql_notif = "SELECT * FROM notifications WHERE is_read = 0 ORDER BY created_at DESC LIMIT 50";
                                        $result_notif = $conn->query($sql_notif);
                                        if ($result_notif && $result_notif->num_rows > 0) {
                                            while ($notif = $result_notif->fetch_assoc()) {
                                                $icon_bg = 'bg-primary';
                                                $icon_class = 'fe-mail';

                                                if ($notif['type'] == 'order') {
                                                    $icon_bg = 'bg-success';
                                                    $icon_class = 'fe-shopping-cart';
                                                } elseif ($notif['type'] == 'cancellation') {
                                                    $icon_bg = 'bg-danger';
                                                    $icon_class = 'fe-x-circle';
                                                } elseif ($notif['type'] == 'contact') {
                                                    $icon_bg = 'bg-info';
                                                    $icon_class = 'fe-message-square';
                                                }

                                                echo '<a class="dropdown-item d-flex" href="notification_handler.php?id=' . $notif['id'] . '">
                                                        <div class="me-3 notifyimg  ' . $icon_bg . ' brround box-shadow-' . str_replace('bg-', '', $icon_bg) . '">
                                                            <i class="fe ' . $icon_class . '"></i>
                                                        </div>
                                                        <div class="mt-1 wd-80p">
                                                            <h5 class="notification-label mb-1">' . htmlspecialchars($notif['message']) . '</h5>
                                                            <span class="notification-subtext">' . date('M d, H:i', strtotime($notif['created_at'])) . '</span>
                                                        </div>
                                                    </a>';
                                            }
                                        } else {
                                            echo '<p class="text-center p-3 text-muted">No new notifications</p>';
                                        }
                                        ?>
                                    </div>
                                    <div class="dropdown-divider m-0"></div>
                                    <!-- <a href="notify-list.html" class="dropdown-item text-center p-3 text-muted">View all Notification</a> -->
                                </div>
                            </div>
                            <!-- NOTIFICATIONS -->

                            <div class="dropdown d-flex profile-1">

                                <a href="javascript:void(0)" data-bs-toggle="dropdown"
                                    class="nav-link icon full-screen-link nav-link-bg">
                                    <i class="fe fe-user"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                                    <div class="drop-heading">
                                        <div class="text-center">
                                            <h5 class="text-dark mb-0 fs-14 fw-semibold">
                                                <?php echo isset($_SESSION['admin_email']) ? htmlspecialchars($_SESSION['admin_email']) : 'Admin'; ?>
                                            </h5>
                                            <small class="text-muted">Administrator</small>
                                        </div>
                                    </div>
                                    <div class="dropdown-divider m-0"></div>
                                    <a class="dropdown-item" href="logout.php">
                                        <i class="dropdown-icon fe fe-alert-circle"></i> Sign out
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>