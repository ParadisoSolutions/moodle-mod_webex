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
 * webex module main user interface
 *
 * @package    mod
 * @subpackage webex
 * @copyright  2012 Carlos kiyan, Walter Castillo , Luis Fukay
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once("$CFG->dirroot/mod/webex/locallib.php");
require_once($CFG->libdir . '/completionlib.php');
require_once $CFG->dirroot.'/mod/webex/webex.php';
  error_reporting(E_ERROR );
$config = get_config('webex');
$id       = optional_param('id', 0, PARAM_INT);        // Course module ID
$u        = optional_param('u', 0, PARAM_INT);         // webex instance id
$redirect = optional_param('redirect', 0, PARAM_BOOL);

$id       = optional_param('id', 0, PARAM_INT);        // Course module ID
$u        = optional_param('u', 0, PARAM_INT);         // webex instance id
$remind = optional_param('remind', 0, PARAM_BOOL);
$start = optional_param('start', 0, PARAM_BOOL);

$state=optional_param('ST','',PARAM_ALPHA); 
$WID=optional_param('WID','',PARAM_ALPHA); 
$response=optional_param('RS','',PARAM_ALPHA);
$attempt=optional_param('AT','',PARAM_ALPHA);  
    
if ($u) {  // Two ways to specify the module
    $url = $DB->get_record('webex', array('id'=>$u), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('webex', $url->id, $url->course, false, MUST_EXIST);

} else {
    $cm = get_coursemodule_from_id('webex', $id, 0, false, MUST_EXIST);
    $url = $DB->get_record('webex', array('id'=>$cm->instance), '*', MUST_EXIST);
}


$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
$url->parameters=unserialize($url->parameters);
require_course_login($course, true, $cm);
$PAGE->set_url(new moodle_url('/mod/webex/view.php', array('id' => $cm->id)));
$context = context_course::instance($course->id, MUST_EXIST);
//$PAGE->set_context($context);

require_capability('mod/webex:view', $context);

$completion = new completion_info($course);
$completion->set_module_viewed($cm);
//commented by alok.kumar_paradiso 
//add_to_log($course->id, 'webex', 'view', 'view.php?id='.$cm->id, $url->id, $cm->id);

$webexViewURL = new moodle_url('/mod/webex/view.php', array('id'=>$cm->id));
$webexEditURL = new moodle_url('/course/modedit.php#id_participantenrolment', array('update'=>$cm->id));
$webexEmailURL = new moodle_url('/mod/webex/view.php', array('id'=>$cm->id,'remind'=>1));
$webexStartURL = new moodle_url('/mod/webex/view.php', array('id'=>$cm->id,'start'=>1));
$isTeacher=has_capability('moodle/course:manageactivities', $context);
//$isPresentator = $DB->record_exists_sql("SELECT * FROM {webex_presentor} WHERE userid={$USER->id} AND webexid={$url->meetingid}");
 $isPresentator = false;

    $PAGE->set_title($url->name);
    $PAGE->set_heading($url->name);
 
    echo $OUTPUT->header();
    
$table = new html_table();
$table->width='100%';
$duration =CalculateDuration($url->duration);
$startLabel = "<strong>".get_string('start','webex')."</strong> ".userdate($url->timestart, '%H:%M %A,%d %B %Y');
$endLabel = "<strong>".get_string('end','webex')."</strong> ".userdate(($url->timestart+$url->duration),'%H:%M %A,%d %B %Y');
$durationLabel =  "<strong>".get_string('duration','webex').": </strong>";

if($url->parameters['webexservice']=='meetingservice'){
    
$durationLabel .=   $duration->duration.' '.$duration->units;
}
else if($url->parameters['webexservice']=='meetingservice')
{
$durationLabel .= (isset($url->parameters->permanent)) ? "Permanent" : $duration->duration.' '.$duration->units;
    
}

/**
* Add target='_blank' of link of webex for prevent errors of access from iframes
* @author Andres Ag.
* @since May 26 of 2016
* @paradiso
*/
$activityButton="<a href='$webexStartURL' target='_blank' class='webexlink disabled' id='startBtn'><i class='fa fa-play'></i>".get_string('activity_label','webex')."</a>";
$sendInvitation="<a href='$webexEmailURL' class='webexlink'><i class='fa fa-envelope-o'></i>".get_string('invitations_label','webex')."</a>";
$manageUsers="<a href='$webexEditURL' class='webexlink'><i class='fa fa-users'></i>".get_string('manage_label','webex')."</a>";
$techSupport="<a href='https://ps.webex.com' target='blank' class='webexlink'><i class='fa fa-question-circle'></i>".get_string('wizard_label','webex').".</a>";
$wizzard="<a href='http://ps.webex.com' target='blank' class='webexlink'><i class='fa fa-cog'></i>".get_string('support_label','webex').".</a>";

$cell = new html_table_cell("<h2>{$url->name}<h2>");
$cell->colspan=2;
$cell->style='text-align:center;';

$cell2 = new html_table_cell($durationLabel);
$cell2->colspan=2;

$cell3 = new html_table_cell("<strong>".get_string('prepare_label','webex')." :</strong>");
$cell3->colspan=2;

$cell4 = new html_table_cell("<strong>".get_string('access_label','webex').":</strong><br/>"
        . "<p style='text-align:center'>".get_string('access_text','webex')."<input id='acceptBtn' type='checkbox'></p>"
        . "<p style='text-align:center'>$activityButton</p><br/>");
$cell4->colspan=2;

/*$cell5 = new html_table_cell($sendInvitation);
$cell5->style='text-align:center;';

$cell6 = new html_table_cell($manageUsers);
$cell6->style='text-align:center;';*/

$cell7 = new html_table_cell("<strong>".get_string('description_label','webex')." :</strong>".$url->intro);
$cell7->colspan=2;

$cell8 = new html_table_cell($wizzard);
$cell8->style='text-align:center;';

$cell9 = new html_table_cell($techSupport);
$cell9->style='text-align:center;';

if($isTeacher){
    $table->data = array(
    array($cell),
    
    array($startLabel, $endLabel),
    array($cell2),
    array($cell7),
    array($cell3),
    array($cell8,$cell9) ,
    //  array($cell5,$cell6) ,
    array($cell4)
);
}else{
    $table->data = array(
    array($cell),
    
    array($startLabel, $endLabel),
    array($cell2),
    array($cell7),
    array($cell8,$cell9) ,
    array($cell4)
);
}

echo html_writer::table($table);
//echo "<link rel='stylesheet' type='text/css' href='{$CFG->wwwroot}/mod/webex/css/font-awesome.min.css'>";
?>
<style>
    .webexlink{background: #5c707c;color:white;font-size:15px;padding:10px;}
    .webexlink i{margin:10px;font-size:25px;}
    .disabled{opacity:.4;}
</style>
<script>
YUI().use('node', function(Y) {
   Y.one('#acceptBtn').set('checked',false);
    Y.one('#startBtn').on('click', function(e){
       if(!Y.one('#acceptBtn').get('checked'))
        e.preventDefault();
    });
    Y.one('#acceptBtn').on('click', function(e){
       if(e.target.get('checked')){
           Y.one('#startBtn').removeClass('disabled');
       }else{
           Y.one('#startBtn').addClass('disabled');
       }
    });
      
      
   
});
</script>    
<?php
/*$Users = parseWebexUsers(trim($config->webexdata));
$currentUser = array_values(array_filter($Users, function($item) {
    global $url;
    return $item->email == $url->hoster;
}))[0];
$currentUser = new stdClass();
*/

//$webex = new Webex($currentUser->email, $currentUser->pass, $config->siteid, $config->partnerid, $config->siteurl);   
$webex = new WebEx($config->webexid, $config->webexpassword, $config->siteid, $config->partnerid, $config->siteurl);   
$records= array();
try{
 $records =  $webex->ep_LstRecording($url->meetingid, $config->webexid);
}catch(moodle_exception $e){
    $records=array();
}
//$records= array();
 if(count($records)>0){
     
    echo "<h2>".get_string('dercordings_label','webex')."</h2>";

    $table = new html_table();
    $table->width='100%';
    
    foreach($records as $record){
        if($isTeacher)
            $table->data[] = array( $record->name, "<a href='{$record->streamURL}' target='blank'>".get_string('view_label','webex')."</a>");
        else
             $table->data[] = array( $record->name,"<a href='{$record->streamURL}' target='blank'>".get_string('view_label','webex')."</a>");
            //$table->data[] = array( $record->name, $record->createTime);
    }
    
    echo html_writer::table($table);


}

if(!empty($remind)){
        $cm->coursemodule=$cm->id;
        if($res=webex_send_notifications($url->parameters,$config,$course)==true){
              echo  $OUTPUT->error_text(get_string('succes_email','webex'));
        }
        else{
              echo  $OUTPUT->error_text(get_string('problem_email','webex').$res);
        }
    }
    
    if($state=='FAIL'){
        echo $OUTPUT->error_text($response);
        if($response=='AlreadyLogon'){
            $webex = new WebEx($config->webexid, $config->webexpassword, $config->siteid, $config->partnerid, $config->siteurl);   

             $logoutUrl=$webex->user_GetlogouturlUser($config->webexid,rawurlencode($webexViewURL));
             redirect($logoutUrl);
             
        }
    }elseif($state=='SUCCESS' && $attempt=="HM"){
        $webex = new WebEx($config->webexid, $config->webexpassword, $config->siteid, $config->partnerid, $config->siteurl);   
        $logoutUrl=$webex->user_GetlogouturlUser($config->webexid,rawurlencode($webexViewURL));
        redirect($logoutUrl);
    }
    
    
if(!empty($start)){
   
        $meeting = $webex->ep_GetSessionInfo($url->meetingid);
        
        if (($isTeacher) AND $meeting->status!="INPROGRESS") {
             try{  
                
                $currentSessions = $webex->ep_LstsummarySession(null,$webex->webExID,"INPROGRESS");
              
            }catch(moodle_exception $e){
                
                if($e->errorcode=='Sorry, no record found')
                    $currentSessions = 0;
                else
                    throw $e;
            }
           
          // if(count($currentSessions) > $config->meetingsbyuser){
               
               /*$newInstance = getRRInstance($config,$url->meetingid,date("m/d/Y H:i:s", ($url->timestart)-60*60),null);
               $webex->training_SetTrainingSession($url->meetingid,$USER->firstname,$newInstance->webexID);
               $webex = $newInstance;
               $url->hoster = $webex->webexID;
               $url->parameters->hoster=$url->hoster;
              $url->parameters=serialize($url->parameters);
               $DB->update_record('webex', $url);*/
              // throw  new moodle_exception(get_string('many_open','webex'),'webex',$PAGE->url);  
               
           //}
         
            $webex->user_SetUser($webex->webExID, $USER->firstname,$USER->lastname);
            $fullurl = $webex->meeting_GethosturlMeeting($url->meetingid);
 
            if(empty($fullurl)){
                echo $OUTPUT->error_text(get_string('accountserror','webex'));
            }else{
                $append = rawurlencode('&BU='.$webexViewURL);
                $fullurl.=$append ;
                $fullurl .= '&BU='.rawurlencode($webexViewURL);
                $url->fullurl=$fullurl;
                redirect($fullurl);
            } 

        }else {

            $fullurl = $webex->meeting_GetjoinurlMeeting($url->meetingid, fullname($USER),$USER->email,"m33t7n9");
            $fullurl .= '&BU='.  rawurlencode($webexViewURL);
            $url->fullurl=$fullurl;
            print_object($fullurl);
             redirect($fullurl);
        }
    } 
echo $OUTPUT->footer($course);          