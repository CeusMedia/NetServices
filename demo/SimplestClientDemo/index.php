<?php
(@include '../../vendor/autoload.php') or die('Please use composer to install required packages.' . PHP_EOL);
require_once '../functions.php';

/*  --  generate server URI  --  */
$protocol	= getEnv( 'HTTPS' ) ? 'https' : 'http';			//  HTTP protocol
$host		= getEnv( 'HTTP_HOST' );						//  get HTTP host
$path		= dirname( getEnv( 'REQUEST_URI' ) )."/";		//  get demo path
$path		.= 'DisclosureDemo/demo/';						//  go to demo services of DisclosureDemo
$serverURI	= $protocol."://".$host.$path;					//  combine the ENS server URI

/*  --  service call data  --  */
$service	= 'getTimestamp';								//  set service name, this is an example
$format		= 'json';										//  set service response format, you can choose depending on service supported formats
$data		= array(										//  parametric service data
	'output'			=> 'c',								//  format of date format output @see http://www.php.net/manual/en/function.date.php
	'compressResponse'	=> 'gzip',							//  activate HTTP compression using gzip
);

/*  --  configuration  --  */
if( !file_exists( 'config.ini' ) )
	copy( 'config.ini.dist', 'config.ini' );
$config		= (object) parse_ini_file( 'config.ini' );

/*  --  call service  --  */
$client		= new \CeusMedia\NetServices\Client( $serverURI );				//  create ENS service client pointing to server URI
if( $config->authUsername && $config->authPassword )
	$client->setBasicAuth( $config->authUsername, $config->authPassword );
try{
	$response	= $client->get( $service, $format, $data );	//  get response from ENS server
	$requests	= $client->getRequests();					//  get list of finished requests
//	var_dump( $requests[0] );die;
	$time		= $requests[0]['time'];						//  get duration of last service call
	$result		= '<span style="border: 2px solid red; background-color: yellow">'.$response.'</span>';
	$status		= '<span class="label label-success">Request passed</span>';
}
catch( Exception $e ){
	$time		= "-";
	$result		= '<em class="muted">An exception occured.</em>';
	$status		= '<span class="label label-important">Request failed</span>';
	$exception	= UI_HTML_Exception_View::render( $e );
	if( $e->getCode() >= 400 && $e->getCode() < 600 ){
		$exception	= ' <span class="muted">HTTP Error</span> <strong>'.$e->getCode().' &minus; '.Net_HTTP_Status::getText( $e->getCode() ).'</strong>';
	}
}

/*  --  generate demo page  --  */
if( !isset( $exception ) ){

}

$factsCall	= renderFacts( array(
	'Server URI'		=> $serverURI,
	'Service Name'		=> $service,
	'Response Format'	=> $format,
	'Parameter(s)'		=> 'output="c"',
	'Response Status'	=> $status,
	'Request Result'	=> $result,
	'Service Time'		=> number_format( (float) $time, 1, ".", "" ).' ms',
) );

$body	= '
<div class="container">
	<h1 class="muted">CeusMedia Components Demo</h1>
	<h2><a href="../">CeusMedia/NetServices</a>: Client Demo</h2>
	<h3>Service Call</h3>
	'.$factsCall.'
	'.( isset( $exception ) ? '<h3>Exception</h3>' : '' ).'
	'.( isset( $exception ) ? '<div class="exception">'.$exception.'</div>' :'' ).'
	<h3>Code</h3>
	<div class="code">'.highlight_file( 'request.phps', TRUE ).'</div>
</div>';

$page		= new UI_HTML_PageFrame();
$page->setTitle( 'Client Demo | NetServices | Component Demo | Ceus Media' );
$page->addStyleSheet( 'https://cdn.ceusmedia.de/css/bootstrap.min.css' );
$page->addStylesheet( 'https://cdn.ceusmedia.de/css/bootstrap-responsive.min.css' );
$page->addStylesheet( '../demo.css' );
$page->addBody( $body );
print $page->build();
?>
