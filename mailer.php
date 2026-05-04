<?php
// Email function placeholder - configure with your SMTP settings
function sendEmail($to, $subject, $body) {
    // For InfinityFree, you may need to use PHP mail() or a third-party service
    // This is a placeholder - implement based on your hosting's email capabilities
    
    $headers = "From: noreply@swiftcivic.com\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    // Uncomment when ready to send emails:
    // return mail($to, $subject, $body, $headers);
    
    // For now, just log the email attempt
    error_log("Email would be sent to: $to, Subject: $subject");
    return true;
}

function sendStatusUpdateEmail($user_email, $user_name, $tracking_number, $new_status) {
    $subject = "SwiftCivic - Request Status Update: $tracking_number";
    $body = "
        <html>
        <body>
            <h2>Hello $user_name,</h2>
            <p>Your request <strong>$tracking_number</strong> has been updated to: <strong>$new_status</strong></p>
            <p>Login to your dashboard to view more details.</p>
            <p>Thank you,<br>SwiftCivic Team</p>
        </body>
        </html>
    ";
    
    return sendEmail($user_email, $subject, $body);
}
