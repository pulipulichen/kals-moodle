<?php  // Moodle configuration file

unset($CFG);
global $CFG;

class MoodleObject {}
$CFG = new stdClass();
while (is_object($CFG) === FALSE) {
    sleep(0.5);
    $CFG = new stdClass();
}

$CFG->dbtype    = 'mysqli';
$CFG->dblibrary = 'native';
$CFG->dbhost    = 'localhost';
$CFG->dbname    = 'moodle';
$CFG->dbuser    = 'moodle';
$CFG->dbpass = '37ed06da70f6228bb17ca9848759ccf2';
$CFG->prefix    = '';
$CFG->dboptions = array (
  'dbpersist' => 0,
  'dbsocket' => false,
);

$protocol='http://';
$hostname='127.0.0.1';
if (isset($_SERVER['HTTPS'])) { $protocol='https://'; }
if (isset($_SERVER['HTTP_HOST'])) { $hostname=$_SERVER['HTTP_HOST']; }
$CFG->wwwroot = $protocol.$hostname;
//$CFG->wwwroot = $protocol.$hostname . "/kals-moodle/moodle";

$CFG->dataroot  = '/var/www/moodledata';

/**
 * 為了兼容Windows做的準備
 * @author Pulipuli Chen
 */
if (DIRECTORY_SEPARATOR === "\\") {
    $windows_dataroot = array(
        'C:\xampp\htdocs\kals-moodle\moodle',
        'D:\xampp\htdocs\kals-moodle\moodle'
    );
    foreach ($windows_dataroot AS $dataroot) {
        if (is_dir($dataroot)) {
            $CFG->dataroot  = $dataroot;
            break;
        }
    }
    
    //$CFG->wwwroot = $CFG->wwwroot . "/kals-moodle/moodle";
    $CFG->wwwroot = $CFG->wwwroot . "/kals-moodle/moodle";
    //echo $CFG->wwwroot;
}

$CFG->admin     = 'admin';

$CFG->directorypermissions = 0750;

$CFG->passwordsaltmain = '800e8e9b661714d756e50acbc94a7ea7';

/**
 * KALS相關的設定，全站統一
 * @author Pulipuli Chen <pulipuli.chen@gmail.com> 20151017
 */
$CFG->kals_config = array(
    "kals_url" => "/kals",
    "kals_converter_url" => "http://public-pdf2html-2013.dlll.nccu.edu.tw/iframe",
    "kals_converter_height" => 180,
    "kals_config" => '{
    /**
     * 從其他檔案讀取設定檔
     * @author Pulipuli Chen 20151017
     * @type String
     */
    kals_config_api: function () {
		var _pathname = window.location.pathname;
		var _parts = _pathname.split("/pluginfile.php/");
		var _base_path = _parts[0];
		var _context_id = _parts[1].substr(0, _parts[1].indexOf("/"));
		var _kals_config_api = _base_path + "/mod/resource/kals_config.php?context_id=" + _context_id;
		return _kals_config_api;
	}
}', 
    /**
     * 可以改變的KALS_CONFIG
     */
    "kals_config_api" => '
    /**
     * 預設登入帳號的網址
     */
    "user_email": "'. parse_url($CFG->wwwroot, PHP_URL_PATH) .'/user.php",
'
);

/**
 * 為了兼容Windows做的準備
 * @author Pulipuli Chen
 */
if (DIRECTORY_SEPARATOR === "\\") { 
    $CFG->kals_config["kals_url"] = "/kals";
    $CFG->kals_config["kals_converter_url"] = "/php-file-converter/iframe";
}

require_once(dirname(__FILE__) . '/lib/setup.php');

// There is no php closing tag in this file,
// it is intentional because it prevents trailing whitespace problems!
