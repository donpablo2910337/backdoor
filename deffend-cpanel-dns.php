<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * Helper Functions for cPanel API Integration 
 */
function connectToCpanel($domain, $cpanel_user, $token, $module, $function, $params = []) {
    $url = "https://{$domain}:2083/json-api/cpanel";
    $query = array_merge([
        'cpanel_jsonapi_user' => $cpanel_user,
        'cpanel_jsonapi_apiversion' => '2',
        'cpanel_jsonapi_module' => $module,
        'cpanel_jsonapi_func' => $function,
    ], $params);

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url . '?' . http_build_query($query));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_HTTPHEADER, [
        "Authorization: cpanel {$cpanel_user}:{$token}",
    ]);
    $response = curl_exec($curl);
    $error = curl_error($curl);
    curl_close($curl);

    if ($error) {
        error_log("cPanel API Error: " . $error);
        return ['success' => false, 'message' => $error];
    }

    $data = json_decode($response, true);
    if (isset($data['cpanelresult']['error'])) {
        error_log("cPanel API Error: " . $data['cpanelresult']['error']);
        return ['success' => false, 'message' => $data['cpanelresult']['error']];
    }

    return ['success' => true, 'message' => $data];
}

/** Subdomain Management Functions **/
function getSubdomains($domain, $cpanel_user, $token) {
    $response = connectToCpanel($domain, $cpanel_user, $token, 'SubDomain', 'listsubdomains');
    
    if ($response['success'] && isset($response['message']['cpanelresult']['data'])) {
        foreach ($response['message']['cpanelresult']['data'] as &$subdomain) {
            $subdomain['domain'] = $subdomain['domain'] ?? '';
            $subdomain['dir'] = $subdomain['dir'] ?? '';
        }
    }
    
    return $response;
}

function addSubdomain($domain, $cpanel_user, $token, $subdomain, $rootdomain, $docroot) {
    return connectToCpanel($domain, $cpanel_user, $token, 'SubDomain', 'addsubdomain', [
        'domain' => $subdomain,
        'rootdomain' => $rootdomain,
        'dir' => $docroot,
    ]);
}

function deleteSubdomain($domain, $cpanel_user, $token, $subdomain) {
    return connectToCpanel($domain, $cpanel_user, $token, 'SubDomain', 'delsubdomain', [
        'domain' => $subdomain,
    ]);
}

/** DNS Management Functions **/
function getDNSRecords($domain, $cpanel_user, $token) {
    error_log("Fetching DNS records for domain: " . $domain);
    $response = connectToCpanel($domain, $cpanel_user, $token, 'ZoneEdit', 'fetchzone', ['domain' => $domain]);
    
    if ($response['success'] && isset($response['message']['cpanelresult']['data'][0]['record'])) {
        $records = &$response['message']['cpanelresult']['data'][0]['record'];
        
        // Filter and normalize records
        $records = array_filter($records, function($record) {
            return !empty($record['type']);
        });

        foreach ($records as &$record) {
            // Basic fields
            $record['name'] = $record['name'] ?? '';
            $record['type'] = $record['type'] ?? '';
            $record['ttl'] = $record['ttl'] ?? '14400';
            $record['line'] = $record['line'] ?? '';

            // Type-specific fields
            switch ($record['type']) {
                case 'A':
                case 'AAAA':
                    $record['address'] = $record['address'] ?? '';
                    break;
                case 'CNAME':
                    $record['cname'] = $record['cname'] ?? '';
                    break;
                case 'NS':
                    $record['nsdname'] = $record['nsdname'] ?? $record['address'] ?? '';
                    break;
                case 'MX':
                    $record['preference'] = $record['preference'] ?? '';
                    $record['exchange'] = $record['exchange'] ?? '';
                    break;
                case 'TXT':
                    $record['txt'] = $record['txt'] ?? $record['txtdata'] ?? '';
                    break;
                    break;
                case 'SOA':
                    $record['mname'] = $record['mname'] ?? '';
                    $record['rname'] = $record['rname'] ?? '';
                    $record['serial'] = $record['serial'] ?? '';
                    $record['refresh'] = $record['refresh'] ?? '';
                    $record['retry'] = $record['retry'] ?? '';
                    $record['expire'] = $record['expire'] ?? '';
                    $record['minimum'] = $record['minimum'] ?? '';
                    break;
                case 'SRV':
                    $record['priority'] = $record['priority'] ?? '';
                    $record['weight'] = $record['weight'] ?? '';
                    $record['port'] = $record['port'] ?? '';
                    $record['target'] = $record['target'] ?? '';
                    break;
            }
        }
    } else {
        error_log("No DNS records found or invalid response structure");
        $response['message']['cpanelresult']['data'][0]['record'] = [];
    }

    return $response;
}

function addDNSRecord($domain, $cpanel_user, $token, $name, $type, $address, $ttl = 14400) {
    if ($name === "@" || empty($name)) {
        $name = $domain . '.';
    } elseif (strpos($name, '.') === false) {
        $name = $name . '.' . $domain . '.';
    }

    $existingRecords = getDNSRecords($domain, $cpanel_user, $token);
    if ($existingRecords['success']) {
        foreach ($existingRecords['message']['cpanelresult']['data'][0]['record'] as $record) {
            if (($record['name'] ?? '') === $name && ($record['type'] ?? '') === $type) {
                deleteDNSRecord($domain, $cpanel_user, $token, $record['line'] ?? '');
                break;
            }
        }
    }

    return connectToCpanel($domain, $cpanel_user, $token, 'ZoneEdit', 'add_zone_record', [
        'domain' => $domain,
        'name' => $name,
        'type' => $type,
        'address' => $address,
        'ttl' => $ttl
    ]);
}

function deleteDNSRecord($domain, $cpanel_user, $token, $line) {
    return connectToCpanel($domain, $cpanel_user, $token, 'ZoneEdit', 'remove_zone_record', [
        'domain' => $domain,
        'line' => $line,
    ]);
}

/**
 * Main Logic
 */
$message = null;
$subdomains = [];
$dnsRecords = [];

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Check connection
if (isset($_SESSION['connected']) && $_SESSION['connected']) {
    $isConnected = true;
    $domain = $_SESSION['domain'];
    $cpanel_user = $_SESSION['cpanel_user'];
    $token = $_SESSION['token'];
} else {
    $isConnected = false;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? null;

    if ($action === 'connect') {
        $domain = $_POST['domain'] ?? null;
        $cpanel_user = $_POST['cpanel_user'] ?? null;
        $token = $_POST['token'] ?? null;

        if (!$domain || !$cpanel_user || !$token) {
            $message = "Please provide all fields: Domain, Username, and Token.";
        } else {
            $test = connectToCpanel($domain, $cpanel_user, $token, 'ZoneEdit', 'fetchzone', ['domain' => $domain]);
            if ($test['success']) {
                $_SESSION['connected'] = true;
                $_SESSION['domain'] = $domain;
                $_SESSION['cpanel_user'] = $cpanel_user;
                $_SESSION['token'] = $token;
                $isConnected = true;
                $message = "Connected to cPanel API successfully.";
            } else {
                $message = "Failed to connect: " . ($test['message'] ?? 'Unknown error');
            }
        }
    }

    if ($isConnected) {
        switch ($action) {
            case 'get_subdomains':
                $response = getSubdomains($domain, $cpanel_user, $token);
                if ($response['success']) {
                    $subdomains = $response['message']['cpanelresult']['data'];
                } else {
                    $message = "Failed to fetch subdomains: " . ($response['message'] ?? 'Unknown error');
                }
                break;

            case 'add_subdomain':
                $subdomain = $_POST['subdomain'] ?? null;
                $rootdomain = $_POST['rootdomain'] ?? null;
                $docroot = $_POST['docroot'] ?? null;
                $response = addSubdomain($domain, $cpanel_user, $token, $subdomain, $rootdomain, $docroot);
                if ($response['success']) {
                    $message = "Subdomain added successfully.";
                    $subdomains_response = getSubdomains($domain, $cpanel_user, $token);
                    if ($subdomains_response['success']) {
                        $subdomains = $subdomains_response['message']['cpanelresult']['data'];
                    }
                } else {
                    $message = "Failed to add subdomain: " . ($response['message'] ?? 'Unknown error');
                }
                break;

            case 'delete_subdomain':
                $subdomain = $_POST['subdomain'] ?? null;
                $response = deleteSubdomain($domain, $cpanel_user, $token, $subdomain);
                if ($response['success']) {
                    $message = "Subdomain deleted successfully.";
                    $subdomains_response = getSubdomains($domain, $cpanel_user, $token);
                    if ($subdomains_response['success']) {
                        $subdomains = $subdomains_response['message']['cpanelresult']['data'];
                    }
                } else {
                    $message = "Failed to delete subdomain: " . ($response['message'] ?? 'Unknown error');
                }
                break;

            case 'get_dns':
                $response = getDNSRecords($domain, $cpanel_user, $token);
                if ($response['success']) {
                    $dnsRecords = $response['message']['cpanelresult']['data'][0]['record'];
                } else {
                    $message = "Failed to fetch DNS records: " . ($response['message'] ?? 'Unknown error');
                }
                break;

            case 'add_dns':
                $name = $_POST['name'] ?? null;
                $type = $_POST['type'] ?? null;
                $address = $_POST['address'] ?? null;
                $ttl = $_POST['ttl'] ?? 14400;
                $response = addDNSRecord($domain, $cpanel_user, $token, $name, $type, $address, $ttl);
                if ($response['success']) {
                    $message = "DNS record added successfully.";
                    $dns_response = getDNSRecords($domain, $cpanel_user, $token);
                    if ($dns_response['success']) {
                        $dnsRecords = $dns_response['message']['cpanelresult']['data'][0]['record'];
                    }
                } else {
                    $message = "Failed to add DNS record: " . ($response['message'] ?? 'Unknown error');
                }
                break;

            case 'delete_dns':
                $line = $_POST['line'] ?? null;
                $response = deleteDNSRecord($domain, $cpanel_user, $token, $line);
                if ($response['success']) {
                    $message = "DNS record deleted successfully.";
                    $dns_response = getDNSRecords($domain, $cpanel_user, $token);
                    if ($dns_response['success']) {
                        $dnsRecords = $dns_response['message']['cpanelresult']['data'][0]['record'];
                    }
                } else {
                    $message = "Failed to delete DNS record: " . ($response['message'] ?? 'Unknown error');
                }
                break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DNS & Subdomain Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-gray-800">DNS & Subdomain Manager</h1>
                <?php if ($isConnected): ?>
                    <div class="flex items-center gap-4">
                        <span class="text-sm text-gray-600">Connected as: <?= htmlspecialchars($cpanel_user) ?></span>
                        <a href="?logout" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Logout</a>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($message): ?>
                <div class="mb-6 p-4 rounded <?= strpos($message, 'Failed') === false ? 'bg-green-100 text-green-800 border border-green-400' : 'bg-red-100 text-red-800 border border-red-400' ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <?php if (!$isConnected): ?>
                <!-- Login Form -->
                <form method="post" class="space-y-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Domain</label>
                        <input type="text" name="domain" class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-400" required>
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Username</label>
                        <input type="text" name="cpanel_user" class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-400" required>
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">API Token</label>
                        <input type="password" name="token" class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-400" required>
                    </div>
                    <button type="submit" name="action" value="connect" class="w-full bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">
                        Connect to cPanel
                    </button>
                </form>
            <?php endif; ?>

            <?php if ($isConnected): ?>
                <!-- Tabs Navigation -->
                <div class="mb-6 border-b">
                    <nav class="-mb-px flex space-x-8">
                        <button onclick="showTab('dns')" class="tab-btn py-2 px-4 border-b-2 font-medium text-sm focus:outline-none border-blue-500 text-blue-600">
                            DNS Records
                        </button>
                        <button onclick="showTab('subdomain')" class="tab-btn py-2 px-4 border-b-2 font-medium text-sm focus:outline-none border-transparent text-gray-500">
                            Subdomains
                        </button>
                    </nav>
                </div>

                <!-- DNS Records Section -->
                <div id="dns-tab" class="tab-content">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-2xl font-bold text-gray-800">DNS Records</h2>
                        <form method="post" class="inline">
                            <button type="submit" name="action" value="get_dns" 
                                    class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                Refresh DNS Records
                            </button>
                        </form>
                    </div>

                    <?php if (!empty($dnsRecords)): ?>
                        <div class="overflow-x-auto mb-6">
                            <table class="min-w-full table-auto border-collapse bg-white">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="border px-4 py-2 text-left">Name</th>
                                        <th class="border px-4 py-2 text-left">Type</th>
                                        <th class="border px-4 py-2 text-left">Address/Value</th>
                                        <th class="border px-4 py-2 text-center">TTL</th>
                                        <th class="border px-4 py-2 text-center w-24">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    if (is_array($dnsRecords)):
                                        foreach ($dnsRecords as $record): 
                                            if (!empty($record['type'])):
                                                // Get correct value based on record type
                                                $value = '';
                                                switch ($record['type']) {
                                                    case 'A':
                                                    case 'AAAA':
                                                        $value = $record['address'] ?? '';
                                                        break;
                                                    case 'CNAME':
                                                        $value = $record['cname'] ?? '';
                                                        break;
                                                    case 'NS':
                                                        $value = $record['nsdname'] ?? $record['address'] ?? '';
                                                        break;
                                                    case 'MX':
                                                        $value = ($record['preference'] ?? '') . ' ' . ($record['exchange'] ?? '');
                                                        break;
                                                    case 'TXT':
                                                        $value = $record['txt'] ?? $record['txtdata'] ?? '';
                                                        break;
                                                    case 'SOA':
                                                        $value = sprintf(
                                                            "%s %s %s %s %s %s %s",
                                                            $record['mname'] ?? '',
                                                            $record['rname'] ?? '',
                                                            $record['serial'] ?? '',
                                                            $record['refresh'] ?? '',
                                                            $record['retry'] ?? '',
                                                            $record['expire'] ?? '',
                                                            $record['minimum'] ?? ''
                                                        );
                                                        break;
                                                    case 'SRV':
                                                        $value = sprintf(
                                                            "%s %s %s %s",
                                                            $record['priority'] ?? '',
                                                            $record['weight'] ?? '',
                                                            $record['port'] ?? '',
                                                            $record['target'] ?? ''
                                                        );
                                                        break;
                                                    default:
                                                        $value = $record['address'] ?? '';
                                                }
                                    ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="border px-4 py-2"><?= htmlspecialchars($record['name'] ?? '') ?></td>
                                            <td class="border px-4 py-2"><?= htmlspecialchars($record['type'] ?? '') ?></td>
                                            <td class="border px-4 py-2"><?= htmlspecialchars($value) ?></td>
                                            <td class="border px-4 py-2 text-center"><?= htmlspecialchars($record['ttl'] ?? '14400') ?></td>
                                            <td class="border px-4 py-2 text-center">
                                                <?php if (!empty($record['line'])): ?>
                                                <form method="post" class="inline">
                                                    <input type="hidden" name="line" value="<?= htmlspecialchars($record['line']) ?>">
                                                    <button type="submit" name="action" value="delete_dns" 
                                                            class="bg-red-500 text-white px-3 py-1 rounded text-sm hover:bg-red-600"
                                                            onclick="return confirm('Are you sure you want to delete this DNS record?')">
                                                        Delete
                                                    </button>
                                                </form>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php 
                                            endif;
                                        endforeach;
                                    endif;
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4 text-gray-600">
                            No DNS records found. Click "Refresh DNS Records" to fetch the latest records.
                        </div>
                    <?php endif; ?>

                    <!-- Add DNS Record Form -->
                    <form method="post" class="bg-white p-6 rounded-lg shadow-sm">
                        <h3 class="text-xl font-bold text-gray-800 mb-4">Add New DNS Record</h3>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">Record Name</label>
                                <input type="text" name="name" 
                                       class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-400"
                                       placeholder="Use '@' for main domain" required>
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">Record Type</label>
                                <select name="type" class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-400" required>
                                    <option value="A">A</option>
                                    <option value="AAAA">AAAA</option>
                                    <option value="CNAME">CNAME</option>
                                    <option value="MX">MX</option>
                                    <option value="TXT">TXT</option>
                                    <option value="SRV">SRV</option>
                                    <option value="NS">NS</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">Address/Value</label>
                                <input type="text" name="address" 
                                       class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-400"
                                       placeholder="e.g., 192.168.1.1" required>
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">TTL (seconds)</label>
                                <input type="number" name="ttl" 
                                       class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-400"
                                       value="14400" required>
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="submit" name="action" value="add_dns" 
                                    class="bg-green-500 text-white py-2 px-4 rounded hover:bg-green-600">
                                Add DNS Record
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Subdomain Section -->
                <div id="subdomain-tab" class="tab-content hidden">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-2xl font-bold text-gray-800">Subdomains</h2>
                        <form method="post" class="inline">
                            <button type="submit" name="action" value="get_subdomains" 
                                    class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                Refresh Subdomains
                            </button>
                        </form>
                    </div>

                    <?php if (!empty($subdomains)): ?>
                        <div class="overflow-x-auto mb-6">
                            <table class="min-w-full table-auto border-collapse bg-white">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="border px-4 py-2 text-left">Domain</th>
                                        <th class="border px-4 py-2 text-left">Document Root</th>
                                        <th class="border px-4 py-2 text-center w-24">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($subdomains as $sub): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="border px-4 py-2"><?= htmlspecialchars($sub['domain'] ?? '') ?></td>
                                            <td class="border px-4 py-2"><?= htmlspecialchars($sub['dir'] ?? '') ?></td>
                                            <td class="border px-4 py-2 text-center">
                                                <form method="post" class="inline">
                                                    <input type="hidden" name="subdomain" value="<?= htmlspecialchars($sub['domain'] ?? '') ?>">
                                                    <button type="submit" name="action" value="delete_subdomain" 
                                                            class="bg-red-500 text-white px-3 py-1 rounded text-sm hover:bg-red-600"
                                                            onclick="return confirm('Are you sure you want to delete this subdomain?')">
                                                        Delete
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4 text-gray-600">
                            No subdomains found. Click "Refresh Subdomains" to fetch the latest records.
                        </div>
                    <?php endif; ?>

                    <!-- Add Subdomain Form -->
                    <form method="post" class="bg-white p-6 rounded-lg shadow-sm">
                        <h3 class="text-xl font-bold text-gray-800 mb-4">Add New Subdomain</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">Subdomain</label>
                                <input type="text" name="subdomain" 
                                       class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-400"
                                       placeholder="Enter subdomain" required>
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">Root Domain</label>
                                <input type="text" name="rootdomain" 
                                       class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-400"
                                       value="<?= htmlspecialchars($domain ?? '') ?>"
                                       placeholder="example.com" required>
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">Document Root</label>
                                <input type="text" name="docroot" 
                                       class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-400"
                                       placeholder="/home/user/public_html/subdomain" required>
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="submit" name="action" value="add_subdomain" 
                                    class="bg-green-500 text-white py-2 px-4 rounded hover:bg-green-600">
                                Add Subdomain
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
    function showTab(tabName) {
        // Hide all tab contents
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.add('hidden');
        });

        // Remove active class from all tab buttons
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('border-blue-500', 'text-blue-600');
            btn.classList.add('border-transparent', 'text-gray-500');
        });

        // Show selected tab content
        const selectedTab = document.getElementById(tabName + '-tab');
        if (selectedTab) {
            selectedTab.classList.remove('hidden');
        }

        // Add active class to selected tab button
        const selectedBtn = document.querySelector(`[onclick="showTab('${tabName}')"]`);
        if (selectedBtn) {
            selectedBtn.classList.remove('border-transparent', 'text-gray-500');
            selectedBtn.classList.add('border-blue-500', 'text-blue-600');
        }
    }

    // Show DNS tab by default when page loads
    window.addEventListener('load', () => showTab('dns'));
    </script>
</body>
</html>
