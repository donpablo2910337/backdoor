<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

class CPanelBrowser {
    private $baseUrl;
    private $auth;
    private $currentDir;
    
    public function __construct($domain, $username, $apiToken, $currentDir = '') {
        $this->baseUrl = "https://{$domain}:2083";
        $this->auth = "cpanel {$username}:{$apiToken}";
        $this->currentDir = $currentDir ?: "/home/{$username}/public_html";
    }

    public function testConnection() {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->baseUrl . '/execute/Fileman/list_files?dir=' . urlencode($this->currentDir),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER => ['Authorization: ' . $this->auth]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return $httpCode === 200;
    }

    public function listDir() {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->baseUrl . '/execute/Fileman/list_files?dir=' . urlencode($this->currentDir),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER => ['Authorization: ' . $this->auth]
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true);
    }

    public function uploadFile($file) {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            throw new Exception('Invalid file upload');
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->baseUrl . '/execute/Fileman/upload_files',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER => ['Authorization: ' . $this->auth],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => [
                'dir' => $this->currentDir,
                'file' => new CURLFile($file['tmp_name'], $file['type'], $file['name'])
            ]
        ]);

        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true);
    }

    public function viewFile($filename) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->baseUrl . '/execute/Fileman/get_file_content?dir=' . urlencode($this->currentDir) . '&file=' . urlencode($filename),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER => ['Authorization: ' . $this->auth]
        ]);
        
        $response = curl_exec($ch);
        $result = json_decode($response, true);
        
        if (isset($result['data']['content'])) {
            return $result['data']['content'];
        } elseif (isset($result['data'])) {
            return $result['data'];
        }
        return 'Unable to read file content';
    }

    public function createFile($filename, $content = '') {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->baseUrl . '/execute/Fileman/save_file_content',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER => ['Authorization: ' . $this->auth],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'dir' => $this->currentDir,
                'file' => $filename,
                'content' => $content
            ])
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true);
    }

    public function createFolder($foldername) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->baseUrl . '/json-api/cpanel',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER => ['Authorization: ' . $this->auth],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'cpanel_jsonapi_module' => 'Fileman',
                'cpanel_jsonapi_func' => 'mkdir',
                'path' => $this->currentDir,
                'name' => $foldername
            ])
        ]);

        $response = curl_exec($ch);
        if ($response === false) {
            throw new Exception('cURL error: ' . curl_error($ch));
        }

        curl_close($ch);

        $result = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('JSON decode error: ' . json_last_error_msg());
        }

        if (!isset($result['cpanelresult']['event']['result']) || !$result['cpanelresult']['event']['result']) {
            throw new Exception('API error: ' . (isset($result['cpanelresult']['errors'][0]) ? $result['cpanelresult']['errors'][0] : 'Unknown error'));
        }
        return $result;
    }

    public function deleteFile($filename) {
        $sourcefiles = $this->currentDir . '/' . $filename;
        if (empty($this->currentDir) || strpos($sourcefiles, '/home/') !== 0) {
            throw new Exception('Invalid path: ' . $sourcefiles);
        }

        // Periksa apakah file ada
        $contents = $this->listDir();
        $fileExists = false;
        if (!empty($contents['data'])) {
            foreach ($contents['data'] as $item) {
                if ($item['file'] === $filename && $item['type'] !== 'dir') {
                    $fileExists = true;
                    break;
                }
            }
        }
        if (!$fileExists) {
            throw new Exception('File does not exist: ' . $sourcefiles);
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->baseUrl . '/json-api/cpanel',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER => ['Authorization: ' . $this->auth],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'cpanel_jsonapi_module' => 'Fileman',
                'cpanel_jsonapi_func' => 'fileop',
                'cpanel_jsonapi_apiversion' => 2,
                'filelist' => 1,
                'multiform' => 1,
                'doubledecode' => 0,
                'op' => 'trash',
                'metadata' => '[object Object]',
                'sourcefiles' => $sourcefiles
            ])
        ]);

        $response = curl_exec($ch);
        if ($response === false) {
            throw new Exception('cURL error: ' . curl_error($ch));
        }
        curl_close($ch);

        $result = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('JSON decode error: ' . json_last_error_msg());
        }

        $eventResult = isset($result['cpanelresult']['event']['result']) && $result['cpanelresult']['event']['result'];
        $dataResult = (isset($result['cpanelresult']['data'][0]['result']) && $result['cpanelresult']['data'][0]['result']) ||
                      (isset($result['cpanelresult']['data']['result']) && $result['cpanelresult']['data']['result']);

        if (!$eventResult || !$dataResult) {
            $errorMessage = isset($result['cpanelresult']['error']) ? $result['cpanelresult']['error'] :
                            (isset($result['cpanelresult']['data']['reason']) ? $result['cpanelresult']['data']['reason'] : 'Unknown error');
            throw new Exception('API error: ' . $errorMessage);
        }

        return $result;
    }

    public function deleteFolder($foldername) {
        $sourcefiles = $this->currentDir . '/' . $foldername;
        if (empty($this->currentDir) || strpos($sourcefiles, '/home/') !== 0) {
            throw new Exception('Invalid path: ' . $sourcefiles);
        }

        // Periksa apakah folder ada
        $contents = $this->listDir();
        $folderExists = false;
        if (!empty($contents['data'])) {
            foreach ($contents['data'] as $item) {
                if ($item['file'] === $foldername && $item['type'] === 'dir') {
                    $folderExists = true;
                    break;
                }
            }
        }
        if (!$folderExists) {
            throw new Exception('Folder does not exist: ' . $sourcefiles);
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->baseUrl . '/json-api/cpanel',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER => ['Authorization: ' . $this->auth],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'cpanel_jsonapi_module' => 'Fileman',
                'cpanel_jsonapi_func' => 'fileop',
                'cpanel_jsonapi_apiversion' => 2,
                'filelist' => 1,
                'multiform' => 1,
                'doubledecode' => 0,
                'op' => 'trash',
                'metadata' => '[object Object]',
                'sourcefiles' => $sourcefiles
            ])
        ]);

        $response = curl_exec($ch);
        if ($response === false) {
            throw new Exception('cURL error: ' . curl_error($ch));
        }
        curl_close($ch);

        $result = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('JSON decode error: ' . json_last_error_msg());
        }

        $eventResult = isset($result['cpanelresult']['event']['result']) && $result['cpanelresult']['event']['result'];
        $dataResult = (isset($result['cpanelresult']['data'][0]['result']) && $result['cpanelresult']['data'][0]['result']) ||
                      (isset($result['cpanelresult']['data']['result']) && $result['cpanelresult']['data']['result']);

        if (!$eventResult || !$dataResult) {
            $errorMessage = isset($result['cpanelresult']['error']) ? $result['cpanelresult']['error'] :
                            (isset($result['cpanelresult']['data']['reason']) ? $result['cpanelresult']['data']['reason'] : 'Unknown error');
            throw new Exception('API error: ' . $errorMessage);
        }

        return $result;
    }

    public function createFTPAccount($username, $password, $quota = 0, $directory = '') {
        if (empty($directory)) {
            $directory = $this->currentDir;
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->baseUrl . '/execute/Ftp/add_ftp',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER => ['Authorization: ' . $this->auth],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'user' => $username,
                'pass' => $password,
                'quota' => $quota,
                'homedir' => $directory
            ])
        ]);
        
        $response = curl_exec($ch);
        if ($response === false) {
            throw new Exception('cURL error: ' . curl_error($ch));
        }
        curl_close($ch);
        
        $result = json_decode($response, true);
        if (isset($result['status']) && !$result['status']) {
            throw new Exception('API error: ' . (isset($result['errors'][0]) ? $result['errors'][0] : 'Unknown error'));
        }
        return $result;
    }
}

// Login handling
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    try {
        $browser = new CPanelBrowser(
            $_POST['domain'],
            $_POST['username'],
            $_POST['apiToken']
        );
        
        if ($browser->testConnection()) {
            $_SESSION['cpanel'] = [
                'domain' => $_POST['domain'],
                'username' => $_POST['username'],
                'apiToken' => $_POST['apiToken']
            ];
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $loginError = 'Invalid credentials or connection failed';
        }
    } catch (Exception $e) {
        $loginError = 'Connection error: ' . $e->getMessage();
    }
}

// Logout handling
if (isset($_GET['logout'])) {
    unset($_SESSION['cpanel']);
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Show login form if not logged in
if (!isset($_SESSION['cpanel'])) {
    ?>
    <!DOCTYPE html>
    <html class="dark">
    <head>
        <meta charset="UTF-8">
        <title>cPanel Manager</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <style>
            body {
                background-color: #1f2937;
                color: #e5e7eb;
            }
            .animate-slide-up {
                animation: slideUp 0.3s ease-out;
            }
            @keyframes slideUp {
                from {
                    transform: translateY(20px);
                    opacity: 0;
                }
                to {
                    transform: translateY(0);
                    opacity: 1;
                }
            }
        </style>
    </head>
    <body class="min-h-screen flex items-center justify-center">
        <div class="w-full max-w-md p-8 bg-gray-800 rounded-xl shadow-2xl animate-slide-up">
            <h1 class="text-3xl font-bold mb-8 text-center text-white">cPanel Login</h1>
            
            <?php if (isset($loginError)): ?>
                <div class="mb-6 p-4 bg-red-900/50 text-red-200 rounded-lg">
                    <?php echo htmlspecialchars($loginError); ?>
                </div>
            <?php endif; ?>

            <form method="post" class="space-y-6">
                <div>
                    <label class="block text-gray-300 mb-2 font-medium">Domain</label>
                    <input type="text" name="domain" 
                           class="w-full p-3 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-white placeholder-gray-400 transition-all" 
                           placeholder="example.com" required>
                </div>

                <div>
                    <label class="block text-gray-300 mb-2 font-medium">Username</label>
                    <input type="text" name="username" 
                           class="w-full p-3 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-white placeholder-gray-400 transition-all" 
                           required>
                </div>

                <div>
                    <label class="block text-gray-300 mb-2 font-medium">API Token</label>
                    <input type="password" name="apiToken" 
                           class="w-full p-3 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-white placeholder-gray-400 transition-all" 
                           required>
                </div>

                <button type="submit" name="login" 
                        class="w-full bg-blue-600 text-white p-3 rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-500/50 transition-all font-medium">
                    Login
                </button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Initialize browser with session data
if (isset($_GET['view'])) {
    $currentDir = isset($_GET['dir']) ? $_GET['dir'] : '/home/' . $_SESSION['cpanel']['username'] . '/public_html';
} else {
    $currentDir = $_POST['dir'] ?? '/home/' . $_SESSION['cpanel']['username'] . '/public_html';
}

$browser = new CPanelBrowser(
    $_SESSION['cpanel']['domain'],
    $_SESSION['cpanel']['username'],
    $_SESSION['cpanel']['apiToken'],
    $currentDir
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['file'])) {
        try {
            $result = $browser->uploadFile($_FILES['file']);
            $uploadMessage = $result['status'] ? 'File uploaded successfully' : 'Upload failed: ' . ($result['errors'][0] ?? 'Unknown error');
        } catch (Exception $e) {
            $uploadMessage = 'Error: ' . $e->getMessage();
        }
    } elseif (isset($_POST['action'])) {
        if ($_POST['action'] === 'create' && !empty($_POST['filename'])) {
            try {
                $result = $browser->createFile($_POST['filename'], $_POST['content'] ?? '');
                $createMessage = $result['status'] ? 'File created successfully' : 'Failed to create file: ' . ($result['errors'][0] ?? 'Unknown error');
            } catch (Exception $e) {
                $createMessage = 'Error: ' . $e->getMessage();
            }
        } elseif ($_POST['action'] === 'create_folder' && !empty($_POST['foldername'])) {
            try {
                $result = $browser->createFolder($_POST['foldername']);
                $createMessage = $result['cpanelresult']['event']['result'] ? 'Folder created successfully' : 'Failed to create folder: ' . ($result['cpanelresult']['errors'][0] ?? 'Unknown error');
            } catch (Exception $e) {
                $createMessage = 'Error: ' . $e->getMessage();
            }
        } elseif ($_POST['action'] === 'delete_file' && !empty($_POST['filename'])) {
            try {
                $result = $browser->deleteFile($_POST['filename']);
                $deleteMessage = 'File deleted successfully';
            } catch (Exception $e) {
                $deleteMessage = 'Error: ' . $e->getMessage();
            }
        } elseif ($_POST['action'] === 'delete_folder' && !empty($_POST['foldername'])) {
            try {
                $result = $browser->deleteFolder($_POST['foldername']);
                $deleteMessage = 'Folder deleted successfully';
            } catch (Exception $e) {
                $deleteMessage = 'Error: ' . $e->getMessage();
            }
        } elseif ($_POST['action'] === 'create_ftp') {
            try {
                $result = $browser->createFTPAccount(
                    $_POST['ftp_username'],
                    $_POST['ftp_password'],
                    $_POST['ftp_quota'] ?? 0,
                    $_POST['ftp_directory'] ?? $currentDir
                );
                $ftpMessage = $result['status'] ? 'FTP account created successfully' : 'Failed to create FTP account: ' . ($result['errors'][0] ?? 'Unknown error');
            } catch (Exception $e) {
                $ftpMessage = 'Error: ' . $e->getMessage();
            }
        }
    }
}

$fileContent = '';
$viewingFile = '';
if (isset($_GET['view'])) {
    $fileContent = $browser->viewFile($_GET['view']);
    $viewingFile = $_GET['view'];
}

$contents = $browser->listDir();
?>
<!DOCTYPE html>
<html class="dark">
<head>
    <meta charset="UTF-8">
    <title>cPanel Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background-color: #1f2937;
            color: #e5e7eb;
        }
        .modal {
            transition: all 0.3s ease;
            transform: scale(0.95);
            opacity: 0;
        }
        .modal.show {
            transform: scale(1);
            opacity: 1;
        }
        .file-item:hover {
            background-color: #374151;
            transform: translateX(4px);
            transition: all 0.2s ease;
        }
        textarea {
            background-color: #2d3748;
            color: #e5e7eb;
            border-color: #4b5563;
        }
        input, button {
            transition: all 0.2s ease;
        }
    </style>
</head>
<body class="p-6">
    <div class="max-w-5xl mx-auto">
        <!-- Header with logout -->
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-2xl font-bold text-white">File Manager - <?php echo htmlspecialchars($_SESSION['cpanel']['domain']); ?></h1>
            <a href="?logout" class="bg-red-600 text-white px-5 py-2.5 rounded-lg hover:bg-red-700 focus:ring-4 focus:ring-red-500/50 transition-all">Logout</a>
        </div>

        <!-- Directory navigation -->
        <form method="post" class="mb-8 flex gap-4">
            <input name="dir" class="flex-1 p-3 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-white placeholder-gray-400" value="<?php echo htmlspecialchars($currentDir); ?>">
            <button class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-500/50">List Directory</button>
        </form>

        <!-- Upload form -->
        <form method="post" enctype="multipart/form-data" class="mb-8 p-6 bg-gray-800 rounded-xl shadow-lg">
            <input type="hidden" name="dir" value="<?php echo htmlspecialchars($currentDir); ?>">
            <input type="file" name="file" class="w-full mb-4 p-3 bg-gray-700 border border-gray-600 rounded-lg text-white">
            <button type="submit" class="w-full bg-green-600 text-white p-3 rounded-lg hover:bg-green-700 focus:ring-4 focus:ring-green-500/50">Upload File</button>
        </form>

        <!-- Action buttons -->
        <div class="grid grid-cols-3 gap-4 mb-8">
            <button onclick="showModal()" class="bg-purple-600 text-white p-3 rounded-lg hover:bg-purple-700 focus:ring-4 focus:ring-purple-500/50">New File</button>
            <button onclick="showFolderModal()" class="bg-teal-600 text-white p-3 rounded-lg hover:bg-teal-700 focus:ring-4 focus:ring-teal-500/50">New Folder</button>
            <button onclick="showFTPModal()" class="bg-indigo-600 text-white p-3 rounded-lg hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-500/50">Create FTP Account</button>
        </div>

        <!-- Messages -->
        <?php if (isset($uploadMessage)): ?>
            <div class="mb-6 p-4 bg-blue-900/50 text-blue-200 rounded-lg">
                <?php echo htmlspecialchars($uploadMessage); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($createMessage)): ?>
            <div class="mb-6 p-4 bg-blue-900/50 text-blue-200 rounded-lg">
                <?php echo htmlspecialchars($createMessage); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($deleteMessage)): ?>
            <div class="mb-6 p-4 bg-blue-900/50 text-blue-200 rounded-lg">
                <?php echo htmlspecialchars($deleteMessage); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($ftpMessage)): ?>
            <div class="mb-6 p-4 bg-blue-900/50 text-blue-200 rounded-lg">
                <?php echo htmlspecialchars($ftpMessage); ?>
            </div>
        <?php endif; ?>

        <!-- File viewer -->
        <?php if ($fileContent): ?>
            <div class="mb-8">
                <h3 class="font-bold text-lg mb-3 text-white">Viewing: <?php echo htmlspecialchars($viewingFile); ?></h3>
                <textarea class="w-full h-96 p-4 font-mono text-sm rounded-lg focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($fileContent); ?></textarea>
            </div>
        <?php endif; ?>

        <!-- File list -->
        <?php 
        if (!empty($contents['data'])) {
            echo '<div class="bg-gray-800 rounded-xl shadow-lg overflow-hidden">';
            foreach ($contents['data'] as $item) {
                echo '<div class="p-4 file-item flex justify-between items-center border-b border-gray-700">';
                echo '<span class="text-gray-200">';
                echo $item['type'] === 'dir' ? '📁 ' : '📄 ';
                echo htmlspecialchars($item['file']);
                echo '</span>';
                echo '<div class="flex gap-3">';
                if ($item['type'] !== 'dir') {
                    echo '<a href="?dir=' . urlencode($currentDir) . '&view=' . urlencode($item['file']) . '" class="text-blue-400 hover:text-blue-300 transition-colors">View</a>';
                    echo '<form method="post" onsubmit="return confirm(\'Are you sure you want to delete this file?\');">';
                    echo '<input type="hidden" name="action" value="delete_file">';
                    echo '<input type="hidden" name="filename" value="' . htmlspecialchars($item['file']) . '">';
                    echo '<button type="submit" class="text-red-400 hover:text-red-300 transition-colors">Delete</button>';
                    echo '</form>';
                } else {
                    echo '<form method="post" onsubmit="return confirm(\'Are you sure you want to delete this folder?\');">';
                    echo '<input type="hidden" name="action" value="delete_folder">';
                    echo '<input type="hidden" name="foldername" value="' . htmlspecialchars($item['file']) . '">';
                    echo '<button type="submit" class="text-red-400 hover:text-red-300 transition-colors">Delete</button>';
                    echo '</form>';
                }
                echo '</div>';
                echo '</div>';
            }
            echo '</div>';
        }
        ?>

        <!-- Create File Modal -->
        <div id="createFileModal" class="hidden fixed inset-0 bg-black/60 flex items-center justify-center">
            <div class="bg-gray-800 rounded-xl p-8 max-w-lg w-full modal">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-white">Create New File</h3>
                    <button onclick="hideModal()" class="text-gray-400 hover:text-gray-200 text-2xl">×</button>
                </div>
                <form method="post" class="space-y-4">
                    <input type="hidden" name="dir" value="<?php echo htmlspecialchars($currentDir); ?>">
                    <input type="hidden" name="action" value="create">
                    <input type="text" name="filename" class="w-full p-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500" placeholder="File name">
                    <textarea name="content" class="w-full h-64 p-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500" placeholder="File content (optional)"></textarea>
                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="hideModal()" class="px-5 py-2.5 bg-gray-700 text-gray-200 rounded-lg hover:bg-gray-600">Cancel</button>
                        <button type="submit" class="px-5 py-2.5 bg-purple-600 text-white rounded-lg hover:bg-purple-700">Create</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Create Folder Modal -->
        <div id="createFolderModal" class="hidden fixed inset-0 bg-black/60 flex items-center justify-center">
            <div class="bg-gray-800 rounded-xl p-8 max-w-lg w-full modal">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-white">Create New Folder</h3>
                    <button onclick="hideFolderModal()" class="text-gray-400 hover:text-gray-200 text-2xl">×</button>
                </div>
                <form method="post" class="space-y-4">
                    <input type="hidden" name="dir" value="<?php echo htmlspecialchars($currentDir); ?>">
                    <input type="hidden" name="action" value="create_folder">
                    <input type="text" name="foldername" class="w-full p-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500" placeholder="Folder name">
                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="hideFolderModal()" class="px-5 py-2.5 bg-gray-700 text-gray-200 rounded-lg hover:bg-gray-600">Cancel</button>
                        <button type="submit" class="px-5 py-2.5 bg-teal-600 text-white rounded-lg hover:bg-teal-700">Create</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Create FTP Modal -->
        <div id="createFTPModal" class="hidden fixed inset-0 bg-black/60 flex items-center justify-center">
            <div class="bg-gray-800 rounded-xl p-8 max-w-lg w-full modal">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-white">Create FTP Account</h3>
                    <button onclick="hideFTPModal()" class="text-gray-400 hover:text-gray-200 text-2xl">×</button>
                </div>
                <form method="post" class="space-y-4">
                    <input type="hidden" name="action" value="create_ftp">
                    <div>
                        <label class="block text-gray-300 mb-2 font-medium">FTP Username</label>
                        <input type="text" name="ftp_username" 
                               class="w-full p-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500"
                               required>
                    </div>
                    <div>
                        <label class="block text-gray-300 mb-2 font-medium">FTP Password</label>
                        <input type="password" name="ftp_password" 
                               class="w-full p-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500"
                               required>
                    </div>
                    <div>
                        <label class="block text-gray-300 mb-2 font-medium">Quota (MB, 0 for unlimited)</label>
                        <input type="number" name="ftp_quota" 
                               class="w-full p-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500"
                               value="0">
                    </div>
                    <div>
                        <label class="block text-gray-300 mb-2 font-medium">Directory</label>
                        <input type="text" name="ftp_directory" 
                               class="w-full p-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500"
                               value="/">
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="hideFTPModal()" 
                                class="px-5 py-2.5 bg-gray-700 text-gray-200 rounded-lg hover:bg-gray-600">Cancel</button>
                        <button type="submit" 
                                class="px-5 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Create FTP Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showModal() {
            const modal = document.getElementById('createFileModal');
            modal.classList.remove('hidden');
            setTimeout(() => modal.querySelector('.modal').classList.add('show'), 10);
        }
        function hideModal() {
            const modal = document.getElementById('createFileModal');
            modal.querySelector('.modal').classList.remove('show');
            setTimeout(() => modal.classList.add('hidden'), 300);
        }
        function showFolderModal() {
            const modal = document.getElementById('createFolderModal');
            modal.classList.remove('hidden');
            setTimeout(() => modal.querySelector('.modal').classList.add('show'), 10);
        }
        function hideFolderModal() {
            const modal = document.getElementById('createFolderModal');
            modal.querySelector('.modal').classList.remove('show');
            setTimeout(() => modal.classList.add('hidden'), 300);
        }
        function showFTPModal() {
            const modal = document.getElementById('createFTPModal');
            modal.classList.remove('hidden');
            setTimeout(() => modal.querySelector('.modal').classList.add('show'), 10);
        }
        function hideFTPModal() {
            const modal = document.getElementById('createFTPModal');
            modal.querySelector('.modal').classList.remove('show');
            setTimeout(() => modal.classList.add('hidden'), 300);
        }
    </script>
</body>
</html>
