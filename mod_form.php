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
 * webex configuration form
 *
 * @package    mod
 * @subpackage webex
 * @copyright  2012 Carlos kiyan, Walter Castillo , Luis Fukay
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

require_once ($CFG->dirroot . '/course/moodleform_mod.php');
require_once($CFG->dirroot . '/mod/webex/locallib.php');

class mod_webex_mod_form extends moodleform_mod {

    function definition() {
        global $CFG, $DB, $PAGE;
        
        $mform = $this->_form;
        $newevent = true;
        $repeatedevents = false;
        $hasduration = (!empty($this->_customdata->hasduration) && $this->_customdata->hasduration);


        $config = get_config('webex');
        //Commented by alok.kumar_paradiso on 21-oct-15 as this is generatinga an exception. 
        //$PAGE->requires->js(new moodle_url('http://cdn.bitbucket.org/pellepim/jstimezonedetect/downloads/jstz-1.0.4.min.js'), true);
        //-------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('text', 'name', get_string('name'), array('size' => '48', 'onchange="console.log(this.form.timezone.value=)"'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $this->add_intro_editor($config->requiremodintro);
        
        //  added by alok.kumar_paradiso
        $services = array(''=>'Select');
        if (isset($config->enablemeeting) && $config->enablemeeting == 1) {
            $services['meetingservice'] = 'Meetings Center';
        }
        if (isset($config->enabletraining) && $config->enabletraining == 1) {
            $services['trainingservice'] = 'Training Center';
        }
        if($config->enabletraining == 0 && $config->enablemeeting ==0) 
        {
            
        }

        /** get parameters field from DB to set select elements default
         * @author: miguel p
         * @since 15/06/2016
         * @paradiso
         */ 
        $webexparam = unserialize(@$this->current->parameters);
      

        $mform->addElement('header', get_string('servicetype', 'webex'), get_string('servicetype', 'webex'));

        $mform->addElement('select', 'webexservice', get_string('webexservice', 'webex'), $services);
        $mform->setType('webexservice', PARAM_TEXT);
        $webexparamserv = $webexparam['webexservice'] ; 
        $mform->setDefault('webexservice',  $webexparamserv);
        $mform->addRule('webexservice', get_string('webexservicerequired', 'webex'), 'required', null, 'client');
         $mform->addHelpButton('webexservice','webexservice','webex');
        
       
        $mform->addElement('passwordunmask', 'meeting_password', get_string('meeting_password','webex'));
        $mform->setType('meeting_password',PARAM_RAW);
        $mform->setDefault('meeting_password',  random_string(8));
        $mform->addHelpButton('meeting_password', 'meeting_password','webex');
        
        $mform->addElement('date_time_selector', 'timestart', get_string('startdatetime','webex'));
        $mform->addRule('timestart', get_string('required'), 'required');
        $mform->addElement('hidden', 'timezone', 11);
        // Added by alok.kumar_paradiso on 02/10/2015 to remove warning
        $mform->setType('timezone', PARAM_TIMEZONE);
        //$mform->addElement('header', 'durationdetails', get_string('eventduration', 'calendar'));

                       $radioarray=array();
         $attributes = array();
         $radioarray[] =& $mform->createElement('radio', 'permanent', '', get_string('yes'), 1, $attributes);
         $radioarray[] =& $mform->createElement('radio', 'permanent', '', get_string('no'), 0, $attributes);
         $mform->addGroup($radioarray, 'radioar', get_string('permanent', 'webex'), array(' '), false);
         $mform->addHelpButton('radioar','permanent','webex');
         $mform->disabledIf('permanent', 'webexservice','eq','meetingservice');
        $mform->addElement('text', 'timedurationminutes', get_string('timedurationminutes','webex'), "value='60'");
        $mform->setType('timedurationminutes', PARAM_INT);
       // $mform->disabledIf('timedurationminutes', 'permanent', 'eq', '');
       // $mform->addRule('timedurationminutes',get_string('required'),'required','server');
        

       
       // $mform->addElement('radio', 'duration', null, get_string('durationminutes', 'calendar'), 0);
       
        //$mform->addElement('radio', 'duration', null, get_string('durationnone', 'calendar'), 0);
        //$mform->addElement('radio', 'duration', null, get_string('permanent', 'webex'), 1);
       // $mform->disabledIf('duration', 'webexservice','noteq','trainingservice');
        $mform->addElement('date_time_selector', 'timedurationuntil', get_string('timedurationuntil','webex'));
        $mform->disabledIf('timedurationuntil', 'permanent', 'eq', 0);
        
        $mform->addElement('textarea','attendees',get_string('attendees','webex'));
        $mform->setType('attendees', PARAM_RAW);
        $mform->addHelpButton('attendees', 'attendees','webex');

        $options = array(''=>'Select','1'=>'VoIP','2'=>'WebEx Audio');
        $mform->addElement('select','conference_type',get_string('conference_type','webex'),$options);
        $webexparamconf = $webexparam['conference_type'] ;
        $mform->setDefault('conference_type', $webexparamconf);
        $mform->addHelpButton('conference_type', 'conference_type','webex');
        $mform->addRule('conference_type',  get_string('conference_type_req','webex'),'required','client');
        
        $options = array(''=>'Select','1'=>'HQVideo','2'=>'HDVideo');
        $mform->addElement('select','video_type',get_string('video_type','webex'),$options);
        $webexparamvide = $webexparam['video_type'] ;
        $mform->setDefault('video_type', $webexparamvide);
        $mform->addHelpButton('video_type', 'video_type','webex');
        $mform->addRule('video_type',  get_string('video_type_req','webex'),'required','client');
        $mform->setDefault('duration', ($hasduration) ? 1 : 0);

       /* if ($newevent) {

            $mform->addElement('header', 'repeatevents', get_string('repeatedevents', 'calendar'));
            $mform->addElement('checkbox', 'repeat', get_string('repeatevent', 'calendar'), null, 'repeat');
            $mform->addElement('text', 'repeats', get_string('repeatweeksl', 'calendar'), 'maxlength="10" size="10"');
            $mform->setType('repeats', PARAM_INT);
            $mform->setDefault('repeats', 1);
            $mform->disabledIf('repeats', 'repeat', 'notchecked');
        } else if ($repeatedevents) {

            $mform->addElement('hidden', 'repeatid');
            $mform->setType('repeatid', PARAM_INT);

            $mform->addElement('header', 'repeatedevents', get_string('repeatedevents', 'calendar'));
            $mform->addElement('radio', 'repeateditall', null, get_string('repeateditall', 'calendar', $this->_customdata->event->eventrepeats), 1);
            $mform->addElement('radio', 'repeateditall', null, get_string('repeateditthis', 'calendar'), 0);

            $mform->setDefault('repeateditall', 1);
        }*/


        //-------------------------------------------------------
        //$mform->addElement('header', 'content', get_string('contentheader', 'webex'));
        $mform->addElement('hidden', 'externalurl', get_string('externalurl', 'webex'), array('size' => '48'));
        $mform->setDefault('externalurl', $config->siteurl);
        // Added by alok.kumar_paradiso on 02/10/2015 to remove warning
        $mform->setType('externalurl', PARAM_URL);
        //$mform->addElement('static', 'parametersinfo', '', 'http://<b style="color:red">yoursite</b>.webex.com/');
        //$mform->addRule('externalurl', null, 'required', null, 'client');
        //-------------------------------------------------------
        //$mform->addElement('header', 'optionssection', get_string('optionsheader', 'webex'));

        if ($this->current->instance) {
            $options = resourcelib_get_displayoptions(explode(',', $config->displayoptions), $this->current->display);
        } else {
            $options = resourcelib_get_displayoptions(explode(',', $config->displayoptions));
        }
        if (count($options) == 1) {
            $mform->addElement('hidden', 'display');
            $mform->setType('display', PARAM_INT);
            reset($options);
           // $mform->setDefault('display', key($options));
        } else {
            $mform->addElement('hidden', 'display', get_string('displayselect', 'webex'), $options);
            $mform->setDefault('display', $config->display);
            $mform->setAdvanced('display', $config->display_adv);
            $mform->addHelpButton('display', 'displayselect', 'webex');
             // Added by alok.kumar_paradiso on 02/10/2015 to remove warning
            $mform->setType('display', PARAM_INT);
        }

        if (array_key_exists(RESOURCELIB_DISPLAY_POPUP, $options)) {
            $mform->addElement('hidden', 'popupwidth', get_string('popupwidth', 'webex'), array('size' => 3));
            if (count($options) > 1) {
                $mform->disabledIf('popupwidth', 'display', 'noteq', RESOURCELIB_DISPLAY_POPUP);
            }
            $mform->setType('popupwidth', PARAM_INT);
            $mform->setDefault('popupwidth', $config->popupwidth);
            $mform->setAdvanced('popupwidth', $config->popupwidth_adv);

            $mform->addElement('hidden', 'popupheight', get_string('popupheight', 'webex'), array('size' => 3));
            if (count($options) > 1) {
                $mform->disabledIf('popupheight', 'display', 'noteq', RESOURCELIB_DISPLAY_POPUP);
            }
            $mform->setType('popupheight', PARAM_INT);
            $mform->setDefault('popupheight', $config->popupheight);
            $mform->setAdvanced('popupheight', $config->popupheight_adv);
        }

        if (array_key_exists(RESOURCELIB_DISPLAY_FRAME, $options)) {
            $mform->addElement('hidden', 'printheading', get_string('printheading', 'webex'));
            $mform->disabledIf('printheading', 'display', 'eq', RESOURCELIB_DISPLAY_POPUP);
            $mform->disabledIf('printheading', 'display', 'eq', RESOURCELIB_DISPLAY_OPEN);
            $mform->disabledIf('printheading', 'display', 'eq', RESOURCELIB_DISPLAY_NEW);
            $mform->setDefault('printheading', $config->printheading);
            $mform->setAdvanced('printheading', $config->printheading_adv);

            $mform->addElement('hidden', 'printintro', get_string('printintro', 'webex'));
            $mform->disabledIf('printintro', 'display', 'eq', RESOURCELIB_DISPLAY_POPUP);
            $mform->disabledIf('printintro', 'display', 'eq', RESOURCELIB_DISPLAY_OPEN);
            $mform->disabledIf('printintro', 'display', 'eq', RESOURCELIB_DISPLAY_NEW);
            $mform->setDefault('printintro', $config->printintro);
            $mform->setAdvanced('printintro', $config->printintro_adv);
        }

        //-------------------------------------------------------
        //$mform->addElement('header', 'parameterssection', get_string('parametersheader', 'webex'));
        //$mform->addElement('static', 'parametersinfo', '', get_string('parametersheader_help', 'webex'));
        //$mform->setAdvanced('parametersinfo');
        /*
          $mform->addElement('hidden', 'variable_0', '', array('value'=>'AT','style'=>'display:none'));
          $mform->addElement('hidden', 'parameter_0', '', array('value'=>'JM','style'=>'display:none'));


          $mform->addElement('hidden', 'variable_1', '', array('value'=>'MK','readonly'=>'readonly','style'=>'display:none'));
          $mform->addElement('hidden', 'parameter_1', get_string('mettingkey', 'webex'), array('size'=>'12'));
          $mform->addElement('hidden', 'variable_2', '', array('value'=>'PW','readonly'=>'yes','style'=>'display:none'));
          $mform->addElement('hidden', 'parameter_2', get_string('password', 'webex'), array('size'=>'12'));
         */
        //-------------------------------------------------------
       $this->standard_coursemodule_elements();

        //-------------------------------------------------------
        $this->add_action_buttons(true, false, "Create Event and Display");
    }

    function data_preprocessing(&$default_values) {
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
        }
        if (!empty($default_values['parameters'])) {
            $parameters = unserialize($default_values['parameters']);
            $i = 0;
            foreach ($parameters as $parameter => $variable) {
                $default_values['parameter_' . $i] = $parameter;
                $default_values['variable_' . $i] = $variable;
                $i++;
            }
        }
    }

    function validation($data, $files) {
        global $CFG, $cw, $course;
        require_once($CFG->dirroot . "/course/format/weeks/lib.php");

        // Validating Entered webex, we are looking for obvious problems only,
        // teachers are responsible for testing if it actually works.
        // This is not a security validation!! Teachers are allowed to enter "javascript:alert(666)" for example.
        // NOTE: do not try to explain the difference between webex and URI, people would be only confused...
        $errors = parent::validation($data, $files);
      
       if($data['webexservice']=='meetingservice')
       {
           $starttime = $data['timestart'];
          if ($starttime <= time()) {
            $errors['timestart'] = "Start date must be in present or future";
        }
        if(isset($data['timedurationminutes']) && $data['timedurationminutes'] <=0 ){
         $errors['timedurationminutes'] = 'Duration is required, plesae enter a valid positive duration in minutes';  
        }
       }
        if($data['webexservice']=='trainingservice'){
            if ($data['timestart'] <= time()) {
            $errors['timestart'] = "Start date must be in present or future";
        }
        if ($data["permanent"] == 1) {
            if($data['timedurationuntil'] <= $data['timestart']){
                $errors['timedurationuntil'] = 'Expiry date should be greater than start date';
            }   
         
        } else if ($data["permanent"] == 0) {

           if(isset($data['timedurationminutes']) && $data['timedurationminutes'] <=0 ){
         $errors['timedurationminutes'] = 'Duration is required, plesae enter a valid positive duration in minutes';  
        }
        }
       
       
        } 
        if($data['password']!=''){
        $errmsg = '';
          if (!check_password_policy($data['password'], $errmsg)) {
            $errors['password'] = $errmsg;
        }
        }
        if($data['attendees']!='')
        {
            $emails= split(',',$data['']);
        }
        if ($course->format == 'weeks') {

           // $dates = format_weeks_get_section_dates($cw, $course);
           $dates = course_get_format($course)->get_section_dates($cw);
            if (($time > $dates->end) || ($time < $dates->start)) {
                $errors['timestart'] = "Start date must be in the range of the course week";
            }
        }

        if (empty($data['externalurl'])) {
            $errors['externalurl'] = get_string('required');
        } else {
            $url = trim($data['externalurl']);
        }
        
     
        return $errors;
    }

}
