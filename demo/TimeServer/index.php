<?php
(@include '../../vendor/autoload.php') or die('Please use composer to install required packages.' . PHP_EOL);

require_once 'TimeServices.php';

$serviceFile	= 'services.xml';
$servicePoint	= new \CeusMedia\NetServices\Point( $serviceFile );
$serviceHandler	= new \CeusMedia\NetServices\Handler( $servicePoint );
$request		= new Net_HTTP_Request_Receiver();
$serviceName	= trim( $request->get( '___NetServicePath' ) );

//  --  SERVICE HANDLING  --  //
if( $serviceName )
	if( $servicePoint->hasService( $serviceName ) )
		if( $serviceHandler->handle( $serviceName, $request ) )
			exit;

//  --  INDEX SCREEN  --  //
$data	= array(
	'codeClass'		=> highlight_file( 'TimeServices.php', TRUE ),
	'codeXmlDef'	=> highlight_file( 'services.xml', TRUE ),
	'codeMain'		=> highlight_file( 'index.php', TRUE ),
	'codeHtaccess'	=> highlight_file( '.htaccess', TRUE )
);

$page	= new UI_HTML_PageFrame();
$page->setTitle( 'Simple Time Server Demo | NetServices | Component Demo | Ceus Media' );
$page->addBody( UI_Template::render( 'index.phpt', $data ) );
$page->addStylesheet( 'https://cdn.ceusmedia.de/css/bootstrap.min.css' );
$page->addStylesheet( 'https://cdn.ceusmedia.de/css/bootstrap-responsive.min.css' );
$page->addStylesheet( 'style.css' );
$page->addStylesheet( '../demo.css' );
$page->addJavaScript( 'https://cdn.ceusmedia.de/js/jquery/1.10.2.min.js' );
$page->addJavaScript( 'https://cdn.ceusmedia.de/js/bootstrap.min.js' );
$page->addJavaScript( 'script.js' );
print $page->build();


?>
