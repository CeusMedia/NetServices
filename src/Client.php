<?php
/**
 *	Client for interaction with Frontend Services.
 *
 *	Copyright (c) 2007-2015 Christian Würker (ceusmedia.de)
 *
 *	This program is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation, either version 3 of the License, or
 *	(at your option) any later version.
 *
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU General Public License for more details.
 *
 *	You should have received a copy of the GNU General Public License
 *	along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *	@category		Library
 *	@package		CeusMedia_NetServices
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2015 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/NetServices
 */
namespace CeusMedia\NetServices;
/**
 *	Client for interaction with Frontend Services.
 *	@category		Library
 *	@package		CeusMedia_NetServices
 *	@uses			Net_CURL
 *	@uses			Alg_Time_Clock
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2015 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/NetServices
 */
class Client{

	/**	@var		string		$id					ID of Service Request Client */
	protected $id;
	/**	@var		bool		$logFile			File Name of Request Log File */
	protected $logFile			= NULL;
	/**	@var		string		$host				Basic URL of Services Host */
	protected $host;
	/**	@var		string		$username			Username for Basic Authentication */
	protected $username			= "";
	/**	@var		string		$password			Password for Basic Authentication */
	protected $password			= "";
	/**	@var		string		$userAgent			User Agent to sent to Service Point */
	protected $userAgent		= "NetServiceClient/0.7";
	/**	@var		bool		$verifyHost			Flag: verify Host */
	protected $verifyHost 		= FALSE;
	/**	@var		bool		$verifyPeer			Flag: verify Peer */
	protected $verifyPeer		= FALSE;
	/**	@var		array		$requests			Collected Request Information */
	protected $requests			= array();
	/**	@var		array		$statistics			Collected Statistic Information */
	protected $statistics		= array(
		'requests'	=> 0,
		'traffic'	=> 0,
		'time'		=> 0,
	);
	/**	@var		CMM_ENS_Decoder	$decoder	Response Decoder Object */
	protected $decoder;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		string		$hostUrl			Basic Host URL of Service
	 *	@param		bool		$logFile			File Name of Request Log File
	 *	@param		string		$decoderClass		Name of Class with Methods to decompress and decode Response
	 *	@return		void
	 */
	public function __construct( $hostUrl = NULL, $logFile = NULL, $decoderClass = "\CeusMedia\NetServices\Decoder" ){
		$this->id	= md5( uniqid( rand(), true ) );
		if( $hostUrl )
			$this->setHostAddress( $hostUrl );
		if( $logFile )
			$this->setLogFile( $logFile );
		$this->decoder	= \Alg_Object_Factory::createObject( $decoderClass );
	}

	/**
	 *	Executes Request, logs statistical Information and returns Response.
	 *	@access		protected
	 *	@param		Net_CURL	$request			Request Object
	 *	@param		bool		$compression		Type of Compression of Content (deflate,gzip)
	 *	@return		string
	 */
	protected function executeRequest( $request, $compression = NULL ){
		$request->setOption( CURLOPT_SSL_VERIFYPEER, $this->verifyPeer );
		$request->setOption( CURLOPT_SSL_VERIFYHOST, $this->verifyHost );
		if( $this->userAgent )
			$request->setOption( CURLOPT_USERAGENT, $this->userAgent );
		if( $this->username )
			$request->setOption( CURLOPT_USERPWD, $this->username.":".$this->password );
		$response['content']	= $request->exec();
		$response['info']		= $request->getInfo();
		$response['headers']	= $request->getHeader();

		$code		= $request->getInfo( \Net_CURL::INFO_HTTP_CODE );								//  get HTTP return status code
		if( !in_array( $code, array( '200', '304' ) ) ){											//  request failed
			$url		= $request->getOption( CURLOPT_URL );										//  get service request URI
			$resource	= preg_replace( '/&clientRequestSessionId='.$this->id.'$/', '', $url );		//  trim client ID
			throw new \Exception_IO( 'Service call HTTP request failed', $code, $resource );			//  throw IO exception with HTTP code and URI resource
		}
		$headers	= array();																		//  since delivered headers are case sensitive
		foreach( $response['headers'] as $key => $values )											//  and can contain several values, we need to iterate them
			$headers[strtolower( $key )]	= array_pop( $values );									//  to lowercase the keyand grab only the last value entry

		if( array_key_exists( 'content-encoding', $headers ) ){										//  compression header is set
			$content	= $response['content'];														//  get compressed content
			$method		= $headers['content-encoding'];												//  get used compression method
			$response['content']	= $this->decoder->decompressResponse( $content, $method );		//  decompress content
		}
		return $response;																			//  return content
	}

	/**
	 *	Requests Information from Service.
	 *	@access		public
	 *	@param		string		$service			Name of Service
	 *	@param		string		$format				Response Format
	 *	@param		array		$parameters			Array of URL Parameters
	 *	@param		bool		$verbose			Flag: show Request URL and Response
	 *	@return		mixed
	 */
	public function get( $service, $format = NULL, $parameters = array(), $verbose = FALSE ){
		$baseUrl	= $this->host.$service."?format=".$format;
		$compress	= isset( $parameters['compressResponse'] ) ? strtolower( $parameters['compressResponse'] ) : "";

		$parameters['clientRequestSessionId']	= $this->id;
		$parameters	= "&".http_build_query( $parameters, '', '&' );
		$serviceUrl	= $baseUrl.$parameters;
		if( $verbose )
			remark( "GET: ".$serviceUrl );

		$clock		= new \Alg_Time_Clock;
		$request	= new \Net_CURL( $serviceUrl );
		$response	= $this->executeRequest( $request, $compress );
		if( $this->logFile ){
			$message	= time()." ".strlen( $response['content'] )." ".$clock->stop( 6, 0 )." ".$service."\n";
			error_log( $message, 3, $this->logFile );
		}

		$this->requests[]	= array(
			'method'	=> "GET",
			'url'		=> preg_replace( '/&clientRequestSessionId='.$this->id.'$/', '', $serviceUrl ),
			'headers'	=> $response['headers'],
			'info'		=> $response['info'],
			'response'	=> $response['content'],
			'time'		=> $clock->stop(),
		);
		$response['content']	= $this->decoder->decodeResponse( $response['content'], $format, $verbose );
		return $response['content'];
	}

	/**
	 *	Returns ID of Service Request Client.
	 *	@access		public
	 *	@return		string
	 */
	public function getId(){
		return $this->id;
	}

	/**
	 *	Returns noted Requests.
	 *	@access		public
	 *	@param		boolean		$latestOnly		Flag: return latest quest only
	 *	@return		array
	 */
	public function getRequests( $latestOnly = FALSE ){
		if( $latestOnly ){
			$number	= count( $this->requests );
			if( !$number )
				throw new \RuntimeException( 'No services requested yet' );
			return $this->requests[$number - 1];
		}
		return $this->requests;
	}

	/**
	 *	Send Information to Service.
	 *	@access		public
	 *	@param		string		$service			Name of Service
	 *	@param		string		$format				Response Format
	 *	@param		array		$data				Array of Information to post
	 *	@param		bool		$verbose			Flag: show Request URL and Response
	 *	@return		mixed
	 */
	public function post( $service, $format = NULL, $data = array(), $verbose = FALSE ){
		$baseUrl	= $this->host.$service."?format=".$format;
		if( $verbose )
			remark( "POST: ".$baseUrl );

		//  cURL POST Hack (cURL identifies leading @ in Values as File Upload  //
		foreach( $data as $key => $value )
			if( is_string( $value ) && substr( $value, 0, 1 ) == "@" )
				$data[$key]	= "\\".$value;
			else if( is_array( $value ) )
				$data[$key]	= serialize( $value );

		$data['clientRequestSessionId']	= $this->id;							//  adding Client Request Session ID

		$clock		= new \Alg_Time_Clock;
		$request	= new \Net_CURL( $baseUrl );
		$request->setOption( CURLOPT_POST, TRUE );
		$request->setOption( CURLOPT_POSTFIELDS, $data );
		$response	= $this->executeRequest( $request );
		if( $this->logFile ){
			$message	= time()." ".strlen( $response['content'] )." ".$clock->stop( 6, 0 )." ".$service."\n";
			error_log( $message, 3, $this->logFile );
		}
		$this->requests[]	= array(
			'method'	=> "POST",
			'url'		=> $baseUrl,
			'data'		=> serialize( $data ),
			'headers'	=> $response['headers'],
			'info'		=> $response['info'],
			'response'	=> $response['content'],
			'time'		=> $clock->stop(),
			);
		if( $verbose )
			xmp( $response['content'] );
		$response['content']	= $this->decoder->decodeResponse( $response['content'], $format, $verbose );
		return $response['content'];
	}

	/**
	 *	Sets HTTP Basic Authentication.
	 *	@access		public
	 *	@param		string		$username			Username for HTTP Basic Authentication.
	 *	@param		string		$password			Password for HTTP Basic Authentication.
	 *	@return		void
	 */
	public function setBasicAuth( $username, $password ){
		$this->username	= $username;
		$this->password	= $password;
	}

	/**
	 *	Sets Basic Host URL of Service.
	 *	@access		public
	 *	@param		string		$hostUrl			Basic Host URL of Service
	 *	@return		void
	 */
	public function setHostAddress( $hostUrl ){
		$this->host	= $hostUrl;
	}

	/**
	 *	Sets File Name of Request Log File.
	 *	@access		public
	 *	@param		string		$fileName			File Name of Request Log File
	 *	@return		void
	 */
	public function setLogFile( $fileName ){
		$this->logFile	= $fileName;
	}

	/**
	 *	Sets Option CURLOPT_USERAGENT.
	 *	@access		public
	 *	@param		int			$userAgent			User Agent to set
	 *	@return		void
	 */
	public function setUserAgent( $userAgent ){
		$this->userAgent	= $userAgent;
	}

	/**
	 *	Sets Option CURLOPT_SSL_VERIFYHOST.
	 *	@access		public
	 *	@param		bool		$verify				Flag: verify Host
	 *	@return		void
	 */
	public function setVerifyHost( $verify ){
		$this->verifyHost	= (bool) $verify;
	}

	/**
	 *	Sets Option CURLOPT_SSL_VERIFYPEER.
	 *	@access		public
	 *	@param		bool		$verify				Flag: verify Peer
	 *	@return		void
	 */
	public function setVerifyPeer( $verify ){
		$this->verifyPeer	= (bool) $verify;
	}
}
?>
