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

//echo $resource->kals_config;
//print_r($resource->displayoptions["kals_config"]);
//print_r($resource->displayoptions);
$options = empty($resource->displayoptions) ? array() : unserialize($resource->displayoptions);
$kals_config = $options["kals_config"];

$pattern = '/(?:(?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:(?<!\:|\\\|\')\/\/.*))/';
$kals_config = preg_replace($pattern, '', $kals_config);

function isJson($string) {
    json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE);
}

json_decode($kals_config);
if (json_last_error() == JSON_ERROR_NONE) {
    echo $kals_config;
}
else {
    print_error('resource kals config format error: ' . $kals_config);
}