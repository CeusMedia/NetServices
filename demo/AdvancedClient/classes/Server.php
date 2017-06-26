<?php
class Server
{
	protected $config;
	protected $request;

	public function __construct()
	{
		$this->config	= parse_ini_file( 'config/config.ini' );
		$this->request	= new Net_HTTP_Request_Receiver();
		$numberBytes	= $this->dispatch();
	}

	protected function dispatch()
	{
		$path			= $this->request->get( 'path' );
		$parts			= explode( '/', trim( $path ) );
		$serviceName	= array_pop( $parts );
		$servicePath	= $parts ? implode( '/', $parts ).'/' : '';
		$serviceFile	= 'services' . ( $parts ? '.'.implode( '.', $parts ) : '' );
		$fileDefinition	= 'config/'.$serviceFile.'.xml';
		$fileCache		= 'config/'.$serviceFile.'.cache';

#		if( !file_exists( $fileDefinition ) )
#			$this->throw404();

		$servicePoint	= new \CeusMedia\NetServices\Point( $fileDefinition, $fileCache );
		if( $serviceName )
		{
			if( !$this->request->has( '___test' ) )
				return $this->handleService( $servicePoint, $serviceName );
			$page	= $this->testService( $servicePoint, $serviceName, $servicePath );
		}
		else
			$page	= $this->indexServices( $servicePoint, $servicePath );

		$response	= new Net_HTTP_Response();
		$response->setBody( $page->build() );
		return Net_HTTP_Response_Sender::sendResponse( $response );
	}

	protected function getEmptyPage()
	{
		$page		= new UI_HTML_PageFrame();
		$page->setBaseHref( 'http://'.getEnv( 'HTTP_HOST' ).dirname( $_SERVER['PHP_SELF'] ).'/' );
		$page->addJavaScript( $this->config['path.js.lib'].'jquery/1.10.2.min.js' );
		$page->addJavaScript( $this->config['path.js.lib'].'bootstrap.min.js' );
		$page->addStyleSheet( $this->config['path.css.lib'].'bootstrap.min.css' );
		$page->addStyleSheet( $this->config['path.css.lib'].'bootstrap-responsive.min.css' );
		$page->addStyleSheet( 'css/services.css' );
		$page->addFavouriteIcon( 'images/favicon.ico' );
		return $page;
	}

	protected function handleService( \CeusMedia\NetServices\Point $servicePoint, $serviceName )
	{
		$handler	= new \CeusMedia\NetServices\Handler( $servicePoint );
		$length		= $handler->handle( $serviceName, $this->request );
		return $length;
	}

	protected function indexServices( \CeusMedia\NetServices\Point $servicePoint, $path )
	{
		$index		= new HTML_Index( $servicePoint );
		$content	= $index->buildContent( $path );

		$page	= $this->getEmptyPage();
		$page->setTitle( $servicePoint->getTitle() );
		$page->addBody( $content );
		$page->addStyleSheet( 'css/services.index.table.css' );
		$page->addStyleSheet( 'css/services.index.filter.css' );
//		$page->addJavaScript( $this->config['path.js.lib'].'jquery/color.js' );
		$page->addJavaScript( $this->config['path.js.lib'].'jquery/cmServiceIndex/0.1.js' );
//		$page->addJavaScript( $this->config['path.js.lib'].'jquery/cmBlitz/0.1.js' );
		return $page;
	}

	protected function testService( \CeusMedia\NetServices\Point $servicePoint, $serviceName, $path )
	{
		$index		= new HTML_Test( $servicePoint );
		if( $this->config['auth.username'] && $this->config['auth.password'] )
			$index->setAuth( $this->config['auth.username'], $this->config['auth.password'] );
		$content	= $index->buildContent( $serviceName, $this->request, $path );

		$page	= $this->getEmptyPage();
		$page->addBody( $content );
		$page->addStyleSheet( './css/services.test.css' );
//		$page->addStyleSheet( 'css/tabs.css' );
		$page->addJavaScript( $this->config['path.js.lib'].'jquery/cmExceptionView/0.1.js' );
//		$page->addJavaScript( $this->config['path.js.lib'].'jquery/ui/1.8.4/min.js' );
//		$page->addStyleSheet( $this->config['path.js.lib'].'jquery/ui/1.8.4/css/smoothness.css' );
		$page->addStyleSheet( $this->config['path.js.lib'].'jquery/cmExceptionView/0.1.css' );
		$page->setTitle( $serviceName . " @ " . $servicePoint->getTitle() );
		return $page;
	}
}
?>
