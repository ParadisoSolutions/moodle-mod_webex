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
 * Strings for component 'WebEx', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package    mod
 * @subpackage WebEx
 * @copyright  2012 Carlos kiyan, Walter Castillo , Luis Fukay
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['clicktoopen'] = 'Click {$a} here to go the meeting.';
$string['configdisplayoptions'] = 'Select all options that should be available, existing settings are not modified. Hold CTRL key to select multiple fields.';
$string['configframesize'] = 'When a web page or an uploaded file is displayed within a frame, this value is the height (in pixels) of the top frame (which contains the navigation).';
$string['configrolesinparams'] = 'Enable if you want to include localized role names in list of available parameter variables.';
$string['configsecretphrase'] = 'This secret phrase is used to produce encrypted code value that can be sent to some servers as a parameter.  The encrypted code is produced by an md5 value of the current user IP address concatenated with your secret phrase. ie code = md5(IP.secretphrase). Please note that this is not reliable because IP address may change and is often shared by different computers.';
$string['contentheader'] = 'Content';
$string['displayoptions'] = 'Available display options';
$string['displayselect'] = 'Display';
$string['displayselect_help'] = 'This setting determines how the WebEx is displayed. Options may include:

* Open - Only the WebEx is displayed in the browser window
* In pop-up - The WebEx is displayed in a new browser window without menus or an address bar
* In frame - The WebEx is displayed within a frame below the the navigation bar and WebEx description
* New window - The WebEx is displayed in a new browser window with menus and an address bar';
$string['displayselectexplain'] = 'Choose display type, unfortunately not all types are suitable for all WebExs.';
$string['externalurl'] = 'Your site';
$string['framesize'] = 'Frame height';
$string['invalidstoredwebex'] = 'Cannot display this resource, WebEx is invalid.';
$string['chooseavariable'] = 'Choose a variable...';
$string['invalidwebex'] = 'Entered WebEx is invalid';
$string['modulename'] = 'WebEx';
$string['modulenameplural'] = 'WebExs';
$string['neverseen'] = 'Never seen';
$string['optionsheader'] = 'Options';
$string['page-mod-webex-x'] = 'Any WebEx module page';
$string['parameterinfo'] = '&amp;parameter=variable';
$string['parametersheader'] = 'Session Information';
$string['parametersheader_help'] = 'Type the session number and the password for this session into each text box(es).';
$string['pluginadministration'] = 'WebEx module administration';
$string['pluginname'] = 'WebEx';
$string['chat:addinstance'] = 'Add a new WebEX meeting';
$string['popupheight'] = 'Popup height (in pixels)';
$string['popupheightexplain'] = 'Specifies default height of popup windows.';
$string['popupwidth'] = 'Popup width (in pixels)';
$string['popupwidthexplain'] = 'Specifies default width of popup windows.';
$string['printheading'] = 'Display WebEx name';
$string['printheadingexplain'] = 'Display WebEx name above content? Some display types may not display WebEx name even if enabled.';
$string['printintro'] = 'Display WebEx description';
$string['printintroexplain'] = 'Display WebEx description bellow content? Some display types may not display description even if enabled.';
$string['rolesinparams'] = 'Include role names in parameters';
$string['serverwebex'] = 'Server WebEx';
$string['webex:view'] = 'View WebEx';
$string['mettingkey'] = 'Session Number';
$string['password'] = 'Password';
//added by alok.kumar_paradiso 
$string['meetingtypeheader'] = 'Meeting Type';
$string['durationdetails'] = 'Start time and duration detail';
$string['startdatetime'] = 'Start date and time';
$string['enablemeeting'] = 'Enable Meeting Center';
$string['enablemeetingdesc'] = 'Enable Meeting Center will alow you to create meeting from course area.';
$string['enabletraining'] = 'Enable Training Center';
$string['enabletrainingdesc'] ='Enable Training Center will allow you to create the training session from course area.';
$string['servicetype'] = 'WebEx meeting setup ';
$string['servicetypedesc'] = 'WebEx meeting setup ';
$string['webexservice'] = 'WebEx service';
$string['updatewebexservice'] = '';
$string['webexservicerequired'] = 'WebEx service type is required';
$string['enablewebexdebug'] = 'Enable webex debug';
$string['enablewebexdebugdesc'] = 'Enable debug will display all  xml request and responce message, It\'s only for developer   ';
$string['repeattype'] = 'Recurrence:';
$string['timedurationminutes'] = 'Duration in minute';
$string['expiredate'] = 'Expire Date';
$string['startdatetime']= 'Start date and time';
$string['timedurationminutes'] = 'Duration in minutes';
$string['permanent'] = 'Permanent activity';
$string['start'] = 'Start date and time';
$string['end'] = 'End date and time';
$string['duration'] = 'Duration';
$string['description_label'] = 'Description';
$string['recordings_label']='Available recording(s)';
$string['prepare_label'] = 'Prepare your activity';
$string['access_label'] = 'Access your activity';
$string['access_text'] = 'This activity may be recorded When Checking the box , you are accepting to be recorded.<br/><br/>I accept ';
$string['support_label'] = 'Click here to get technical support';
$string['invitations_label'] = 'Send invitations to all participants';
$string['wizard_label'] = 'Click here for the setup wizard';
$string['manage_label'] = 'Manage participants list';
$string['activity_label']="Click here to get to the activity";
$string['dercordings_label']= 'Recordings';
$string['view_label'] = 'View';
$string['timedurationuntil'] ='End date and time';
$string['permanent_help']="The activity will no longer have a fixed date and time. All participants associated with this activity can then access at any time.";
$string['permanent'] = 'Permanent activity';
$string['webexservice_help'] = 'You can select the WebEx Meeting center service and WebEx training center service ';
$string['meeting_password'] = 'Meeting password';
$string['meeting_password_help'] = 'This Password is only required for the user who join it directly from webex site using meeting ID';
$string['attendees'] = 'Attendees email address';
$string['attendees_help'] = 'Add extra attendees email address separated by comma';

$string['modulename_help'] = 'The module allows users to create WebEx Meetings/Training by adding an activity instance and providing the meeting name, url (optional), start time, end time setting.';



$string['conference_type'] = 'WebEx conference type';
$string['conference_type_req'] = 'WebEx conference type is required';
$string['conference_type_help'] = 'Select the appropriate conference from list VoIP for inbuilt webex audio and WebEx Audio for Teleconferencing .';
$string['video_type'] = 'Video Quality ';
$string['video_type_req'] = 'Video quality is required';
$string['video_type_help'] = 'Video quality define the video  high-quality video or  high-definition video for this meeting.';

