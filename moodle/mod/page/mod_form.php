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
 * Page configuration form
 *
 * @package    mod
 * @subpackage page
 * @copyright  2009 Petr Skoda (http://skodak.org)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/page/locallib.php');
require_once($CFG->libdir.'/filelib.php');

class mod_page_mod_form extends moodleform_mod {
    function definition() {
        global $CFG, $DB;

        $mform = $this->_form;

        $config = get_config('page');
        

        //-------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('text', 'name', get_string('name'), array('size'=>'48'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $this->add_intro_editor($config->requiremodintro);

        //-------------------------------------------------------
        $mform->addElement('header', 'contentsection', get_string('contentheader', 'page'));
        $mform->addElement('editor', 'page', get_string('content', 'page'), null, page_get_editor_options($this->context));
        $mform->addRule('page', get_string('required'), 'required', null, 'client');

        //-------------------------------------------------------
        $mform->addElement('header', 'optionssection', get_string('optionsheader', 'page'));
        
        //echo "121212". $config->displayoptions;
        //print_r($config->displayoptions);
        //print_r($this->current->display);

        if ($this->current->instance) {
            $options = resourcelib_get_displayoptions(explode(',', $config->displayoptions), $this->current->display);
        } else {
            $options = resourcelib_get_displayoptions(explode(',', $config->displayoptions));
        }
        if (count($options) == 1) {
            $mform->addElement('hidden', 'display');
            $mform->setType('display', PARAM_INT);
            reset($options);
            $mform->setDefault('display', key($options));
        } else {
            $mform->addElement('select', 'display', get_string('displayselect', 'page'), $options);
            $mform->setDefault('display', $config->display);
            $mform->setAdvanced('display', $config->display_adv);
        }

        if (array_key_exists(RESOURCELIB_DISPLAY_POPUP, $options)) {
            $mform->addElement('text', 'popupwidth', get_string('popupwidth', 'page'), array('size'=>3));
            if (count($options) > 1) {
                $mform->disabledIf('popupwidth', 'display', 'noteq', RESOURCELIB_DISPLAY_POPUP);
            }
            $mform->setType('popupwidth', PARAM_INT);
            $mform->setDefault('popupwidth', $config->popupwidth);
            $mform->setAdvanced('popupwidth', $config->popupwidth_adv);

            $mform->addElement('text', 'popupheight', get_string('popupheight', 'page'), array('size'=>3));
            if (count($options) > 1) {
                $mform->disabledIf('popupheight', 'display', 'noteq', RESOURCELIB_DISPLAY_POPUP);
            }
            $mform->setType('popupheight', PARAM_INT);
            $mform->setDefault('popupheight', $config->popupheight);
            $mform->setAdvanced('popupheight', $config->popupheight_adv);
        }

        //echo $config->printheading.']]]';
        $mform->addElement('advcheckbox', 'printheading', get_string('printheading', 'page'));
        $mform->setDefault('printheading', $config->printheading);
        $mform->setAdvanced('printintro', $config->printheading_adv);
        
        $mform->addElement('advcheckbox', 'printintro', get_string('printintro', 'page'));
        $mform->setDefault('printintro', $config->printintro);
        $mform->setAdvanced('printintro', $config->printintro_adv);

        // add legacy files flag only if used
        if (isset($this->current->legacyfiles) and $this->current->legacyfiles != RESOURCELIB_LEGACYFILES_NO) {
            $options = array(RESOURCELIB_LEGACYFILES_DONE   => get_string('legacyfilesdone', 'page'),
                             RESOURCELIB_LEGACYFILES_ACTIVE => get_string('legacyfilesactive', 'page'));
            $mform->addElement('select', 'legacyfiles', get_string('legacyfiles', 'page'), $options);
            $mform->setAdvanced('legacyfiles', 1);
        }
        
        //-------------------------------------------------------
        $mform->addElement('header', 'kalssection', get_string('kals_header', 'page'));
        /*
        print_r( array(
            "options" => $options->printintro_adv,
            "config" => $config->printintro_adv,
            
        )) ;
        print_r( array(
            "options" => $options->kals_enable,
            "config" => $config->kals_enable,
            
        )) ;
        */
        
        //$page = $DB->get_record('page', array('id'=>$this->current->instance), '*', MUST_EXIST);
        //$options = empty($page->displayoptions) ? array() : unserialize($page->displayoptions);
        
        
        // 使用標註
        $mform->addElement('advcheckbox', 'kals_enable', get_string('kals_enable', 'page'));
        $mform->setDefault('kals_enable', $options->kals_enable);
        //echo "[".$options->kals_enable."]";
        $mform->setAdvanced('kals_enable', $options->kals_enable_adv);
        
        // 系統網址
        $mform->addElement('text', 'kals_url', get_string('kals_url', 'page'));
        //$mform->setDefault('kals_url', $options->kals_url);
        //$mform->setAdvanced('kals_url', $options->kals_url_adv);
        
        // 標註類型

        
        //$features = new object();
        //$features->kals_enable           = false;
        //$features->kals_url        = 'AAAAAAAAAAA';
        
        //-------------------------------------------------------
        $this->standard_coursemodule_elements();
        //$this->standard_coursemodule_elements($features);

        //-------------------------------------------------------
        $this->add_action_buttons();

        //-------------------------------------------------------
        $mform->addElement('hidden', 'revision');
        $mform->setType('revision', PARAM_INT);
        $mform->setDefault('revision', 1);
    }

    function data_preprocessing(&$default_values) {
        if ($this->current->instance) {
            $draftitemid = file_get_submitted_draft_itemid('page');
            $default_values['page']['format'] = $default_values['contentformat'];
            $default_values['page']['text']   = file_prepare_draft_area($draftitemid, $this->context->id, 'mod_page', 'content', 0, page_get_editor_options($this->context), $default_values['content']);
            $default_values['page']['itemid'] = $draftitemid;
        }
        if (!empty($default_values['displayoptions'])) {
            $displayoptions = unserialize($default_values['displayoptions']);
            if (isset($displayoptions['printintro'])) {
                $default_values['printintro'] = $displayoptions['printintro'];
            }
            if (isset($displayoptions['printheading'])) {
                $default_values['printheading'] = $displayoptions['printheading'];
            }
            if (!empty($displayoptions['popupwidth'])) {
                $default_values['popupwidth'] = $displayoptions['popupwidth'];
            }
            if (!empty($displayoptions['popupheight'])) {
                $default_values['popupheight'] = $displayoptions['popupheight'];
            }
            if (!empty($displayoptions['kals_enable'])) {
                $default_values['kals_enable'] = $displayoptions['kals_enable'];
            }
            //echo "data_preprocessing: " . $default_values;
        }
    }
}

