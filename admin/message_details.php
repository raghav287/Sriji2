<?php
require_once 'check_session.php';
require_once 'db.php';

if (!isset($_GET['id'])) {
    header("Location: contact_messages.php");
    exit();
}

$id = intval($_GET['id']);
$sql = "SELECT * FROM contact_messages WHERE id = $id";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $message = $result->fetch_assoc();

    // Mark as Read
    if ($message['is_read'] == 0) {
        $conn->query("UPDATE contact_messages SET is_read = 1 WHERE id = $id");
    }
} else {
    echo "Message not found.";
    exit();
}

$page_title = "Message Details";
?>
<!doctype html>
<html lang="en" dir="ltr">

<head>
    <!-- META DATA -->
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=0'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Message Details - GSA Industries Admin</title>
    <!-- BOOTSTRAP CSS -->
    <link id="style" href="assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- STYLE CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
    <!-- Plugins CSS -->
    <link href="assets/css/plugins.css" rel="stylesheet">
    <!--- FONT-ICONS CSS -->
    <link href="assets/css/icons.css" rel="stylesheet">
</head>

<body class="app sidebar-mini ltr light-mode">
    <div class="page">
        <div class="page-main">
            <?php include 'includes/header.php'; ?>
            <?php include 'includes/sidebar.php'; ?>

            <div class="main-content app-content mt-0">
                <div class="side-app">
                    <div class="main-container container-fluid">
                        <div class="page-header">
                            <h1 class="page-title">Message Details</h1>
                            <div>
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="contact_messages.php">Messages</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Details</li>
                                </ol>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 col-xl-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title"><?php echo htmlspecialchars($message['subject']); ?></h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="email-details-content">
                                            <div class="table-responsive">
                                                <table class="table row table-borderless w-100 m-0">
                                                    <tbody class="col-lg-12 col-xl-6 p-0">
                                                        <tr>
                                                            <td><span class="fw-semibold">From:</span>
                                                                <?php echo htmlspecialchars($message['name']); ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td><span class="fw-semibold">Email:</span>
                                                                <?php echo htmlspecialchars($message['email']); ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td><span class="fw-semibold">Phone:</span>
                                                                <?php echo htmlspecialchars($message['phone']); ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td><span class="fw-semibold">Sent:</span>
                                                                <?php echo date('F d, Y h:i A', strtotime($message['created_at'])); ?>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="email-body mt-4">
                                                <p>Hello Admin,</p>
                                                <p><?php echo nl2br(htmlspecialchars($message['message'])); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer">
                                        <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>"
                                            class="btn btn-primary"><i class="fe fe-corner-up-left me-2"></i>Reply</a>
                                        <a href="contact_messages.php" class="btn btn-secondary"><i
                                                class="fe fe-arrow-left me-2"></i>Back</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JQUERY JS -->
    <script src="assets/js/jquery.min.js"></script>
    <!-- BOOTSTRAP JS -->
    <script src="assets/plugins/bootstrap/js/popper.min.js"></script>
    <script src="assets/plugins/bootstrap/js/bootstrap.min.js"></script>
    <!-- SIDEBAR JS -->
    <script src="assets/plugins/sidebar/sidebar.js"></script>
    <!-- SIDE-MENU JS-->
    <script src="assets/plugins/sidemenu/sidemenu.js"></script>
    <!-- PERFECT SCROLLBAR JS-->
    <script src="assets/plugins/p-scroll/perfect-scrollbar.js"></script>
    <script src="assets/plugins/p-scroll/pscroll.js"></script>
    <!-- CUSTOM JS -->
    <script src="assets/js/custom.js"></script>
    <script>
        $(window).on("load", function (e) {
            $("#global-loader").fadeOut("slow");
        })
    </script>
</body>

</html>