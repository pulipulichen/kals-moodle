<?php  // Moodle configuration file

unset($CFG);
global $CFG;
$CFG = new stdClass();

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

if (DIRECTORY_SEPARATOR === "\\") {
    $CFG->dataroot  = 'D:\\xampp\\htdocs\\kals-moodle\\moodledata';
}
else {
    $CFG->dataroot  = '/var/www/moodledata';
}
$CFG->admin     = 'admin';

$CFG->directorypermissions = 0750;

$CFG->passwordsaltmain = '800e8e9b661714d756e50acbc94a7ea7';

require_once(dirname(__FILE__) . '/lib/setup.php');

// There is no php closing tag in this file,
// it is intentional because it prevents trailing whitespace problems!
