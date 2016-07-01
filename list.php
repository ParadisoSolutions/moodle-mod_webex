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
require_once("$CFG->dirroot/mod/webex/lib.php");
require_once("$CFG->dirroot/mod/webex/locallib.php");

$courseid = optional_param('course', SITEID, PARAM_INT);
    // Prevent caching of this page to stop confusion when changing page after making AJAX changes
$PAGE->set_cacheable(false);
preload_course_contexts($courseid);
$context = context_course::instance($courseid, MUST_EXIST);
require_course_login($courseid, true);
$PAGE->set_url("/mod/webex/list.php", array('course' => $courseid));



$records = webex_get_active_meetings($courseid);
$PAGE->set_title("List of Meetings");
$PAGE->set_heading("List of Meetings");
$PAGE->set_pagelayout('course');

 if ($PAGE->user_allowed_editing()) {
        $buttons = $OUTPUT->edit_button($PAGE->url);
        $PAGE->set_button($buttons);
    }
echo $OUTPUT->header();
echo $OUTPUT->heading();
?>
<style>
    
    .content{
        border:1px solid #DDD;
        padding :0 5%;
    }
    ul li{
        list-style: none;
    }
</style>
<?php
echo "<div class='content'>";
echo "<h2>List of Meetings</h2>";
echo '<ul class="section img-text" >';
foreach($records as $meeting){
    $resultDuration = CalculateDuration($meeting->duration);
    ?>
        
   <li class="activity webex modtype_webex">
      <div class="mod-indent" >
         <div class="activityinstance">
             <a class="" onclick="" href="<?php echo $CFG->wwwroot ?>/mod/webex/view.php?id=93">
                 <img src="<?php echo $CFG->wwwroot ?>/theme/image.php/standard/webex/1384315388/icon" class="iconlarge activityicon" alt=" " role="presentation">
                 <span class="instancename"><?php echo $meeting->name; ?></span>
             </a>
             <p>
             <?php echo $meeting->intro ?>
             <span><?php echo date('h:i A D,d M,Y',$meeting->timestart) ?></span><br/>
             <span> Duration: <?php echo $resultDuration->duration." ".$resultDuration->units ?><br/>
             <a href='<?php echo "{$CFG->wwwroot}/mod/webex/view.php?id=".$meeting->cmid ?>'>Go to Meeting</a>
             </p>
         </div>
      </div>
   </li>
 

<?php

}   

$records = webex_get_history($courseid);
echo "</ul>
<h2>History</h2>    
<ul>";


foreach($records as $meeting){
        $resultDuration = CalculateDuration($meeting->duration);
        
    ?>
        
   <li class="activity webex modtype_webex">
      <div class="mod-indent" >
         <div class="activityinstance">
            
                 <img src="<?php echo $CFG->wwwroot ?>/theme/image.php/standard/webex/1384315388/icon" class="iconlarge activityicon" alt=" " role="presentation">
                 <span class="instancename"><?php echo $meeting->name; ?></span>

              <p>
             <?php echo $meeting->intro ?>
             <span><?php echo date('h:i A D,d M,Y',$meeting->timestart) ?></span><br/>
             <span> Duration: <?php echo $resultDuration->duration." ".$resultDuration->units ?>  <br/>
            <?php if(!empty($meeting->recordid)){
                $file = str_replace(".arf","",$meeting->recordfile);
                echo "<a href='{$CFG->wwwroot}/mod/webex/records/{$meeting->recordfile}'>Download record: $file</a>";
                
            } ?>
             </p>
         </div>
      </div>
   </li>
 

<?php

}  
echo "</ul>";
echo "</div><a href='{$CFG->wwwroot}/course/view.php?id=$courseid'>Back to course</a>";
echo $OUTPUT->footer();


