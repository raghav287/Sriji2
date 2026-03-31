<?php
require_once __DIR__ . '/smtp_mailer.php';

function sendOrderConfirmation($conn, $order_id)
{
    // 1. Fetch Order Details
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();

    if (!$order)
        return false;

    // 2. Fetch Order Items
    $items_sql = "SELECT oi.*, p.name as product_name, s.size as variant_size, pv.color as variant_color 
                  FROM order_items oi 
                  LEFT JOIN products p ON oi.product_id = p.id 
                  LEFT JOIN product_variants pv ON oi.variant_id = pv.id
                  LEFT JOIN product_sizes s ON pv.product_size_id = s.id 
                  WHERE oi.order_id = ?";
    $i_stmt = $conn->prepare($items_sql);
    $i_stmt->bind_param("i", $order_id);
    $i_stmt->execute();
    $items_res = $i_stmt->get_result();

    // 3. Prepare Email Content
    $to = $order['customer_email'];
    $subject = "Order Confirmation - #" . $order['order_number'];

    // Generate Track Link
    // Assume HTTP/HTTPS based on server or hardcode if needed. using generic logic
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'srijivastrashingarsewa.com'; // Fallback if CLI
    // If webhook calls this, HTTP_HOST might be correct if webhook is hit via URL.

    $track_url = $protocol . $host . "/track-order.php?order_id=" . $order['order_number'] . "&email=" . urlencode($order['customer_email']);

    $message = '
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; }
            .header { background: #e53637; color: #fff; padding: 10px; text-align: center; }
            .order-details { margin-top: 20px; }
            table { width: 100%; border-collapse: collapse; margin-top: 10px; }
            th, td { padding: 10px; border-bottom: 1px solid #eee; text-align: left; }
            th { background: #f9f9f9; }
            .total { font-weight: bold; font-size: 1.1em; }
            .footer { margin-top: 20px; font-size: 0.9em; color: #777; border-top: 1px solid #eee; padding-top: 10px; }
            .btn { display: inline-block; padding: 10px 20px; background: #e53637; color: #fff; text-decoration: none; border-radius: 5px; margin-top: 10px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h2>Thank You for Your Order!</h2>
            </div>
            <p>Hi ' . htmlspecialchars($order['customer_name']) . ',</p>
            <p>Your order has been placed successfully. Here are the details:</p>
            
            <div class="order-details">
                <p><strong>Order Number:</strong> ' . htmlspecialchars($order['order_number']) . '</p>
                <p><strong>Payment Method:</strong> ' . htmlspecialchars($order['payment_method']) . '</p>
                 <p><strong>Status:</strong> ' . ucfirst($order['status']) . '</p>
                
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Price</th>
                        </tr>
                    </thead>
                    <tbody>';

    while ($item = $items_res->fetch_assoc()) {
        $variant_str = "";
        if (!empty($item['variant_size']))
            $variant_str .= $item['variant_size'];
        if (!empty($item['variant_color']))
            $variant_str .= ($variant_str ? ", " : "") . $item['variant_color'];

        $message .= '<tr>
            <td>' . htmlspecialchars($item['product_name']) . ($variant_str ? ' (' . htmlspecialchars($variant_str) . ')' : '') . '</td>
            <td>' . $item['quantity'] . '</td>
            <td>₹' . number_format($item['price'], 2) . '</td>
        </tr>';
    }

    $message .= '
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2" class="total">Total Amount</td>
                            <td class="total">₹' . number_format($order['total_amount'], 2) . '</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <p style="text-align: center;">
                <a href="' . $track_url . '" class="btn">Track Your Order</a>
            </p>

            <div class="footer">
                <p><strong>Support:</strong></p>
                <p>Email: <a href="mailto:info@srijivastrashingarsewa.com">info@srijivastrashingarsewa.com</a></p>
                <p>Phone: +91 9115936593</p>
                <p>Sriji Vastra Shingar Sewa</p>
            </div>
        </div>
    </body>
    </html>';

    // 4. Send Email
    // Hostinger SMTP Credentials
    $smtp_host = 'smtp.hostinger.com';
    $smtp_port = 465;
    $smtp_user = 'no-reply@srijivastrashingarsewa.com';
    $smtp_pass = 'e$|KaxO1O';

    $mailer = new SMTPMailer($smtp_host, $smtp_port, $smtp_user, $smtp_pass, $smtp_user, 'Sriji Vastra Shingar Sewa');

    return $mailer->send($to, $subject, $message);
}
?>