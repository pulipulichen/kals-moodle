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
 * @package    mod
 * @subpackage resource
 * @copyright  2009 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * List of features supported in Resource module
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, false if not, null if doesn't know
 */
function resource_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_ARCHETYPE:           return MOD_ARCHETYPE_RESOURCE;
        case FEATURE_GROUPS:                  return false;
        case FEATURE_GROUPINGS:               return false;
        case FEATURE_GROUPMEMBERSONLY:        return true;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_SHOW_DESCRIPTION:        return true;

        default: return null;
    }
}

/**
 * Returns all other caps used in module
 * @return array
 */
function resource_get_extra_capabilities() {
    return array('moodle/site:accessallgroups');
}

/**
 * This function is used by the reset_course_userdata function in moodlelib.
 * @param $data the data submitted from the reset course.
 * @return array status array
 */
function resource_reset_userdata($data) {
    return array();
}

/**
 * List of view style log actions
 * @return array
 */
function resource_get_view_actions() {
    return array('view','view all');
}

/**
 * List of update style log actions
 * @return array
 */
function resource_get_post_actions() {
    return array('update', 'add');
}

/**
 * Add resource instance.
 * @param object $data
 * @param object $mform
 * @return int new resource instance id
 */
function resource_add_instance($data, $mform) {
    global $CFG, $DB;
    require_once("$CFG->libdir/resourcelib.php");
    require_once("$CFG->dirroot/mod/resource/locallib.php");
    $cmid = $data->coursemodule;
    $data->timemodified = time();

    resource_set_display_options($data);

    $data->id = $DB->insert_record('resource', $data);

    // we need to use context now, so we need to make sure all needed info is already in db
    $DB->set_field('course_modules', 'instance', $data->id, array('id'=>$cmid));
    resource_set_mainfile($data);
    return $data->id;
}

/**
 * Update resource instance.
 * @param object $data
 * @param object $mform
 * @return bool true
 */
function resource_update_instance($data, $mform) {
    global $CFG, $DB;
    require_once("$CFG->libdir/resourcelib.php");
    $data->timemodified = time();
    $data->id           = $data->instance;
    
    /**
     * 把revision拿掉，這樣網址就不會有版本的問題了
     * @author Pulipuli Chen <pulipuli.chen@gmail.com> 20151017
     */
    $data->revision++;
    
    resource_set_display_options($data);

    $DB->update_record('resource', $data);
    resource_set_mainfile($data);
    return true;
}

/**
 * Updates display options based on form input.
 *
 * Shared code used by resource_add_instance and resource_update_instance.
 *
 * @param object $data Data object
 */
function resource_set_display_options($data) {
    $displayoptions = array();
    if ($data->display == RESOURCELIB_DISPLAY_POPUP) {
        $displayoptions['popupwidth']  = $data->popupwidth;
        $displayoptions['popupheight'] = $data->popupheight;
    }
    if (in_array($data->display, array(RESOURCELIB_DISPLAY_AUTO, RESOURCELIB_DISPLAY_EMBED, RESOURCELIB_DISPLAY_FRAME))) {
        $displayoptions['printheading'] = (int)!empty($data->printheading);
        $displayoptions['printintro']   = (int)!empty($data->printintro);
    }
    if (!empty($data->showsize)) {
        $displayoptions['showsize'] = 1;
    }
    if (!empty($data->showtype)) {
        $displayoptions['showtype'] = 1;
    }
    if (!empty($data->revisionenable)) {
        $displayoptions['revisionenable'] = 1;
    }
    
    /**
     * kals_config儲存設定
     * @author Pulipuli Chen <pulipuli.chen@gmail.com> 20151017
     */
    if (!empty($data->kals_config)) {
        
        $kals_config = trim($data->kals_config);
        if (substr($kals_config, -1, 1) === ",") {
            $kals_config = substr($kals_config, 0, -1);
        }
        $kals_config = "{".$kals_config."}";
        
        // 檢查kals_config是否符合規格
        $pattern = '/(?:(?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:(?<!\:|\\\|\')\/\/.*))/';
        $kals_config = preg_replace($pattern, '', $kals_config);
        
        json_decode($kals_config);
        if (json_last_error() !== JSON_ERROR_NONE) {
            print_error('resource kals config format error: <div><pre>' . $data->kals_config . "</pre></div>");
            die();
        }
        
        $displayoptions['kals_config'] = $data->kals_config;
    }
    else {
        // kals_config預設值
        $displayoptions['kals_config'] = "{}";
    }
    
//    error_log("resource_set_display_options: " . empty($data->enable_kals));
//    if (empty($data->enable_kals)) {
//        $displayoptions['enable_kals'] = "false";
//    }
//    else {
//        $displayoptions['enable_kals'] = "true";
//    }
    if (!empty($data->disable_kals)) {
        $displayoptions['disable_kals'] = 1;
    }
    //error_log("resource_set_display_options: $data->kals_config");
    
    $data->displayoptions = serialize($displayoptions);
}

/**
 * Delete resource instance.
 * @param int $id
 * @return bool true
 */
function resource_delete_instance($id) {
    global $DB;

    if (!$resource = $DB->get_record('resource', array('id'=>$id))) {
        return false;
    }

    // note: all context files are deleted automatically

    $DB->delete_records('resource', array('id'=>$resource->id));

    return true;
}

/**
 * Return use outline
 * @param object $course
 * @param object $user
 * @param object $mod
 * @param object $resource
 * @return object|null
 */
function resource_user_outline($course, $user, $mod, $resource) {
    global $DB;

    if ($logs = $DB->get_records('log', array('userid'=>$user->id, 'module'=>'resource',
                                              'action'=>'view', 'info'=>$resource->id), 'time ASC')) {

        $numviews = count($logs);
        $lastlog = array_pop($logs);

        $result = new stdClass();
        $result->info = get_string('numviews', '', $numviews);
        $result->time = $lastlog->time;

        return $result;
    }
    return NULL;
}

/**
 * Return use complete
 * @param object $course
 * @param object $user
 * @param object $mod
 * @param object $resource
 */
function resource_user_complete($course, $user, $mod, $resource) {
    global $CFG, $DB;

    if ($logs = $DB->get_records('log', array('userid'=>$user->id, 'module'=>'resource',
                                              'action'=>'view', 'info'=>$resource->id), 'time ASC')) {
        $numviews = count($logs);
        $lastlog = array_pop($logs);

        $strmostrecently = get_string('mostrecently');
        $strnumviews = get_string('numviews', '', $numviews);

        echo "$strnumviews - $strmostrecently ".userdate($lastlog->time);

    } else {
        print_string('neverseen', 'resource');
    }
}

/**
 * Given a course_module object, this function returns any
 * "extra" information that may be needed when printing
 * this activity in a course listing.
 *
 * See {@link get_array_of_activities()} in course/lib.php
 *
 * @param cm_info $coursemodule
 * @return cached_cm_info info
 */
function resource_get_coursemodule_info($coursemodule) {
    global $CFG, $DB;
    require_once("$CFG->libdir/filelib.php");
    require_once("$CFG->dirroot/mod/resource/locallib.php");
    require_once($CFG->libdir.'/completionlib.php');

    $context = context_module::instance($coursemodule->id);

    if (!$resource = $DB->get_record('resource', array('id'=>$coursemodule->instance),
            'id, name, display, displayoptions, tobemigrated, revision, intro, introformat')) {
        return NULL;
    }

    $info = new cached_cm_info();
    $info->name = $resource->name;
    if ($coursemodule->showdescription) {
        // Convert intro to html. Do not filter cached version, filters run at display time.
        $info->content = format_module_intro('resource', $resource, $coursemodule->id, false);
    }

    if ($resource->tobemigrated) {
        $info->icon ='i/invalid';
        return $info;
    }
    $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, 'mod_resource', 'content', 0, 'sortorder DESC, id ASC', false); // TODO: this is not very efficient!!
    if (count($files) >= 1) {
        $mainfile = reset($files);
        $info->icon = file_file_icon($mainfile, 24);
        $resource->mainfile = $mainfile->get_filename();
    }

    $display = resource_get_final_display_type($resource);

    if ($display == RESOURCELIB_DISPLAY_POPUP) {
        $fullurl = "$CFG->wwwroot/mod/resource/view.php?id=$coursemodule->id&amp;redirect=1";
        $options = empty($resource->displayoptions) ? array() : unserialize($resource->displayoptions);
        $width  = empty($options['popupwidth'])  ? 620 : $options['popupwidth'];
        $height = empty($options['popupheight']) ? 450 : $options['popupheight'];
        $wh = "width=$width,height=$height,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes";
        $info->onclick = "window.open('$fullurl', '', '$wh'); return false;";

    } else if ($display == RESOURCELIB_DISPLAY_NEW) {
        $fullurl = "$CFG->wwwroot/mod/resource/view.php?id=$coursemodule->id&amp;redirect=1";
        $info->onclick = "window.open('$fullurl'); return false;";
    }

    // If any optional extra details are turned on, store in custom data
    $info->customdata = resource_get_optional_details($resource, $coursemodule);

    return $info;
}

/**
 * Called when viewing course page. Shows extra details after the link if
 * enabled.
 *
 * @param cm_info $cm Course module information
 */
function resource_cm_info_view(cm_info $cm) {
    $details = $cm->get_custom_data();
    if ($details) {
        $cm->set_after_link(' ' . html_writer::tag('span', $details,
                array('class' => 'resourcelinkdetails')));
    }
}

/**
 * Lists all browsable file areas
 *
 * @package  mod_resource
 * @category files
 * @param stdClass $course course object
 * @param stdClass $cm course module object
 * @param stdClass $context context object
 * @return array
 */
function resource_get_file_areas($course, $cm, $context) {
    $areas = array();
    $areas['content'] = get_string('resourcecontent', 'resource');
    return $areas;
}

/**
 * File browsing support for resource module content area.
 *
 * @package  mod_resource
 * @category files
 * @param stdClass $browser file browser instance
 * @param stdClass $areas file areas
 * @param stdClass $course course object
 * @param stdClass $cm course module object
 * @param stdClass $context context object
 * @param string $filearea file area
 * @param int $itemid item ID
 * @param string $filepath file path
 * @param string $filename file name
 * @return file_info instance or null if not found
 */
function resource_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    global $CFG;

    if (!has_capability('moodle/course:managefiles', $context)) {
        // students can not peak here!
        return null;
    }

    $fs = get_file_storage();

    if ($filearea === 'content') {
        $filepath = is_null($filepath) ? '/' : $filepath;
        $filename = is_null($filename) ? '.' : $filename;

        $urlbase = $CFG->wwwroot.'/pluginfile.php';
        if (!$storedfile = $fs->get_file($context->id, 'mod_resource', 'content', 0, $filepath, $filename)) {
            if ($filepath === '/' and $filename === '.') {
                $storedfile = new virtual_root_file($context->id, 'mod_resource', 'content', 0);
            } else {
                // not found
                return null;
            }
        }
        require_once("$CFG->dirroot/mod/resource/locallib.php");
        return new resource_content_file_info($browser, $context, $storedfile, $urlbase, $areas[$filearea], true, true, true, false);
    }

    // note: resource_intro handled in file_browser automatically

    return null;
}

/**
 * Serves the resource files.
 *
 * @package  mod_resource
 * @category files
 * @param stdClass $course course object
 * @param stdClass $cm course module object
 * @param stdClass $context context object
 * @param string $filearea file area
 * @param array $args extra arguments
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool false if file not found, does not return if found - just send the file
 */
function resource_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    global $CFG, $DB;
    require_once("$CFG->libdir/resourcelib.php");
    
    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_course_login($course, true, $cm);
    if (!has_capability('mod/resource:view', $context)) {
        return false;
    }

    if ($filearea !== 'content') {
        // intro is handled automatically in pluginfile.php
        return false;
    }

    array_shift($args); // ignore revision - designed to prevent caching problems only

    $fs = get_file_storage();
    $relativepath = implode('/', $args);
    $fullpath = rtrim("/$context->id/mod_resource/$filearea/0/$relativepath", '/');
    do {
        if (!$file = $fs->get_file_by_hash(sha1($fullpath))) {
            if ($fs->get_file_by_hash(sha1("$fullpath/."))) {
                if ($file = $fs->get_file_by_hash(sha1("$fullpath/index.htm"))) {
                    break;
                }
                if ($file = $fs->get_file_by_hash(sha1("$fullpath/index.html"))) {
                    break;
                }
                if ($file = $fs->get_file_by_hash(sha1("$fullpath/Default.htm"))) {
                    break;
                }
            }
            $resource = $DB->get_record('resource', array('id'=>$cm->instance), 'id, legacyfiles', MUST_EXIST);
            if ($resource->legacyfiles != RESOURCELIB_LEGACYFILES_ACTIVE) {
                return false;
            }
            if (!$file = resourcelib_try_file_migration('/'.$relativepath, $cm->id, $cm->course, 'mod_resource', 'content', 0)) {
                return false;
            }
            // file migrate - update flag
            $resource->legacyfileslast = time();
            $DB->update_record('resource', $resource);
        }
    } while (false);

    // should we apply filters?
    $mimetype = $file->get_mimetype();
    if ($mimetype === 'text/html' or $mimetype === 'text/plain') {
        $filter = $DB->get_field('resource', 'filterfiles', array('id'=>$cm->instance));
        $CFG->embeddedsoforcelinktarget = true;
    } else {
        $filter = 0;
    }

    $resource_full = $DB->get_record('resource', array('id'=>$cm->instance), '*', MUST_EXIST);
    $options = empty($resource_full->displayoptions) ? array() : unserialize($resource_full->displayoptions);
    $disable_kals = $options["disable_kals"];
    
    //error_log("plugingfile: " . $resource_full->displayoptions . "!" .  $disable_kals);
    if (($mimetype === 'text/html')
            && $disable_kals !== 1) {
        resource_kals_render($file);
    }
    else {
        // finally send the file
        send_stored_file($file, 86400, $filter, $forcedownload, $options);
    }
}

/**
 * 不使用預設的輸出，而是搭配KALS的輸出
 * @author Pulipuli Chen <pulipuli.chen@gmail.com> 20151018
 * @param type $file
 */
function resource_kals_render($file) {
    global $CFG;
    $output = $file->get_content();
    
    // 如果不是UTF8，改成UTF8
    if (mb_detect_encoding($output, 'UTF-8', true) === false) {
        $output = iconv(mb_detect_encoding($output, mb_detect_order(), true), "UTF-8", $output);
    }
    
    // 標頭輸出UTF-8
    header("Content-Type:text/html; charset=utf-8");
    
    // 輸出文件內容
    echo $output;
    
    $has_title = true;
    if (strpos($output, "<title>") === FALSE) {
        $has_title = false;
    }
    
    echo '
<!-- [KALS] -->
<script type="text/javascript" src="' . $CFG->kals_config["kals_url"] . '/web_apps/generic/loader/release"></script>
<script type="text/javascript">
KALS_CONFIG = {
kals_config_api: function () {
            var _pathname = window.location.pathname;
            var _parts = _pathname.split("/pluginfile.php/");
            var _base_path = _parts[0];
            var _context_id = _parts[1].substr(0, _parts[1].indexOf("/"));
            var _kals_config_api = _base_path + "/mod/resource/kals_config.php?context_id=" + _context_id;
            return _kals_config_api;
    }
};

</script>
<!-- [/KALS] -->
<style="text/css">
body {background-color:transparent;}
</style>
';
    if ($has_title === false) {
        $filename = $file->get_filename();
        $dot_pos = strrpos($filename, ".");
        if ($dot_pos !== FALSE) {
            $filename = substr($filename, 0 , strrpos($filename, "."));
        }
        //echo '<title>' . $filename . '</title>';
        echo '<script type="text/javascript">document.title="'.$filename.'"</script>';
    }
    die;
}

/**
 * Return a list of page types
 * @param string $pagetype current page type
 * @param stdClass $parentcontext Block's parent context
 * @param stdClass $currentcontext Current context of block
 */
function resource_page_type_list($pagetype, $parentcontext, $currentcontext) {
    $module_pagetype = array('mod-resource-*'=>get_string('page-mod-resource-x', 'resource'));
    return $module_pagetype;
}

/**
 * Export file resource contents
 *
 * @return array of file content
 */
function resource_export_contents($cm, $baseurl) {
    global $CFG, $DB;
    $contents = array();
    $context = context_module::instance($cm->id);
    $resource = $DB->get_record('resource', array('id'=>$cm->instance), '*', MUST_EXIST);

    $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, 'mod_resource', 'content', 0, 'sortorder DESC, id ASC', false);

    foreach ($files as $fileinfo) {
        $file = array();
        $file['type'] = 'file';
        $file['filename']     = $fileinfo->get_filename();
        $file['filepath']     = $fileinfo->get_filepath();
        $file['filesize']     = $fileinfo->get_filesize();
        $file['fileurl']      = file_encode_url("$CFG->wwwroot/" . $baseurl, '/'.$context->id.'/mod_resource/content/'.$resource->revision.$fileinfo->get_filepath().$fileinfo->get_filename(), true);
        $file['timecreated']  = $fileinfo->get_timecreated();
        $file['timemodified'] = $fileinfo->get_timemodified();
        $file['sortorder']    = $fileinfo->get_sortorder();
        $file['userid']       = $fileinfo->get_userid();
        $file['author']       = $fileinfo->get_author();
        $file['license']      = $fileinfo->get_license();
        $contents[] = $file;
    }

    return $contents;
}

/**
 * Register the ability to handle drag and drop file uploads
 * @return array containing details of the files / types the mod can handle
 */
function resource_dndupload_register() {
    return array('files' => array(
                     array('extension' => '*', 'message' => get_string('dnduploadresource', 'mod_resource'))
                 ));
}

/**
 * Handle a file that has been uploaded
 * @param object $uploadinfo details of the file / content that has been uploaded
 * @return int instance id of the newly created mod
 */
function resource_dndupload_handle($uploadinfo) {
    // Gather the required info.
    $data = new stdClass();
    $data->course = $uploadinfo->course->id;
    $data->name = $uploadinfo->displayname;
    $data->intro = '';
    $data->introformat = FORMAT_HTML;
    $data->coursemodule = $uploadinfo->coursemodule;
    $data->files = $uploadinfo->draftitemid;

    // Set the display options to the site defaults.
    $config = get_config('resource');
    $data->display = $config->display;
    $data->popupheight = $config->popupheight;
    $data->popupwidth = $config->popupwidth;
    $data->printheading = $config->printheading;
    $data->printintro = $config->printintro;
    $data->showsize = (isset($config->showsize)) ? $config->showsize : 0;
    $data->showtype = (isset($config->showtype)) ? $config->showtype : 0;
    $data->disable_kals = (isset($config->disable_kals)) ? $config->disable_kals : 0;
    $data->revisionenable = (isset($config->revisionenable)) ? $config->revisionenable : 0;
    $data->filterfiles = $config->filterfiles;
    
    return resource_add_instance($data, null);
}
