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
 * Mandatory public API of webex module
 *
 * @package    mod
 * @subpackage webex
 * @copyright  2012 Carlos kiyan, Walter Castillo , Luis Fukay
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

/**
 * List of features supported in webex module
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, false if not, null if doesn't know
 */
function webex_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_ARCHETYPE: return MOD_ARCHETYPE_RESOURCE;
        case FEATURE_GROUPS: return false;
        case FEATURE_GROUPINGS: return false;
        //case FEATURE_GROUPMEMBERSONLY: return true;
        case FEATURE_MOD_INTRO: return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_GRADE_HAS_GRADE: return false;
        case FEATURE_GRADE_OUTCOMES: return false;
        case FEATURE_BACKUP_MOODLE2: return true;

        default: return null;
    }
}

/**
 * Returns all other caps used in module
 * @return array
 */
function webex_get_extra_capabilities() {
    return array('moodle/site:accessallgroups');
}

/**
 * This function is used by the reset_course_userdata function in moodlelib.
 * @param $data the data submitted from the reset course.
 * @return array status array
 */
function webex_reset_userdata($data) {
    return array();
}

/**
 * List of view style log actions
 * @return array
 */
function webex_get_view_actions() {
    return array('view', 'view all');
}

/**
 * List of update style log actions
 * @return array
 */
function webex_get_post_actions() {
    return array('update', 'add');
}

/**
 * Add webex instance.
 * @param object $data
 * @param object $mform
 * @return int new webex instance id
 */
function webex_add_instance($data, $mform) {
    global $CFG, $DB,$USER;
   
    require_once($CFG->dirroot . '/mod/webex/locallib.php');
    $config = get_config('webex');
    /* MEETING DATA PARAMETERS BY ESTEBAN MOLINA */
    
    $displayoptions = array();

    if ($data->display == RESOURCELIB_DISPLAY_POPUP) {
        $displayoptions['popupwidth'] = $data->popupwidth;
        $displayoptions['popupheight'] = $data->popupheight;
    }
    if (in_array($data->display, array(RESOURCELIB_DISPLAY_AUTO, RESOURCELIB_DISPLAY_EMBED, RESOURCELIB_DISPLAY_FRAME))) {
        $displayoptions['printheading'] = (int) !empty($data->printheading);
        $displayoptions['printintro'] = (int) !empty($data->printintro);
    }

    if ($data->duration == 1) {

        $data->timeduration = $data->timedurationuntil - $data->timestart;
    } else if ($data->duration == 0) {
        $data->timeduration = $data->timedurationminutes * MINSECS;
    }
    
    $data->duration = $data->timeduration;

    $data->displayoptions = serialize($displayoptions);

    $data->externalurl = webex_fix_submitted_webex($data->externalurl);

    $data->timemodified = time();
    //WEBEX XML API CLIENT INTANCE
    require_once($CFG->dirroot . '/mod/webex/webex.php');

    $webexoptions = new stdclass();
        if(isset($data->meeting_password) && $data->meeting_password!=''){
            $webexoptions->meeting_password = trim($data->meeting_password);
        }else 
        {
         $webexoptions->meeting_password = 'paradiso';   
        }
        if(isset($data->attendees) && $data->attendees != '' ){
          $webexoptions->attendees = str_getcsv(trim($data->attendees),','); 
        }
        if(isset($data->video_type) && $data->video_type)
        {
            $webexoptions->video_type= $data->video_type;
        }
        if(isset($data->conference_type) && $data->conference_type)
        {
           $webexoptions->conference_type = $data->conference_type;
        }
        
    $webex = new WebEx($config->webexid, $config->webexpassword, $config->siteid, $config->partnerid, $config->siteurl);
    $webex->user_SetUser($config->webexid, $USER->firstname,$USER->lastname);

//CREEATE A MEETING ON WEBEX SYSTEM AND RETURNS NEETING ID
    if($data->webexservice=='meetingservice')
    {
       $meeting = $webex->meeting_CreateMeeting($data->name, date("m/d/Y H:i:s", ($data->timestart)), $data->intro, $data->duration / 60,'NO_REPEAT',strtoupper(date("l",$data->timestart)),$webexoptions);
      
       if(!$meeting)
       {
        print_error('Unable to process request this time.','',new moodle_url($CFG->wwwroot.'/course/view.php',array('id'=>$data->course)));
        return false;  
       }
           
       
		
    }else if($data->webexservice=='trainingservice')
    {
     if(isset($data->repeat) && $data->repeat==1){
        $meeting = $webex->training_CreateTrainingSession($data->name, date("m/d/Y H:i:s", ($data->timestart)), $data->intro, $data->duration / 60,'RECURRING_SINGLE',strtoupper(date("l",$data->timestart)),$webexoptions);
    }else{
        $meeting = $webex->training_CreateTrainingSession($data->name, date("m/d/Y H:i:s", ($data->timestart)), $data->intro, $data->duration / 60);
    }
    }   
    $parameters["MK"] = $meeting;
    $parameters['webexservice']=$data->webexservice;
    $parameters["PW"] =  $webexoptions->meeting_password;   
    
    
     if(isset($webexoptions->attendees)){
        $parameters['attendees']= $webexoptions->attendees; 
    }
    /** add parameters conference type and video type 
     * @author: miguel p 14/06/16
     * @since 15/06/2016
     * @paradiso
     */ 
    if($data->conference_type) {
      $parameters['conference_type'] =  $data->conference_type;
    }
    if($data->video_type) {
      $parameters["video_type"] =  $data->video_type;
    }
   
    $data->parameters = serialize($parameters);
    $data->meetingid = $meeting;

    $data->eventid = -1;
    $data->id = $DB->insert_record('webex', $data);
    /* EVENT PROCESSING BY ESTEBAN MOLINA */

    require_once($CFG->dirroot . '/calendar/event_form.php');
    require_once($CFG->dirroot . '/calendar/lib.php');
    require_once($CFG->dirroot . '/course/lib.php');

    $action = 'new';
    $eventid = optional_param('id', 0, PARAM_INT);
    $courseid = $data->course;
    //$meeting = 'meeting';
     // Added by alok.kumar_paradiso on 02/10/2015 to as get_context_instance deprecated
    //$contextid = get_context_instance(CONTEXT_COURSE, $courseid);
      $contextid = context_course::instance($courseid);      

    $cal_y = optional_param('cal_y', 0, PARAM_INT);
    $cal_m = optional_param('cal_m', 0, PARAM_INT);
    $cal_d = optional_param('cal_d', 0, PARAM_INT);

    $url = new moodle_url('/calendar/event.php', array('action' => $action));
    if ($eventid != 0) {
        $url->param('id', $eventid);
    }
    if ($courseid != SITEID) {
        $url->param('course', $courseid);
    }
    if ($cal_y !== 0) {
        $url->param('cal_y', $cal_y);
    }
    if ($cal_m !== 0) {
        $url->param('cal_m', $cal_m);
    }
    if ($cal_d !== 0) {
        $url->param('cal_d', $cal_d);
    }


    if ($courseid != SITEID && !empty($courseid)) {
        $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
        $courses = array($course->id => $course);
        $issite = false;
    } else {
        $course = get_site();
        $courses = calendar_get_default_courses();
        $issite = true;
    }

    //require_login($course, false);

    if ($action === 'delete' && $eventid > 0) {
        $deleteurl = new moodle_url('/calendar/delete.php', array('id' => $eventid));
        if ($courseid > 0) {
            $deleteurl->param('course', $courseid);
        }
        redirect($deleteurl);
    }


    $calendar = new calendar_information($cal_d, $cal_m, $cal_y);
    $calendar->prepare_for_view($course, $courses);

    $formoptions = new stdClass;
    if ($eventid !== 0) {
        $title = get_string('editevent', 'calendar');
        $event = calendar_event::load($eventid);

        if (!calendar_edit_event_allowed($event)) {
            print_error('nopermissions');
        }
        $event->action = $action;
        $event->course = $courseid;
        $event->timedurationuntil = $event->timestart + $event->timeduration;
        $event->count_repeats();

        if (!calendar_add_event_allowed($event)) {
            print_error('nopermissions');
        }
    } else {

        $title = get_string('newevent', 'calendar');
        calendar_get_allowed_types($formoptions->eventtypes, $course);


        $event = new stdClass();
        $event->action = $action;
        $event->course = $courseid;
        $event->courseid = $courseid;
        $event->timeduration = $data->timeduration;
        $event->eventtype = 'meeting';
        $event->timestart = $data->timestart;
        $event->modulename = 'webex';
        $event->instance = $data->id;
        // Added by alok.kumar_paradiso on 02/10/2015 as duration is missing
        $event->duration = $data->duration;
        $event = new calendar_event($event);

        if (!calendar_add_event_allowed($event)) {
            print_error('nopermissions');
        }
    }

    $properties = $event->properties(true);
    $formoptions->event = $event;
    $formoptions->hasduration = ($event->timeduration > 0);


    if ($properties) {
        if ($properties->duration == 1) {
            $properties->timeduration = $properties->timedurationuntil - $properties->timestart;
        } else if ($properties->duration == 2) {
            $properties->timeduration = $properties->timedurationminutes * MINSECS;
        } else {
            $properties->timeduration = 0;
        }
        $properties->name = $data->name;
        $properties->description = $data->intro;
        if(isset($data->repeat)){
            $properties->repeat=$data->repeat;
            $properties->repeats=$data->repeats;
        }
        

        $event->update($properties);

        $data->eventid = $event->id;
    }

    $DB->update_record('webex', $data);

    $ssql = "SELECT u.id, u.username,u.email,u.firstname,u.lastname
    FROM {user} u, {role_assignments} r
    WHERE u.id=r.userid AND r.contextid = {$contextid->id}";
    $result = $DB->get_records_sql($ssql);

    $eventdate = date('h:i A D,d M,Y', $data->timestart);

    $resultDuration = CalculateDuration($data->duration);
    $duration = round($resultDuration->duration);
    
    return  $data->id;
    foreach ($result as $user) {
        $mail = & get_mailer();
        $mail->SetFrom($config->fromemailaddress, $config->fromemailname);
        $mail->Subject = 'New online meeting';

        $mail->AddAddress($user->email, $user->username);
        $mail->IsHTML(true);

        $mail->Body = <<< HTML
             <strong>Hello $user->firstname $user->lastname,</strong><br/>
                <br/>
                A new Online class or meeting has been created for the course $course->fullname, see all details below:<br/>
                <br/>
                <strong>Course:</strong> $course->fullname <br/>
                <strong>Meeting name:</strong> $data->name<br/>
                <strong>Description:</strong> $data->intro<br/>
                <strong>Start date:</strong> $eventdate<br/>
                <strong>Duration:</strong>$duration $resultDuration->units<br/>
                <br/>
                Click on the link below to see the list of all meetings.<br/>

                <a href="$CFG->wwwroot/mod/webex/list.php?course=$courseid"> See all meetings.</a>
                <br/>
                <br/>
                Regards
HTML;
        if ($mail->Send()) {
            echo 'Mail sent';
        } else {
            echo('ERROR:' . $mail->ErrorInfo);
            return false;
        }
    }
    
    return  $data->id;
}

function webex_get_rooms($course){
    global $DB, $CFG;
    require_once($CFG->dirroot . '/mod/webex/webex.php');
    $config = get_config('webex');
    
    $webex = new WebEx($config->webexid, $config->webexpassword, $config->siteid, $config->partnerid, $config->siteurl . '/WBXService/XMLService');
    
    //0 = waiting
    //1 = active
    $ssql = "SELECT * FROM {rooms} r,{webex} w WHERE r.status ='0' OR r.status ='1' AND r.courseid = $course AND w.id=r.webexid";
    $result = $DB->get_records_sql($ssql);
    $maxRooms = 2;
    $roomsData = array();
    
    $length=$count($result);
        
        if($length>0){
            foreach($result as $room){
                $meetingData=$webex->meeting_GetMeeting($room->meetingid);
                print_r($meetingData);
                exit;
            }
        }
        
        for($i=$length-1;$i<$maxRooms;$i++){
            $meetingObject = stdClass();
            $meetingObject->name = "Room$i";
            $meetingObject->timestart = time();
            $meetingObject->intro = '';
            $data->duration= 0;
            continue;
            $meeting = $webex->meeting_CreateMeeting($meetingObject->name, date("m/d/Y h:i:s", $meetingObject->timestart), $meetingObject->intro, $meetingObject->duration / 60);
            $meetingObject->meetingid = $meeting;
            $meetingObject->id = $DB->insert_record('webex', $data);
            
            $fullurl = $webex->meeting_GethosturlMeeting($meetingObject->meetingid);
            $append = urlencode('&BU='.new moodle_url('/course/view.php', array('id'=>$cm->course)));
            $fullurl.=$append ;
            
            $meetingObject->url = $fullurl;
            $roomsData[] = $meetingObject;
            
            $room = new stdObject();
            $room->webexid = $meetingObject->id ;
        }
        
    
}

/**
 * Update a webex event.
 *
 * @author  Andrew Ramos
 * @param   object $data contains the form elements
 * @param   object $mform contains all forms keys and attributes
 * @return  bool true if it was updated, false otherwise.
 */
function webex_update_instance($data, $mform) {
    
    global $CFG, $DB, $USER;

    require_once($CFG->dirroot . '/mod/webex/locallib.php');
    require_once($CFG->dirroot . '/mod/webex/webex.php');
    $config = get_config('webex');

    $parameters = array();
    for ($i = 0; $i < 100; $i++) {
        $parameter = "parameter_$i";
        $variable = "variable_$i";
        if (empty($data->$parameter) or empty($data->$variable)) {
            continue;
        }
        $parameters[$data->$parameter] = $data->$variable;
    }
    
    /** 
     * Set correctly parameters field from DB.
     * @author Miguel P.
     * @since 15/06/2016
     * @paradiso
     */ 
    $meetingparameters = $DB->get_record('webex', array('id' => $data->instance),'parameters');
    
    if ($meetingparameters){
        foreach ($meetingparameters as $itemaux) {
           $parammk['params']=  unserialize($itemaux);            
        }
        //assign meeting key and others
        $parameters["MK"] = $parammk['params']['MK'];        
        if($data->webexservice)        
        {
          $parameters['webexservice'] =  $data->webexservice;
        }
        if($data->meeting_password)        
        {
          $parameters["PW"] =  $data->meeting_password;
        }         
        if($data->conference_type)        
        {
          $parameters['conference_type'] =  $data->conference_type;
        }
        if($data->video_type)        
        {
          $parameters["video_type"] =  $data->video_type;
        }        
    }

    $data->parameters = serialize($parameters);

    $displayoptions = array();
    if ($data->display == RESOURCELIB_DISPLAY_POPUP) {
        $displayoptions['popupwidth'] = $data->popupwidth;
        $displayoptions['popupheight'] = $data->popupheight;
    }
    if (in_array($data->display, array(RESOURCELIB_DISPLAY_AUTO, RESOURCELIB_DISPLAY_EMBED, RESOURCELIB_DISPLAY_FRAME))) {
        $displayoptions['printheading'] = (int) !empty($data->printheading);
        $displayoptions['printintro'] = (int) !empty($data->printintro);
    }
    $data->displayoptions = serialize($displayoptions);

    $data->externalurl = webex_fix_submitted_webex($data->externalurl);

    $data->timemodified = time();
    $data->id = $data->instance;

    // Attempt to update the event on webex.
    $displayoptions = array();
    if ($data->display == RESOURCELIB_DISPLAY_POPUP) {
        $displayoptions['popupwidth'] = $data->popupwidth;
        $displayoptions['popupheight'] = $data->popupheight;
    }
    if (in_array($data->display, array(RESOURCELIB_DISPLAY_AUTO, RESOURCELIB_DISPLAY_EMBED, RESOURCELIB_DISPLAY_FRAME))) {
        $displayoptions['printheading'] = (int) !empty($data->printheading);
        $displayoptions['printintro'] = (int) !empty($data->printintro);
    }

    if ($data->timedurationminutes) {
        $data->duration = $data->timedurationminutes;
    } 
    // else {
    //     $data->duration = $data->timedurationuntil - $data->timestart;
    // }
    
    $data->displayoptions = serialize($displayoptions);
    $data->externalurl = webex_fix_submitted_webex($data->externalurl);
    $data->timemodified = time();

    $webexoptions = new stdclass();
    if(isset($data->meeting_password) && $data->meeting_password!='') {
        $webexoptions->meeting_password = trim($data->meeting_password);
    } else {
        $webexoptions->meeting_password = 'paradiso';   
    }

    if (isset($data->attendees) && $data->attendees != '' ) {
        $webexoptions->attendees = str_getcsv(trim($data->attendees),','); 
    }

    if(isset($data->video_type) && $data->video_type) {
        $webexoptions->video_type= $data->video_type;
    }

    if(isset($data->conference_type) && $data->conference_type) {
       $webexoptions->conference_type = $data->conference_type;
    }

    $webex = new WebEx($config->webexid, $config->webexpassword, $config->siteid, $config->partnerid, $config->siteurl);
    $webex->user_SetUser($config->webexid, $USER->firstname,$USER->lastname);

    // If the WebEx service is Meeting Center
    if ($data->webexservice === 'meetingservice') {

        $meeting = $webex->meeting_SetMeeting(
                        $data->id,
                        $data->name,
                        date("m/d/Y H:i:s", ($data->timestart)),
                        $data->intro,
                        $data->duration,
                        'NO_REPEAT',
                        strtoupper(date("l",$data->timestart)),
                        $webexoptions
        );

        // If there was an error, then notify the user.
        if(!$meeting) {
            print_error(
                'The event could not be updated.',
                '',
                new moodle_url($CFG->wwwroot.'/course/view.php',
                array('id'=>$data->course))
            );
            return false;  
        } else {
            // ... Otherwise, update the meeting record.
            $DB->update_record('webex', $data);
            return true;
        }

    } // End if.

    return true;
}

/**
 * Delete webex instance.
 *
 * NEED WORK, ONLY ONE TYPE OF EVENT IS BEING DELETED
 * @param int $id
 * @return bool true
 */
function webex_delete_instance($id) {
    global $DB, $CFG;
    require_once($CFG->dirroot . '/mod/webex/webex.php');
    $config = get_config('webex');

    $url = $DB->get_record('webex', array('id' => $id));

    $file = $url->recordfile;
    $webex = new WebEx($config->webexid, $config->webexpassword, $config->siteid, $config->partnerid, $config->siteurl );
    $webex->meeting_DelMeeting($url->meetingid);
    // $webex->training_DelTrainingSession($url->meetingid);
    $event = $DB->get_record('event', array('id' => $url->eventid));
    
    if (!$url) {
        return false;
    }
    else
        $DB->delete_records('webex', array('id' => $url->id));
    if (empty($event)) {
        return false;
    }
    else
        $DB->delete_records('event', array('id' => $event->id));

    if (!empty($file))
        unlink($CFG->dirroot . "/mod/webex/records/" . $file);



    return true;
}

/**
 * Return use outline
 * @param object $course
 * @param object $user
 * @param object $mod
 * @param object $url
 * @return object|null
 */
function webex_user_outline($course, $user, $mod, $url) {
    global $DB;

    if ($logs = $DB->get_records('log', array('userid' => $user->id, 'module' => 'webex',
        'action' => 'view', 'info' => $url->id), 'time ASC')) {

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
 * @param object $url
 */
function webex_user_complete($course, $user, $mod, $url) {
    global $CFG, $DB;

    if ($logs = $DB->get_records('log', array('userid' => $user->id, 'module' => 'webex',
        'action' => 'view', 'info' => $url->id), 'time ASC')) {
        $numviews = count($logs);
        $lastlog = array_pop($logs);

        $strmostrecently = get_string('mostrecently');
        $strnumviews = get_string('numviews', '', $numviews);

        echo "$strnumviews - $strmostrecently " . userdate($lastlog->time);
    } else {
        print_string('neverseen', 'webex');
    }
}

/**
 * Returns the users with data in one webex
 *
 * @todo: deprecated - to be deleted in 2.2
 *
 * @param int $urlid
 * @return bool false
 */
function webex_get_participants($urlid) {
    return false;
}

/**
 *
 * @param int $course course
 * @param int $tstart Start time of time range for events
 * @param int $tend End time of time range for events
 * @param boolean $withduration whether only events starting within time range selected
 *                              or events in progress/already started selected as well
 * @param boolean $ignorehidden whether to select only visible events or all events
 * @return array meetings Objects
 */
function webex_get_last_meetings($course) {
    global $DB, $CFG;

    $timeclause = 'timestart+duration>=UNIX_TIMESTAMP(NOW())';
    if (!empty($whereclause)) {
        // We have additional constraints
        $whereclause = $timeclause . ' AND (' . $whereclause . ')';
    } else {
        // Just basic time filtering
        $whereclause = $timeclause;
    }

    $sql = "SELECT w.*,md.id as cmid FROM {webex} w,{course_modules} md,{modules} m WHERE 
              w.id = md.instance AND md.course = :course AND $whereclause AND m.name='webex' AND m.id=md.module ORDER BY w.timestart ASC";

    $params['course'] = $course;
    $events = $DB->get_records_sql($sql, $params);


    if ($events === false) {
        $events = array();
    }
    return $events;
}

function webex_get_active_meetings($course) {
    global $DB, $CFG;

    $timeclause = 'timestart+duration>=UNIX_TIMESTAMP(NOW())';
    if (!empty($whereclause)) {
        // We have additional constraints
        $whereclause = $timeclause . ' AND (' . $whereclause . ')';
    } else {
        // Just basic time filtering
        $whereclause = $timeclause;
    }

    $sql = "SELECT w.*,md.id as cmid FROM {webex} w,{course_modules} md,{modules} m WHERE 
              w.id = md.instance  AND md.course = :course AND $whereclause AND m.name='webex' AND m.id=md.module ORDER BY w.timestart ASC";

    $params['course'] = $course;
    $events = $DB->get_records_sql($sql, $params);

    if ($events === false) {
        $events = array();
    }
    return $events;
}

function webex_get_history($course) {
    global $DB, $CFG;

    $timeclause = 'timestart+duration<UNIX_TIMESTAMP(NOW())';
    if (!empty($whereclause)) {
        // We have additional constraints
        $whereclause = $timeclause . ' AND (' . $whereclause . ')';
    } else {
        // Just basic time filtering
        $whereclause = $timeclause;
    }

    $sql = "SELECT w.*,md.id as cmid FROM {webex} w,{course_modules} md,{modules} m WHERE 
              w.id = md.instance  AND md.course = :course AND $whereclause AND m.name='webex' AND m.id=md.module ORDER BY w.timestart ASC";

    $params['course'] = $course;
    $events = $DB->get_records_sql($sql, $params);

    

    if ($events === false) {
        $events = array();
    }

    return $events;
}

/**
 * Given a course_module object, this function returns any
 * "extra" information that may be needed when printing
 * this activity in a course listing.
 *
 * See {@link get_array_of_activities()} in course/lib.php
 *
 * @param object $coursemodule
 * @return object info
 */
function webex_get_coursemodule_info($coursemodule) {
    global $CFG, $DB;
    require_once("$CFG->dirroot/mod/webex/locallib.php");

    if (!$url = $DB->get_record('webex', array('id' => $coursemodule->instance), 'id, name, display, displayoptions, externalurl, parameters, intro, introformat')) {
        return NULL;
    }

    $info = new cached_cm_info();
    $info->name = $url->name;

    $display = webex_get_final_display_type($url);

    if ($display == RESOURCELIB_DISPLAY_POPUP) {
        $fullurl = "$CFG->wwwroot/mod/webex/view.php?id=$coursemodule->id&amp;redirect=1";
        $options = empty($url->displayoptions) ? array() : unserialize($url->displayoptions);
        $width = empty($options['popupwidth']) ? 620 : $options['popupwidth'];
        $height = empty($options['popupheight']) ? 450 : $options['popupheight'];
        $wh = "width=$width,height=$height,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes";
        $info->onclick = "window.open('$fullurl', '', '$wh'); return false;";
    } else if ($display == RESOURCELIB_DISPLAY_NEW) {
        $fullurl = "$CFG->wwwroot/mod/webex/view.php?id=$coursemodule->id&amp;redirect=1";
        $info->onclick = "window.open('$fullurl'); return false;";
    }

    if ($coursemodule->showdescription) {
        // Convert intro to html. Do not filter cached version, filters run at display time.
        $info->content = format_module_intro('webex', $url, $coursemodule->id, false);
    }

    return $info;
}

/**
 * This function extends the global navigation for the site.
 * It is important to note that you should not rely on PAGE objects within this
 * body of code as there is no guarantee that during an AJAX request they are
 * available
 *
 * @param navigation_node $navigation The webex node within the global navigation
 * @param stdClass $course The course object returned from the DB
 * @param stdClass $module The module object returned from the DB
 * @param stdClass $cm The course module instance returned from the DB
 */
function webex_extend_navigation($navigation, $course, $module, $cm) {
    /**
     * This is currently just a stub so that it can be easily expanded upon.
     * When expanding just remove this comment and the line below and then add
     * you content.
     */
    $navigation->nodetype = navigation_node::NODETYPE_LEAF;
}

/**
 * Return a list of page types
 * @param string $pagetype current page type
 * @param stdClass $parentcontext Block's parent context
 * @param stdClass $currentcontext Current context of block
 */
function webex_page_type_list($pagetype, $parentcontext, $currentcontext) {
    $module_pagetype = array('mod-webex-*' => get_string('page-mod-webex-x', 'webex'));
    return $module_pagetype;
}
function webex_cron(){
    global $DB, $CFG;
return true;    
    mtrace('Start Webex Cron');
    $timeclause = 'timestart+duration<UNIX_TIMESTAMP(NOW())';
    if (!empty($whereclause)) {
        // We have additional constraints
        $whereclause = $timeclause . ' AND (' . $whereclause . ')';
    } else {
        // Just basic time filtering
        $whereclause = $timeclause;
    }

    $sql = "SELECT w.*,md.id as cmid FROM {webex} w,{course_modules} md,{modules} m WHERE 
              w.id = md.instance  AND $whereclause AND m.name='webex' AND m.id=md.module ORDER BY w.timestart ASC";

    $events = $DB->get_records_sql($sql);
    
    require_once($CFG->dirroot . '/mod/webex/webex.php');
    require_once($CFG->dirroot . '/mod/webex/NBRSoapClient/SwASoapClient.php');
    $config = get_config('webex');

    $webex = new WebEx($config->webexid, $config->webexpassword, $config->siteid, $config->partnerid, $config->siteurl . '/WBXService/XMLService');
    $NBRWSDLPath = $CFG->wwwroot . "/mod/webex/NBR_File_Open.wsdl";
    
    $client = new SwASoapClient($NBRWSDLPath);
    mtrace('Event Count ::'.count($events));
    foreach ($events as $item) {
        
        if (empty($item->recordfile) || !file_exists($CFG->dirroot.'/mod/webex/records/'.$item->recordfile )) {
            $recorid = $webex->ep_LstRecording($item->meetingid, $config->webexid);
            
            
            if (!empty($recorid)) {
                mtrace('RecordID ::'.$recorid);
                try {
                    $resp = $client->__call("downloadFile", array("siteId" => $config->siteid, "serviceName" => "MC", "userName" => $config->webexid, "password" => $config->webexpassword, "recordId" => $recorid));
                } catch (SoapFault $sf) {
                    $resp = $client->ResultObject->Response;
                }
                mtrace('ClientResp::'.$resp);
                if (!empty($resp) && !empty($client->ResultObject->FileName)) {
                    mtrace('RecordNAME::'.$client->ResultObject->FileName);
                    $item->recordfile = $client->ResultObject->FileName;
                    $item->recordid = $recorid;
                    
                    mtrace('UPDATE {webex} SET recordfile="'.$item->recordfile.'",recordid="'.$item->recordid.'" WHERE id='.$item->id);
                    $DB->execute('UPDATE {webex} SET recordfile="'.$item->recordfile.'",recordid="'.$item->recordid.'" WHERE id='.$item->id);
                }
            }
        }else{
            mtrace($CFG->dirroot.'mod/webex/records/'.$item->recordfile);
        }
    }
    
    mtrace('Finish Webex Cron');
}
function CalculateDuration($duration) {

    $result = new stdClass();
    if ($duration == 0) {
        return null;
    }
    $start = $duration / 60;
    $units = "Minutes";
    if ($start < 1)
        $start = 1;
    if ($start == 1)
        $units = "Minute";

    if ($start >= 60) {
        $units = "Hours";
        $start = $start / 60;
        if ($start == 1)
            $units = "Hour";
        if ($start >= 24) {

            $units = "Days";
            $start = $start / 24;
            if ($start == 1)
                $units = "Day";
        }
    }

    if ($start == 1) {
        $result->duration = $start;
    } else {
        if (is_int($start))
            $result->duration = $start;
        else
            $result->duration = number_format($start, 1);
    }


    $result->units = $units;
    return $result;
}