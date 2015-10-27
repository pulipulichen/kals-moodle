<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Resource module version information
 *
 * @package    mod
 * @subpackage resource
 * @copyright  2009 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

header("Content-Type: application/javascript");

require('../../config.php');
require_once($CFG->dirroot.'/mod/resource/locallib.php');
require_once($CFG->libdir.'/completionlib.php');

$id       = optional_param('id', 0, PARAM_INT); // Course Module ID
$r        = optional_param('r', 0, PARAM_INT);  // Resource instance ID
$redirect = optional_param('redirect', 0, PARAM_BOOL);

/**
 * @author Pulipuli Chen <pulipuli.chen@gmail.com> 20151017
 */
$context_id  = optional_param('context_id', 0, PARAM_INT); // Resource File ID
if ($context_id !== 0) {
    list($context, $course, $cm) = get_context_info_array($context_id);
    $id = $cm->id;
}

if ($r) {
    if (!$resource = $DB->get_record('resource', array('id'=>$r))) {
        resource_redirect_if_migrated($r, 0);
        print_error('invalidaccessparameter');
    }
    $cm = get_coursemodule_from_instance('resource', $resource->id, $resource->course, false, MUST_EXIST);

} else {
    if (!$cm = get_coursemodule_from_id('resource', $id)) {
        resource_redirect_if_migrated(0, $id);
        print_error('invalidcoursemodule');
    }
    $resource = $DB->get_record('resource', array('id'=>$cm->instance), '*', MUST_EXIST);
}

//function convert_to_json_object($config) {
//    $pattern = '/(?:(?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:(?<!\:|\\\|\')\/\/.*))/';
//
//    $config = trim($config);
//    if (substr($config, -1 , 1) === ",") {
//        $config = substr($config, 0, -1);
//    }
//    $config = "{".$config."}";
//    $config = preg_replace($pattern, '', $config);
//    //$object = json_decode($config, JSON_UNESCAPED_SLASHES);
//    echo $config;
//    $object = json_decode($config);
//    return $object;
//}
//
////echo $resource->kals_config;
////print_r($resource->displayoptions["kals_config"]);
////print_r($resource->displayoptions);
//$options = empty($resource->displayoptions) ? array() : unserialize($resource->displayoptions);
//$kals_config = $options["kals_config"];
//$kals_config_json = convert_to_json_object($kals_config);
//
//$CFG_kals_config = $CFG->kals_config["kals_config_api"];
//
//$CFG_kals_config_json = convert_to_json_object($CFG_kals_config);
//print_r($CFG_kals_config_json);
//
////foreach ($kals_config_json AS $key => $value) {
////   
// 
////    if ($key !== "modules") {
////        $CFG_kals_config_json[$key] = $value;
////    }
////    else {
////        $CFG_modules = $CFG_kals_config_json[$key];
////        $modules = $value;
////        foreach ($modules as $key => $value) {
////            $CFG_modules[$key] = $modules[$key];
////        }
////        $CFG_kals_config_json["modules"] = $CFG_modules;
////    }
////}
//print_r($CFG_kals_config_json);
//$kals_config = json_encode($CFG_kals_config_json, JSON_UNESCAPED_SLASHES+JSON_UNESCAPED_UNICODE);


function convert_to_json($config) {
    $pattern = '/(?:(?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:(?<!\:|\\\|\')\/\/.*))/';

    $config = trim($config);
    if (substr($config, -1 , 1) === ",") {
        $config = substr($config, 0, -1);
    }
    $config = "{".$config."}";
    $config = preg_replace($pattern, '', $config);
    return $config;
}

$options = empty($resource->displayoptions) ? array() : unserialize($resource->displayoptions);
$kals_config = $options["kals_config"];

if (json_last_error() == JSON_ERROR_NONE) {
    echo convert_to_json($kals_config); 
}
else {
    print_error('resource kals config format error: ' . $kals_config);
}