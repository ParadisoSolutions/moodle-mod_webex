<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class SwASoapClient extends SoapClient
{
    private $_bHandleAsMime = false;
    private $_aAttachments  = array();
    private $_sUploadFile;
    
    public $ResultObject;
    
    public function __doRequest(
        $request, $location, $action, $version, $one_way=0
    ) {
        // Normal operation
        $sResult = parent::__doRequest($request, $location, $action, $version, $one_way);
         if(!empty($sResult)){
             $resp = $this->_parseMimeData($sResult);
             
            return $resp;
         }
        
    }

protected function _parseMimeData($data)
{
    $partStart=strpos($data,"------=_Part");
    $partEnd=strpos($data,"\r\n",$partStart);
    $partToken =substr($data, $partStart,$partEnd);
    $parts = split($partToken,$data);
    $results = array();
    $count=0;
    unset($parts[0]);
    $parts = array_values($parts); 
    
    $rsult = new stdClass();
    foreach($parts as $part){
        
        switch($count){
            case 0:
                //echo "<textarea>".$part."</textarea>";
                $ResponseStart = strpos($part,"<?xml version=\"1.0\" encoding=\"UTF-8\"?>");
                
                $ResponseEnd = strpos($part,"</soapenv:Envelope>",$headersStart);
                $rsult->Response=substr($part,$ResponseStart,$ResponseEnd+strlen("</soapenv:Envelope>"));
                 
              
                 
                break;
            case 1:
                $array = preg_split ('/$\R?^/m', $part);
                $rsult->FileName = $array[4];
                    
                //echo $rsult->FileName."<br/><br/><br/>";
                
            break;
            case 2 :
               
                
                
                preg_match_all('/$\R?^/m', $part, $matches, PREG_OFFSET_CAPTURE);
                $part = substr($part,$matches[0][3][1]);
                
                 
                $myFile = dirname(__FILE__)."/../records/".$rsult->FileName;
                
                $part=trim($part, "\r\n");
                $ptr = fopen($myFile, 'wb');
                
                fwrite($ptr, $part);
                fclose($ptr);
                    
                
                break;
        }
        $count++;

    }
    $this->ResultObject =$rsult;
 return $rsult->Response;
   
   
    
}

  
}
?>
