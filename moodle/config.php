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
    
    $CFG->wwwroot = $CFG->wwwroot . "/kals-moodle/moodle";
    //echo $CFG->wwwroot;
}
$CFG->admin     = 'admin';

$CFG->directorypermissions = 0750;

$CFG->passwordsaltmain = '800e8e9b661714d756e50acbc94a7ea7';

require_once(dirname(__FILE__) . '/lib/setup.php');

// There is no php closing tag in this file,
// it is intentional because it prevents trailing whitespace problems!
