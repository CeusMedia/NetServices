<?php
$uri    = "http://mydomain.tld/myServicePath/";     //  URI to path served by ENS
$client = new \CeusMedia\NetServices\Client($uri);  //  create ENS client pointing to server URI
#$client->setBasicAuth("myUser", "myPassword");     //  use Basic Authentication if needed

try{                                                //  try because service requests can fail for several reasons
    $response = $client->get(                       //  get response from ENS server
        'getTimestamp',                             //  call service 'getTimestamp'
        'xml',                                      //  to respond in XML
        array('output' => 'c')                      //  in PHP time format 'c'
    );
    print 'Response: '.$response.PHP_EOL;           //  display realized response of service call
    #$request = $client->getRequests(TRUE);         //  get complete info about last request and response
}
catch(Exception $e){                                //  catch exception if request failed
    UI_HTML_Exception_Page::display($e);            //  replace by your own exception handling
}
?>
