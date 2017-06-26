<?php
$uri    = "http://mydomain.tld/myServicePath/";     //  URI to path served by ENS
$client = new \CeusMedia\NetServices\Client($uri);  //  create ENS client pointing to server URI
#$client->setBasicAuth("myUser", "myPassword");     //  use Basic Authentication if needed
try{                                                //  try because service requests can fail for several reasons
    $response = $client->get(                       //  get response from ENS server
        'myServiceName',                            //  call service 'myServiceName'
        'php',                                      //  to respond in PHP serial or 'json', 'xml', 'wddx', 'txt' etc.
        array(...)                                  //  with provided service parameters
    );

    ...                                             //  now it's your part to work with responded data
}
catch(Exception $e){                                //  catch exception if request failed
    UI_HTML_Exception_Page::display($e);            //  replace by your own exception handling
}
?>
