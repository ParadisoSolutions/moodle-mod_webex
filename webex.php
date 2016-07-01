<?php
//WEBEX XML API CLIENT BY ESTEBAN MOLINA
date_default_timezone_set ("America/New_York");
class WebEx {

	public $webExID;
	private $password;
	private $siteID;
	private $partnerID;
	private $siteURL;		
        
        
        
	public function __construct($webExID, $password, $siteID, $partnerID, $siteURL) {
		global $CFG;
                $this->webExID = $webExID;
		$this->password = $password;
		$this->siteID = $siteID;
		$this->partnerID = $partnerID;
		$this->siteURL = $siteURL;
               
                
	}
	
	private function transmit($payload) {
		
		// Generate XML Payload
		$xml = '<serv:message xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">';
		$xml .= '<header>';
		$xml .= '<securityContext>';
		$xml .= '<webExID>'. $this->webExID .'</webExID>';
		$xml .= '<password>'. $this->password .'</password>';
		$xml .= '<siteID>'. $this->siteID .'</siteID>';
		$xml .= '<partnerID>'. $this->partnerID .'</partnerID>';
		$xml .= '</securityContext>';
		$xml .= '</header>';
		$xml .= '<body>';
		$xml .= '<bodyContent xsi:type="java:com.webex.service.binding.' . $payload['service']  . '">';		
		$xml .= $payload['xml'];
		$xml .= '</bodyContent>';				
		$xml .= '</body>';
		$xml .= '</serv:message>';
		
		//pre($xml);
                
		$URL = $this->siteURL;
                
                //  Separate into Host and URI
                $Host = substr($URL, 0, strpos($URL, "/"));
                $URI = strstr($URL, "/");
                $ContentLength = strlen($xml);

                
                //  Generate the request header
                global $Debug_Mode;
                
                $Debug_Mode = 0;
                
                $fp = fsockopen("ssl://".$URL,443,$errno,$errstr);
                $Post =  "POST /WBXService/XMLService HTTP/1.0\n";
                $Post .= "Host: $URL\n";
                $Post .= "Content-Type: application/xml\n";
                $Post .= "Content-Length: ".strlen($xml)."\n\n";
                $Post .= "$xml\n";
                if($Debug_Mode){
                      echo "<hr>XML Sent $URL:<br><textarea cols=\"50\" rows=\"25\">".htmlspecialchars($xml)."</textarea><hr>";
                }
                if($fp){
                      fwrite($fp,$Post);
                      $response = "";
                      $header="";
                      do // loop until the end of the header
                        {
                                $header .= fgets ( $fp, 128 );

                        } while ( strpos ( $header, "\r\n\r\n" ) === false );

                        // now put the body in variable $body

                        while ( ! feof ( $fp ) )
                        {
                                $response .= fgets ( $fp, 128 );
                        }
 
                      /*
                      while (!feof($fp)) {
                            $response .= fgets($fp, 1024);
                      }
                       * 
                       */
                      if($Debug_Mode){
                            echo "<br>XML Received:<br><textarea cols=\"50\" rows=\"25\">".htmlspecialchars($response)."</textarea><hr>";
                      }

                      return $response;
                }
                else{
                      echo "$errstr ($errno)<br />\n";
                      return false;
                }		
		// Separate $siteURL into Host and URI for Headers
                
                return;
                 $host = substr($this->siteURL, 0, strpos($this->siteURL, "/"));
                $uri = strstr($this->siteURL, "/");	
		// Generate Request Headers
		$content_length = strlen($xml);
		$headers = array(
			"POST $uri HTTP/1.0",
			"Host: $host",
			"User-Agent: PostIt",
			"Content-Type: application/x-www-form-urlencoded",
			"Content-Length: ".$content_length,
			);
			
		// Post the Request
		$ch = curl_init('https://' . $this->siteURL);
		curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);			
		
		$response = curl_exec($ch);
		return $response;
	}
	
        public function site_GetSite(){
            $xml = '';
            $payload['xml'] = $xml;
            $payload['service'] =  str_replace("_", ".", __FUNCTION__);
            return $this->transmit($payload);
        }
        public function user_AuthenticateUser(){
            
        }
        public function user_CreateUser($firstname,$lastname,$username,$email,$pass,$hots){
             $xml= <<< HTML
             <firstName>$firstname</firstName>
                <lastName>$lastname</lastName>
                <webExId>$username</webExId>
                <email>$email</email>
                <password>$pass</password>
                <privilege>
                    <host>$hots</host>
                </privilege>
                <active>ACTIVATED</active>
                <sessionOptions>
                    <defaultSessionType>123</defaultSessionType>
                    <defaultServiceType>MeetingCenter</defaultServiceType>
                </sessionOptions>
                <meetingTypes>
                       <meetingType>123</meetingType>
                </meetingTypes>     
HTML;
              $payload['xml'] = $xml;
		$payload['service'] =  str_replace("_", ".", __FUNCTION__);
		$data = $this->transmit($payload);
                
              
                $xml = new SimpleXmlElement($data);
                
		return (string)$xml->children('serv', true)->body->bodyContent->children('meet',true)->meetingkey;
        }
	public function user_DelUser($WebexId){
            
            $xml= <<< HTML
             <webExId>$WebexId</webExId>
HTML;
              $payload['xml'] = $xml;
		$payload['service'] =  str_replace("_", ".", __FUNCTION__);
		$data = $this->transmit($payload);
                
              
        }
	//public function user_DelSessionTemplates()
	//public function user_GetloginTicket()
	//public function user_GetloginurlUser()
	//public function user_GetlogouturlUser()
        public function user_GetUser($WebexId){
             $xml= <<< HTML
             <webExId>$WebexId</webExId>
HTML;
              $payload['xml'] = $xml;
		$payload['service'] =  str_replace("_", ".", __FUNCTION__);
		$data = $this->transmit($payload);
        }
	//public function user_LstsummaryUser()
	public function user_LstsummaryUser($startFrom = '1', $maximumNum = '', $listMethod = '', $orderOptions = '', $dateScope = '' ) {
		$xml = '<listControl>';
		if($startFrom) $xml .= '<startFrom>'. $startFrom .'</startFrom>';
		if($maximumNum) $xml .= '<maximumNum>'. $maximumNum .'</maximumNum>';
		if($listMethod) $xml .= '<listMethod>'. $listMethod .'</listMethod>';	
		$xml .= '</listControl>';
		
		if($orderOptions) {
			$xml .= '<order>';
			foreach ($orderOptions as $options) {
				$xml .= '<orderBy>'. $options['By'] .'</orderBy>';
				$xml .= '<orderAD>'. $options['AD'] .'</orderAD>';
			}
			$xml .= '</order>';
		}
		
		if($dateScope) {
			$xml .= '<dataScope>';
			if($dateScope['regDateStart']) $xml .= '<regDateStart>'. $dateScope['regDateStart'] .'</regDateStart>';
			if($dateScope['timeZoneID']) $xml .= '<timeZoneID>'. $dateScope['timeZoneID'] .'</timeZoneID>';
			if($dateScope['regDateEnd']) $xml .= '<regDateEnd>'. $dateScope['regDateEnd'] .'</regDateEnd>';
			$xml .= '</dataScope>';
		}

		$payload['xml'] = $xml;
		$payload['service'] =  str_replace("_", ".", __FUNCTION__);	
                
                $data=$this->transmit($payload);
                $xml = new SimpleXmlElement($data);
		
                return $xml->children('serv', true)->body->bodyContent->children('meet',true);	
		
	}
	
        public function user_SetUser($WebexId,$name,$lastname){
            $xml= <<< HTML
            <webExId>$WebexId</webExId>
            <active>ACTIVATED</active>
                    <firstName>$name</firstName>
                    <lastName>$lastname</lastName>  
HTML;
            $payload['xml'] = $xml;
		$payload['service'] =  str_replace("_", ".", __FUNCTION__);
		$data = $this->transmit($payload);
        }
	//public function user_UploadPMRIImage()

	
        public function meeting_CreateMeeting($MeetingName, $MeetingDate, $Description, $Duration, $repeatType='NO_REPEAT', $expirationDate='', $meetingOptions){
           $Description = strip_tags($Description);
           
        $MeetingName = strip_tags($MeetingName);
        $participant = '';
        $telephoneOption = '';

        if (isset($meetingOptions->attendees)) {
            foreach ($meetingOptions->attendees as $email) {
                $participant .= '<attendee><person>  <email>' . $email . ' </email></person></attendee>';
            }
        }
        $enableMeetingOption = '<enableOptions>                    
                    <chat>TRUE</chat>
                    <audioVideo>TRUE</audioVideo>   
                    <attendeeList>TRUE</attendeeList>
                    <fileShare>TRUE</fileShare>';



        if ($meetingOptions->video_type == 2) {
            $enableMeetingOption .= ' <HQvideo>TRUE</HQvideo>'
                    . '<HDvideo>TRUE</HDvideo> ';
        } else if ($meetingOptions->video_type == 1) {
            $enableMeetingOption .= '<HQvideo>TRUE</HQvideo>';
        }
        if ($meetingOptions->conference_type == 1) {
            $enableMeetingOption .= '<voip>TRUE</voip>';
        }

        $enableMeetingOption .= '</enableOptions>';

        
         if($meetingOptions->conference_type==2){
             $telephoneOption = '<telephony>
				 <telephonySupport>CALLIN</telephonySupport>
				 <tollFree>TRUE</tollFree> 
                               
			       </telephony>';
            
        }
        
           
           
           
            $xml= <<< HTML
            <accessControl>
              <meetingPassword>$meetingOptions->meeting_password</meetingPassword>
            </accessControl>       
            <metaData>
                <confName>$MeetingName</confName>
                <meetingType>123</meetingType>
                <greeting>$Description</greeting>
              </metaData>
              <schedule>
                <startDate>$MeetingDate</startDate>
                <timeZoneID>11</timeZoneID>
                <duration>$Duration</duration>
            </schedule>
            <repeat>
                   <repeatType>$repeatType</repeatType>
            </repeat>
          $enableMeetingOption
         $telephoneOption 
HTML;
            
                    if ($participant) {
            $xml .= <<< HTML
                   <participants>
                       <attendees>                         
                       $participant                        
                      </attendees>               
                   </participants> 
HTML;
                    }    
             $payload['xml'] = $xml;
		$payload['service'] =  str_replace("_", ".", __FUNCTION__);
		$data = $this->transmit($payload);
               
        
               
                $xml = new SimpleXmlElement($data);
             
		return (string)$xml->children('serv', true)->body->bodyContent->children('meet',true)->meetingkey;
        }
	//public function meeting_CreateTeleconferenceSession();
            public function meeting_DelMeeting($meetingKey){
            $xml= <<< HTML
            <meetingKey>$meetingKey</meetingKey>
HTML;
             
             $payload['xml'] = $xml;
		$payload['service'] =  str_replace("_", ".", __FUNCTION__);
		$data = $this->transmit($payload);
                
        }
	public function meeting_GethosturlMeeting($key){
            
            $xml = "<meetingKey>$key</meetingKey>";
		$payload['xml'] = $xml;
		$payload['service'] =  str_replace("_", ".", __FUNCTION__);
		
                $data=$this->transmit($payload);
                
                $xml = new SimpleXmlElement($data);
                
		return (string)$xml->children('serv', true)->body->bodyContent->children('meet',true)->hostMeetingURL;	
        }
        public function meeting_GetMeeting($key){
            
            $xml = "<meetingKey>$key</meetingKey>";
		$payload['xml'] = $xml;
		$payload['service'] =  str_replace("_", ".", __FUNCTION__);
		
                $data=$this->transmit($payload);
                $xml = new SimpleXmlElement($data);
		return $xml->children('serv', true)->body->bodyContent->children('meet',true);	
        }
	//public function meeting_GetTeleconferenceSession();
        public function meeting_getMeetingByName($name){
            $data=$this->meeting_LstsummaryMeeting( '1', '20');
            foreach($data as $meeting){
                $aux = $meeting->confName;
                if((string)$aux===$name){ 
                    return $meeting;
                    
                }
            }
            return false;
            
        }
        public function meeting_LstsummaryMeeting($startFrom = '1', $maximumNum = '', $listMethod = '', $orderOptions = '', $dateScope = '' ){
            $xml = '<listControl>'. $sessionKey .'</listControl>';
		
                
		$xml = '<listControl>';
		if($startFrom) $xml .= '<startFrom>'. $startFrom .'</startFrom>';
		if($maximumNum) $xml .= '<maximumNum>'. $maximumNum .'</maximumNum>';
		if($listMethod) $xml .= '<listMethod>'. $listMethod .'</listMethod>';	
		$xml .= '</listControl>';
		
		if($orderOptions) {
			$xml .= '<order>';
			foreach ($orderOptions as $options) {
				$xml .= '<orderBy>'. $options['By'] .'</orderBy>';
				$xml .= '<orderAD>'. $options['AD'] .'</orderAD>';
			}
			$xml .= '</order>';
		}
		
		if($dateScope) {
			$xml .= '<dataScope>';
			if($dateScope['regDateStart']) $xml .= '<regDateStart>'. $dateScope['regDateStart'] .'</regDateStart>';
			if($dateScope['timeZoneID']) $xml .= '<timeZoneID>'. $dateScope['timeZoneID'] .'</timeZoneID>';
			if($dateScope['regDateEnd']) $xml .= '<regDateEnd>'. $dateScope['regDateEnd'] .'</regDateEnd>';
			$xml .= '</dataScope>';
		}

		$payload['xml'] = $xml;
		$payload['service'] =  str_replace("_", ".", __FUNCTION__);	
	
		$data=$this->transmit($payload);
                $xml = new SimpleXmlElement($data);
		
                return $xml->children('serv', true)->body->bodyContent->children('meet',true);	
                
        }
	//public function meeting_SetMeeting();
	//public function meeting_SetTeleconferenceSession();	

	//public function meeting_GetjoinurlMeeting()
	public function meeting_GetjoinurlMeeting($meetingKey, $attendeeName,$attEmail,$meetPw) {
		$xml = '<meetingKey>'. $meetingKey .'</meetingKey>';
		$xml .= '<attendeeName>'. $attendeeName .'</attendeeName>';
                $xml .= '<attendeeEmail>'. $attEmail .'</attendeeEmail>';
                $xml .= '<meetingPW>'. $meetPw .'</meetingPW>';

		$payload['xml'] = $xml;
		$payload['service'] =  str_replace("_", ".", __FUNCTION__);
		$data = $this->transmit($payload);	
                $xml = new SimpleXmlElement($data);
		
                return (string)$xml->children('serv', true)->body->bodyContent->children('meet',true)->joinMeetingURL;
                
		//return 
	}
	
    public function ep_LstOpenSession(){
                    $xml= <<< HTML
            <serviceType>EventCenter</serviceType> 
            <serviceType>MeetingCenter</serviceType> 
            <serviceType>TrainingCenter</serviceType> 
            <serviceType>SupportCenter</serviceType>
HTML;
             
             $payload['xml'] = $xml;
		$payload['service'] =  str_replace("_", ".", __FUNCTION__);
		$data = $this->transmit($payload);
                header("Content-Type:text/xml");
               // echo $data;
               // echo "<br/><br/><br/><br/><br/>";
    }    
    
     public function ep_LstsummarySession($session=null,$hoster=null,$status=null,$timestart=null,$timeend=null,$permanent=false){
            
            $hoster= (!empty($hoster)) ? "<hostEmail>$hoster</hostEmail>" : $hoster;
            $session  = (!empty($session)) ? "<sessionKey>$session</sessionKey>" : $session;
            $status = (!empty($status)) ? "<status>$status</status>" : $status;
            $endtimeend = (!empty($timeend)) ? "<endDateEnd>$timeend</endDateEnd>" : '';
            $timeend = (!empty($timeend)) ? "<startDateEnd>$timeend</startDateEnd>" : '';
            
            $timescope = (!empty($timestart)) ? "<dateScope>
                <startDateStart>$timestart</startDateStart>
                $timeend
                <endDateStart>$timestart</endDateStart>
                $endtimeend 
            </dateScope>" : '';
            
            $repeat = (!empty($permanent)) ?  "<recurrence>$permanent</recurrence>" : '';
                    
            $xml= <<< HTML
                    
            $session
            <listControl>
                <startFrom>1</startFrom>
                <maximumNum>200</maximumNum>
            </listControl>
            <order>
                <orderBy>STARTTIME</orderBy>
                <orderAD>DESC</orderAD>
            </order>
            $hoster
            $status
            $timescope
            $repeat
            <serviceTypes>
                
                <serviceType>TrainingCenter</serviceType>
            </serviceTypes>
            
HTML;
                $payload['xml'] = $xml;
               
                $payload['service'] =  str_replace("_", ".", __FUNCTION__);
                $data = $this->transmit($payload);
             
                $xml = new SimpleXmlElement($data);
                return $xml->children('serv', true)->body->bodyContent->xpath('ep:session');
        }
        
       
       public function ep_GetSessionInfo($key){
                    $xml= <<< HTML
            <sessionPassword>paradiso</sessionPassword>
            <sessionKey>$key</sessionKey>
HTML;
             
             $payload['xml'] = $xml;
		$payload['service'] =  str_replace("_", ".", __FUNCTION__);
		$data = $this->transmit($payload);
               
               $xml = new SimpleXmlElement($data);
		
                return $xml->children('serv', true)->body->bodyContent->children('ep',true);
             
    }
     public function user_GetlogouturlUser($WebexrrId,$BackURL){
             $xml= <<< HTML
             <webExID>$WebexrrId</webExID>
             <backURL>$BackURL</backURL>
HTML;
              $payload['xml'] = $xml;
		$payload['service'] =  str_replace("_", ".", __FUNCTION__);
		$data = $this->transmit($payload);
                $xml = new SimpleXmlElement($data);
                
		return (string)$xml->children('serv', true)->body->bodyContent->children('use',true)->userLogoutURL;
        }
    public function ep_LstRecording($SessionKey,$hostWebExID){
        
                    $xml= <<< HTML
            <listControl>
                <startFrom>0</startFrom>
                <maximumNum>10</maximumNum>
            </listControl>
            <sessionKey>$SessionKey</sessionKey>
            <hostWebExID>$hostWebExID</hostWebExID> 
            <returnSessionDetails>true</returnSessionDetails>
HTML;
             
   $payload['xml'] = $xml;
		$payload['service'] =  str_replace("_", ".", __FUNCTION__);
		$data = $this->transmit($payload);
                $xml = new SimpleXmlElement($data);
                $total=(string)$xml->children('serv', true)->body->bodyContent->children('ep',true)->matchingRecords->children('serv', true)->total;
               
                $childs = (array)$xml->children('serv', true)->body->bodyContent->children('ep',true);
                if(!is_array($childs['recording'])){
                    $childs['recording'] = array($childs['recording']);
                }
                return $childs['recording'];               
    }
    public function ep_GetOneClickSettings($WebexId){
         $xml= <<< HTML
              <hostWebExID>$WebexId</hostWebExID>
HTML;
             
             $payload['xml'] = $xml;
		$payload['service'] =  str_replace("_", ".", __FUNCTION__);
		$data = $this->transmit($payload);
               
    }

    public function ep_SetOneClickSettings($WebexId){
        $xml= <<< HTML
              <hostWebExID>$WebexId</hostWebExID>
                <oneClickMetaData>
                <serviceType>MeetingCenter</serviceType>
                <sessionType>123</sessionType>
                </oneClickMetaData>
HTML;
             
             $payload['xml'] = $xml;
		$payload['service'] =  str_replace("_", ".", __FUNCTION__);
		$data = $this->transmit($payload);
                
      
    }
	//public function event_CreateEvent();
	//public function event_DelEvent();
	//public function event_GetEvent();
        public function event_LstrecordedEvent(){
                $xml= <<< HTML
                <listControl>
                   <startFrom>1</startFrom> 
                   <maximumNum>10</maximumNum> 
                   <listMethod>OR</listMethod>
                </listControl> 
HTML;
             
             $payload['xml'] = $xml;
		$payload['service'] =  str_replace("_", ".", __FUNCTION__);
		$data = $this->transmit($payload);
               
                echo $data;
                echo "<br/><br/><br/><br/><br/>";
        }
	
	//public function event_LstsummaryProgram();
	public function event_LstsummaryProgram($programID = '') {
		if($programID)
			$xml = '<programID>' . $programID . '</programID>';
			
		$payload['xml'] = (!empty($xml)) ? $xml : '';
		$payload['service'] =  str_replace("_", ".", __FUNCTION__);	
	
		return $this->transmit($payload);
	}
	//public function event_SendInvitationEmail();
	//public function event_SetEvent();
	//public function event_UploadEventImage();	
	
	//public function event_LstsummaryEvent()		
	public function event_LstsummaryEvent($startFrom = '1', $maximumNum = '', $listMethod = '', $orderOptions = '', $programID = '', $dateScope = '') {
		
		$xml = '<listControl>';
		if($startFrom) $xml .= '<startFrom>'. $startFrom .'</startFrom>';
		if($maximumNum) $xml .= '<maximumNum>'. $maximumNum .'</maximumNum>';
		if($listMethod) $xml .= '<listMethod>'. $listMethod .'</listMethod>';	
		$xml .= '</listControl>';
		
		if($orderOptions) {
			$xml .= '<order>';
			foreach ($orderOptions as $options) {
				$xml .= '<orderBy>'. $options['By'] .'</orderBy>';
				$xml .= '<orderAD>'. $options['AD'] .'</orderAD>';
			}
			$xml .= '</order>';
		}
		
		if($programID)
			$xml .= '<programID>' . $programID . '</programID>';
		
		if($dateScope) {
			$xml .= '<dateScope>';
			if($dateScope['startDateStart']) $xml .= '<startDateStart>'. $dateScope['startDateStart'] .'</startDateStart>';
			if($dateScope['startDateEnd']) $xml .= '<startDateEnd>'. $dateScope['startDateEnd'] .'</startDateEnd>';
			if($dateScope['endDateStart']) $xml .= '<endDateStart>'. $dateScope['endDateStart'] .'</endDateStart>';
			if($dateScope['endDateEnd']) $xml .= '<endDateEnd>'. $dateScope['endDateEnd'] .'</endDateEnd>';
			$xml .= '</dateScope>';
		}
		
		$payload['xml'] = $xml;
		$payload['service'] =  str_replace("_", ".", __FUNCTION__);
		
		return $this->transmit($payload);
	}
	
	/*
	 *  Meeting Attendee Services
	 */
	//public function attendee_CreateMeetingAttendee()
	public function attendee_CreateMeetingAttendee($attendees) {
		$xml = '';
		foreach($attendees as $attendee) {
			//$xml .= '<attendees>';
			$xml .= '<person>';
			foreach($attendee['info'] as $attr => $val){
				if(!is_array($val) && !empty($val))
					$xml .= '<'.$attr.'>'.$val.'</'.$attr.'>';				
			
				if(is_array($val)) {
						$xml .= '<'.$attr.'>';					
					foreach($val as $att => $val) {
						if(!empty($val))
							$xml .= '<'.$att.'>'.$val.'</'.$att.'>';						
					}
						$xml .= '</'.$attr.'>';					
				}
			}
			$xml .= '</person>';
		
			foreach($attendee['options'] as $attr => $val){
				if(!empty($val))
					$xml .= '<'.$attr.'>'.$val.'</'.$attr.'>';		
			}
			//$xml .= '</attendees>';
		}
		
		$payload['xml'] = $xml;
		$payload['service'] =  str_replace("_", ".", __FUNCTION__);
		
		return $this->transmit($payload);			
	}
	
	//public function attendee_LstMeetingAttendee()
	public function attendee_LstMeetingAttendee($sessionKey) {
		$xml = '';
		$xml .= '<sessionKey>' . $sessionKey . '</sessionKey>';
		
		$payload['xml'] = $xml;
		$payload['service'] =  str_replace("_", ".", __FUNCTION__);
		
		return $this->transmit($payload);				
	}
				
	//public function attendee_RegisterMeetingAttendee()
	public function attendee_RegisterMeetingAttendee($attendees) {
		$xml = '';
		foreach($attendees as $attendee) {
			$xml .= '<attendees>';
			$xml .= '<person>';
			foreach($attendee['info'] as $attr => $val){
				if(!is_array($val) && !empty($val))
					$xml .= '<'.$attr.'>'.$val.'</'.$attr.'>';				
			
				if(is_array($val)) {
						$xml .= '<'.$attr.'>';					
					foreach($val as $att => $val) {
						if(!empty($val))
							$xml .= '<'.$att.'>'.$val.'</'.$att.'>';						
					}
						$xml .= '</'.$attr.'>';					
				}
			}
			$xml .= '</person>';
		
			foreach($attendee['options'] as $attr => $val){
				if(!empty($val))
					$xml .= '<'.$attr.'>'.$val.'</'.$attr.'>';		
			}
			$xml .= '</attendees>';
		}
		
		$payload['xml'] = $xml;
		$payload['service'] =  str_replace("_", ".", __FUNCTION__);
		
		return $this->transmit($payload);		
	}
	
	//public function history_LstmeetingattendeeHistory()
	public function history_LsteventattendeeHistory($meetingKey = '', $orderOptions = '', $startTimeScope = '', $endTimeScope = '', $confName = '', $confID = '', $listControl = '', $inclAudioOnly = false) {
		$xml = '';	
		
		if($meetingKey)
			$xml .= '<meetingKey>' . $meetingKey . '</meetingKey>';

		if($orderOptions) {
			$xml .= '<order>';
			foreach ($orderOptions as $options) {
				$xml .= '<orderBy>'. $options['By'] .'</orderBy>';
				$xml .= '<orderAD>'. $options['AD'] .'</orderAD>';
			}
			$xml .= '</order>';
		}
		
		if($startTimeScope) {
			$xml .= '<startTimeScope>';
			$xml .= '<sessionStartTimeStart>'. $startTimeScope['sessionStartTimeStart'] .'</sessionStartTimeStart>';
			$xml .= '<sessionStartTimeEnd>'. $startTimeScope['sessionStartTimeEnd'] .'</sessionStartTimeEnd>';
			$xml .= '</startTimeScope>';
		}
		
		if($endTimeScope) {
			$xml .= '<endTimeScope>';
			$xml .= '<sessionEndTimeStart>'. $endTimeScope['sessionEndTimeStart'] .'</sessionEndTimeStart>';
			$xml .= '<sessionEndTimeEnd>'. $endTimeScope['sessionEndTimeEnd'] .'</sessionEndTimeEnd>';
			$xml .= '</endTimeScope>';			
		}					

		if($confName)
			$xml .= '<confName>' . $confName . '</confName>';

		if($confID)
			$xml .= '<confID>' . $confID . '</confID>';
			
		if($listControl) {
			$xml .= '<listControl>';
			$xml .= '<serv:startFrom>'. $listControl['startFrom'] .'</serv:startFrom>';
			$xml .= '<serv:maximumNum>'. $listControl['maximumNum'] .'</serv:maximumNum>';			
			$xml .= '<serv:listMethod>'. $listControl['listMethod'] .'</serv:listMethod>';
			$xml .= '</listControl>';				
		}

		if($inclAudioOnly)
			$xml .= '<inclAudioOnly>' . $inclAudioOnly . '</inclAudioOnly>';							
					
		$payload['xml'] = $xml;
		$payload['service'] =  str_replace("_", ".", __FUNCTION__);		
		return $this->transmit($payload);	
	}
        public function training_CreateTrainingSession($MeetingName,$MeetingDate,$Description,$Duration,$repeatType='SINGLE',$endAfter='',$day='SUNDAY'){
          $Description = strip_tags($Description);
            $xml= <<< HTML
            <accessControl>
              <sessionPassword>paradiso</sessionPassword>
            </accessControl>       
            <metaData>
                <confName>$MeetingName</confName>
                <greeting>$Description</greeting>
              </metaData>
              <schedule>
                <startDate>$MeetingDate</startDate>
                <duration>$Duration</duration>
                   <timeZoneID>11</timeZoneID>
            </schedule>
HTML;
           if($repeatType!='SINGLE'){
               
               $xml.= <<< HTML
            <trainRepeatType>
                <repeatType>$repeatType</repeatType>
                <occurenceType>WEEKLY</occurenceType>
                <endAfter>$endAfter</endAfter>
                <dayInWeek>
                   <day>$day</day></dayInWeek>
             </trainRepeatType>
HTML;
           }
             
             $payload['xml'] = $xml;
		$payload['service'] =  str_replace("_", ".", __FUNCTION__);
		$data = $this->transmit($payload);
                
              
                $xml = new SimpleXmlElement($data);
                
		return (string)$xml->children('serv', true)->body->bodyContent->children('train',true)->sessionkey;
        }
          public function training_DelTrainingSession($meetingKey){
            $xml= <<< HTML
            <sessionKey>$meetingKey</sessionKey>
HTML;
             
             $payload['xml'] = $xml;
		$payload['service'] =  str_replace("_", ".", __FUNCTION__);
		$data = $this->transmit($payload);
                
        }
}
?>