<?php
class Server
{
	public function  __construct()
	{
		$request		= new Net_HTTP_Request_Receiver();
		$path			= $request->get( '___NetServicePath' );
		$parts			= explode( '/', trim( $path ) );
		$serviceName	= array_pop( $parts );
		$servicePath	= $parts ? implode( '/', $parts ).'/' : '';
		$serviceFile	= 'config/services' . ( $parts ? '.'.implode( '.', $parts ) : '' );
		$fileDefinition	= $serviceFile.'.xml';
		$fileCache		= $serviceFile.'.cache';
		$request->remove( '___NetServicePath' );

		if( file_exists( $fileDefinition ) )
		{
			$servicePoint	= new \CeusMedia\NetServices\Point( $fileDefinition, $fileCache );
			$serviceHandler	= new \CeusMedia\NetServices\Handler( $servicePoint );
			if( $serviceName )
			{
				if( !$servicePoint->hasService( $serviceName ) )
					$this->respondError404();
				$serviceHandler->handle( $serviceName, $request );
			}
		}
		else if( !$servicePath)
		{
			$view	= new Disclosure;
			$view->display();
		}
		else
			$this->respondError404();
	}

	protected function respondError404( $message = NULL )
	{
		$response	= new Net_HTTP_Response;
		$response->setStatus( '404 Not Found' );
		$response->setBody( 'No service available for this URL.' );
		Net_HTTP_Response_Sender::sendResponse( $response );
		exit;
	}
}
?>
