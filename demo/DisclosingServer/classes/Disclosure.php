<?php
class Disclosure
{
	protected $counter		= 0;
	protected $time			= 0;
	public $fileDisclosure	= 'config/services.disclosure.xml';
	public $cacheDisclosure	= 'config/services.disclosure.cache';

	protected function getIndexData( $serviceName, $requestData = array() )
	{
		$this->counter	++;
		$clock		= new Alg_Time_Clock();
		$response	= $this->servicePoint->callService( $serviceName, 'wddx', $requestData );
		$decoder	= new \CeusMedia\NetServices\Decoder();
		$this->time	+= $clock->stop( 6, 0 );
		return $decoder->decodeResponse( $response, 'wddx' );
	}

	public function display()
	{
		$response	= new Net_HTTP_Response;
		$response->setBody( $this->render() );
		Net_HTTP_Response_Sender::sendResponse( $response );
	}

	public function render()
	{
		if( !file_exists( $this->fileDisclosure ) )
			throw new Exception( 'No disclosure service available' );

		$clock			= new Alg_Time_Clock();
		$buffer			= new UI_OutputBuffer();
		$this->servicePoint	= new \CeusMedia\NetServices\Point( $this->fileDisclosure, $this->cacheDisclosure );

		$paths	= $this->getIndexData( 'getPaths' );
		$list	= array();
		foreach( $paths as $path )
		{
			$title		= $this->getIndexData( 'getPathTitle', array( 'path' => $path ) );
			$desc		= $this->getIndexData( 'getPathDescription', array( 'path' => $path ) );
			$labelPath	= UI_HTML_Tag::create( 'h2', rtrim( $path, '/' ) );
			$labelTitle	= $title ? '<big><u>'.$title.'</u></big><br/>' : '';
			$labelDesc	= $desc ? UI_HTML_Tag::create( 'p', $desc ) : '';
			$list[]	=	 $labelPath.$labelTitle.$labelDesc.$this->renderServiceList( $path );
		}
		$list	= join( $list );
		$ratio	= round( $this->time / $this->counter / 1000, 1 );
		$footer	= sprintf(
			'<hr/><small>%s ms | %s Requests | %s ms | %s ms per Request | <em>This demo has been made by <a href="http://ceusmedia.de/">Ceus Media</a> 2010</a>. Please donate if you want to support this open source product.</em></small>',
			$clock->stop( 3, 0 ),
			$this->counter,
			round( $this->time / 1000 ),
			$ratio
		);

		$body	= '
		<div class="container">
			<h1 class="muted">CeusMedia Components Demo</h1>
			<h2><a href="../">CeusMedia/NetServices</a>: Disclosing Server Demo</h2>
			'.$list.'
			'.$footer.'
			'.$buffer->get( TRUE ).'
		</div>';

		$page	= new UI_HTML_PageFrame();
		$page->setTitle( 'Disclosing Server Demo | NetServices | Component Demo | Ceus Media' );
		$page->addBody( $body );
		$page->addStylesheet( 'https://cdn.ceusmedia.de/css/bootstrap.min.css' );
		$page->addStylesheet( 'https://cdn.ceusmedia.de/css/bootstrap-responsive.min.css' );
		$page->addStylesheet( 'style.css' );
		$page->addStylesheet( '../demo.css' );
		$page->addJavaScript( 'https://cdn.ceusmedia.de/js/jquery/1.10.2.min.js' );
		$page->addJavaScript( 'https://cdn.ceusmedia.de/js/bootstrap.min.js' );
		$page->addJavaScript( 'script.js' );
		return $page->build();
		return $page->build( array( 'style' => 'margin: 1.5em' ) );
	}

	protected function renderServiceFormatList( $path, $service )
	{
		$list		= array();
			$data	= array( 'path' => $path, 'service' => $service );
		$formats	= $this->getIndexData( 'getServiceFormats', $data );
		if( !$formats )
			return '';
		$formats	= UI_HTML_Tag::create( 'span', join( ', ', $formats ) );
		return 'Formats: '.$formats.'<br/><br/>';
	}

	protected function renderServiceFilterList( $path, $service )
	{
		$list		= array();
			$data	= array( 'path' => $path, 'service' => $service );
		$filters	= $this->getIndexData( 'getServiceFilters', $data );
		if( !$filters )
			return '';
		$list	= array();
		foreach( $filters as $filter => $title )
		{
			if( $title )
				$filter	= UI_HTML_Elements::Acronym( $filter, $title );
			$list[]	= $filter;
		}
		$filters	= UI_HTML_Tag::create( 'span', join( ', ', $list ) );
		return 'Filters: '.$filters.'<br/>';
	}

	protected function renderServiceList( $path )
	{
		$list		= array();
		$data		= array( 'path' => $path );
		$services	= $this->getIndexData( 'getServicesFromPath', $data );
		foreach( $services as $service )
		{
			$data		= array( 'path' => $path, 'service' => $service );
			$formats	= $this->renderServiceFormatList( $path, $service );
			$filters	= $this->renderServiceFilterList( $path, $service );
			$roles		= $this->getIndexData( 'getServiceRoles', $data );
			$roles		= $roles ? 'Roles: '.UI_HTML_Tag::create( 'span', join( ', ', $roles ) ) : '';
			$desc		= $this->getIndexData( 'getServiceDescription', $data );
			$desc		= $desc ? UI_HTML_Tag::create( 'p', $desc ) : '';
			$parameters	= $this->renderServiceParameterList( $path, $service );

			$url	= './' . ( $path == '/' ? $service : $path.$service );
			$link	= UI_HTML_Elements::Link( $url, $service );
			$label	= UI_HTML_Tag::create( 'h3', $link );
			$list[]	= $label.$desc.$roles.$formats.$filters.$parameters;
		}
		return $list ? implode( $list ) : '';
	}

	protected function renderServiceParameterList( $path, $service )
	{
		$list		= array();
		$data		= array( 'path' => $path, 'service' => $service );
		$parameters	= $this->getIndexData( 'getServiceParameters', $data );
		foreach( $parameters as $parameter => $rules )
		{
			$title	= empty( $rules['title'] ) ? '' : UI_HTML_Tag::create( 'p', $rules['title'] );
			$label	= UI_HTML_Tag::create( 'h4', 'Parameter: '.$parameter );
			$rules	= $this->renderServiceParameterRuleList( $rules );
			$list[]	= $label.$title.$rules;
		}
		return $list ? implode( $list ) : '';
	}

	protected function renderServiceParameterRuleList( $rules )
	{
		$list	= array();
		foreach( $rules as $ruleKey => $ruleValue )
		{
			switch( $ruleKey )
			{
				case 'title':
					$ruleValue = NULL;
					break;
				case 'mandatory':
					$mandatory	= $ruleValue;
					$ruleValue	= $ruleValue ? "yes" : "no";
					break;
				case 'filters':
					$ruleValue	= implode( ", ", $ruleValue );
					break;
				case 'type':
					$type	= UI_HTML_Tag::create( 'em', $ruleValue );
					$type	= UI_HTML_Tag::create( 'small', $type );
					$type	.= "&nbsp;";
					break;
			}
			if( !$ruleValue )
				continue;
			$ruleValue	= htmlspecialchars( $ruleValue );
			$key		= UI_HTML_Tag::create( "dt", $ruleKey, array( 'class' => "key" ) );
			$value		= UI_HTML_Tag::create( "dd", $ruleValue, array( 'class' => "value" ) );
			$list[]		= $key.$value;
		}
		$list	= $list ? UI_HTML_Tag::create( 'dl', implode( $list ), array( 'class' => 'rules' ) ) : '';
		return $list;
	}
}
?>
