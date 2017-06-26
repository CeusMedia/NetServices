<?php
(@include '../../vendor/autoload.php') or die('Please use composer to install required packages.' . PHP_EOL);
require_once '../functions.php';

use \CeusMedia\NetServices\Client;

/*  --  generate server URI  --  */
$protocol	= getEnv( 'HTTPS' ) ? 'https' : 'http';				//  HTTP protocol
$host		= getEnv( 'HTTP_HOST' );							//  get HTTP host
$path		= dirname( getEnv( 'REQUEST_URI' ) )."/";			//  get demo path
$path		.= 'DisclosureDemo/demo/';							//  go to demo services of DisclosureDemo
$serverURI	= $protocol."://".$host.$path;						//  combine the ENS server URI

/*  --  configuration  --  */
if( !file_exists( 'config.ini' ) )
	copy( 'config.ini.dist', 'config.ini' );
$config		= (object) parse_ini_file( 'config.ini' );

/*  --  call service  --  */
$client		= new Client( $serverURI );
if( $config->authUsername && $config->authPassword )
	$client->setBasicAuth( $config->authUsername, $config->authPassword );


$tabException	= '<em class="muted">No exception.</em>';
$tabResponse	= '<em class="muted">Request failed.</em>';
$tabInfo		= '<em class="muted">Request failed.</em>';
$tabCall		= '<em class="muted">Request failed.</em>';
$tabHeaders		= '<em class="muted">Request failed.</em>';

/*  --  execute demo request  --  */
try{
	$response	= $client->get( 'getTimestamp', 'xml', array(
		'output' => 'c'
	) );
	$request	= $client->getRequests( TRUE );					//  get complete info about last request and response
	$tabInfo	= renderFacts( $request['info'] );
	$tabCall	= '
	<p>
		The service call performs an HTTP request like this:
	</p>'.renderFacts( array(
		'Method'	=> $request['method'],
		'URL'		=> '<a href="'.$request['url'].'">'.$request['url'].'</a>',
		'Response'	=> '<tt>'.$response.'</tt>',
		'Time'		=> number_format( $request['time'], 1, ".", "" ).' ms',
	) ).'
	<p>
		These are the sent HTTP headers:
	</p>'.renderFacts( $request['headers'] );
	$tabResponse	= '
	<p>
		The server\'s raw answer will look like this:
	</p>'.UI_HTML_Tag::create( 'pre', htmlentities( $request['response'], ENT_QUOTES, 'UTF-8' ), array( 'class' => 'code' ) ).'
	<p>
		Yes, this is XML, because the response format has been set to XML within the client call.<br/>
		<strong>But nevermind!</strong> The format is interpreted by the client. Your will receive the understood service return data.
	</p>';
}
catch( Exception $e ){
	$tabException	= UI_HTML_Exception_View::render( $e );
	if( $e->getCode() >= 400 && $e->getCode() < 600 ){
		$tabException	= '<h3><span class="muted">HTTP Error</span></h3><strong><big>'.$e->getCode().' &minus; '.Net_HTTP_Status::getText( $e->getCode() ).'</big></strong>';
	}
}

/*  --  render this demo  --  */
$tabClient		= '
<p>
	Basically the hereby used PHP client class calls a service on a NetService server and returns the understood response.<br/>
	Therefore is connecting to the server by given URL and credentials, beforehand.<br/>
</p>
<div class="code">'.highlight_file( 'request.phps', TRUE ).'</div>';

$tabs		= new \CeusMedia\Bootstrap\TabbableNavbar();
#$tabs->setBrand( 'Simple Client Demo' );
$tabs->add( 'tab6', 'PHP Code', $tabClient );
if( isset( $request ) ){
	$tabs->add( 'tab1', 'Service Call', $tabCall );
	$tabs->add( 'tab3', 'Response', $tabResponse );
	$tabs->add( 'tab4', 'cURL info', $tabInfo );
}
else
	$tabs->add( 'tab5', 'Exception', $tabException );

$body	= '
<div class="container">
	<h1 class="muted">CeusMedia Components Demo</h1>
	<h2><a href="../">CeusMedia/NetServices</a>: Simple Client Demo</h2>
	<p>
		This is the simplest demonstration of a clientside use of a NetServices server in PHP.
	</p>
	<div id="tabs">
		'.$tabs->render().'
	</div>
</div>';

$page	= new UI_HTML_PageFrame();
$page->setTitle( 'Simple Client Demo | NetServices | Component Demo | Ceus Media' );
$page->addBody( $body );
$page->addStylesheet( 'https://cdn.ceusmedia.de/css/bootstrap.min.css' );
$page->addStylesheet( 'https://cdn.ceusmedia.de/css/bootstrap-responsive.min.css' );
$page->addStylesheet( 'style.css' );
$page->addStylesheet( '../demo.css' );
$page->addJavaScript( 'https://cdn.ceusmedia.de/js/jquery/1.10.2.min.js' );
$page->addJavaScript( 'https://cdn.ceusmedia.de/js/bootstrap.min.js' );
$page->addJavaScript( 'script.js' );
print $page->build();
?>
