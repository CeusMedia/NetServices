<?php
/**
 *	Decompresses and decodes Service Response Strings in several Formats.
 *	Can be overwritten to extend with further Formats or Compression Methods.
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
 *	Decompresses and decodes Service Response Strings in several Formats.
 *	Can be overwritten to extend with further Formats or Compression Methods.
 *	@category		Library
 *	@package		CeusMedia_NetServices
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2015 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/NetServices
 *	@todo			fix: not reproducable errors using gzuncompress on format 'txt'
 */
class Decoder{

	/**	@var		array		$compressionTypes	List of supported Compression Types */
	protected $compressionTypes	= array(
		'deflate'	=> 'gzipUncompress',
		'gzip'		=> 'gzipDecode',
	);

	public $formats	= array(
		'json'	=> 'decodeJson',
		'php'	=> 'decodePhp',
		'wddx'	=> 'decodeWddx',
		'xml'	=> 'decodeXml',
	);

	/**
	 *	Decodes Responses in using Methods assigned to Format.
	 *	@access		public
	 *	@param		string		$response			Response Content as serialized String
	 *	@param		string		$format				Format of Serial (json|php|wddx|xml|rss|txt|...)
	 *	@return		mixed
	 */
	public function decodeResponse( $response, $format ){
		if( !array_key_exists( $format, $this->formats ) )						//  other Formats like Text or HTML
			return $response;													//  bypass Response Content undecoded

		ob_start();																//  open Buffer for PHP Error Messages
		$method		= $this->formats[$format];									//  get Name of Method to decode Response
		$structure	= $this->$method( $response );								//  call Method to decode Response
		$data		= $structure['data'];										//  Extract Response Data

		if( $structure['status'] == "exception" ){								//  Response contains an Exception
			if( !empty( $data['serial64'] ) )									//  does not carry a serialized Exception
				$data['serial']	= base64_decode( $data['serial64'] );			//
			if( empty( $data['serial'] ) )										//  does not carry a serialized Exception
				throw new \RuntimeException( $data['message'], $data['code'] );	//  forward Exception
			$object	= unserialize( $data['serial'] );							//  try to unserialize carried Exception
			if( $object instanceof \__PHP_Incomplete_Class ){
				$name	= $object->__PHP_Incomplete_Class_Name;
				throw new \RuntimeException( 'Class "'.$name.'" is not loaded.' );
			}
			else if( $object instanceof \Exception )							//  unserialized Object is an Exception
				throw $object;													//  throw responded Exception
		}

		$output	= ob_get_clean();												//  close Buffer for PHP Error Messages
		if( $structure === FALSE )												//  could not decode
			return $output;														//  return Error Message instead
		return $data;															//  return decoded Response Data
	}

	/**
	 *	Decodes Response String encoded in JSON.
	 *	@access		protected
	 *	@param		string		$response			Response Content as serialized String
	 *	@return		array
	 */
	protected function decodeJson( $response ){
		$structure	= json_decode( $response, TRUE );							//  try to decode JSON Response
		return $structure;
	}

	/**
	 *	Decodes Response String encoded as PHP Serial.
	 *	@access		protected
	 *	@param		string		$response			Response Content as serialized String
	 *	@return		array
	 */
	protected function decodePhp( $response ){
		$structure	= unserialize( $response );									//  try to decode PHP Response
		if( $structure && $structure instanceof \Exception )					//  Response is Exception
			throw $structure;													//  throw Response Exception
		return $structure;
	}

	/**
	 *	Decodes Response String encoded as WDDX Package.
	 *	@access		protected
	 *	@param		string		$response			Response Content as serialized String
	 *	@return		array
	 */
	protected function decodeRss( $response ){
		$content	= \XML_RSS_Parser::parse( $response );
		$structure	= array(
			'status'	=> "data",
			'data'		=> $content,
		);
		return $structure;
	}

	/**
	 *	Decodes Response String encoded as WDDX Package.
	 *	@access		protected
	 *	@param		string		$response			Response Content as serialized String
	 *	@return		array
	 */
	protected function decodeWddx( $response ){
		$structure	= wddx_deserialize( $response );							//  try to decode WDDX Response
		return $structure;
	}

	/**
	 *	Decodes Response String encoded in XML.
	 *	@access		protected
	 *	@param		string		$response			Response Content as serialized String
	 *	@return		array
	 */
	protected function decodeXml( $response ){
		$doc	= new \SimpleXmlElement( $response );
		if( strtolower( $doc->status ) == "exception" )
			throw new \RuntimeException( $doc->data->message );
		$structure	= array(
			'status'	=> $doc->status,
			'data'		=> $doc->data,
		);
		return $structure;
	}

	/**
	 *	Decompresses compressed Response Content.
	 *	@access		public
	 *	@param		string		$content			Response Content, compressed
	 *	@param		string		$type				Compression Type used for compressing Response
	 *	@param		int			$fallback			Flag: use first Method of Type not found
	 *	@return		string
	 */
	public function decompressResponse( $content, $type, $fallback = FALSE ){
		if( !array_key_exists( $type, $this->compressionTypes ) ){
			if( !$fallback )
				throw new \InvalidArgumentException( 'Decompression method "'.$type.'" is not supported' );
			$type	= array_shift( array_keys( $this->compressionTypes ) );
		}
		$method		= $this->compressionTypes[$type];												//  get name of method to decompress response content
		ob_start();																					//  open a output buffer
		$result		= $this->$method( $content );													//  call method to decompress response content
		$output		= ob_get_clean();																//  close buffer for PHP error messages
		if( $result === FALSE && $output )															//  could not decompress
			throw new \RuntimeException( $output )					;								//  throw exception and carry error message
		return $result;																				//  return decompressed response Content
	}

	/**
	 *	Decompresses gzipped String. Function is missing in some PHP Win Builds.
	 *	@access		protected
	 *	@param		string		$data				Data String to be decompressed
	 *	@return		string
	 *	@todo 		fix native decompression method
	 */
	protected function gzipDecode( $data ){
		if( 0 && function_exists( 'gzdecode' ) )															//  if PHP method has been released
			$data	= gzdecode( $data );															//  use it to decompress the data
		else{																						//  otherwise: own implementation
			$tmp	= tempnam( '/tmp', 'ENS' );														//  create temporary file
			@file_put_contents( $tmp, $data );														//  store gzipped data
			ob_start();																				//  open output buffer
			readgzfile( $tmp );																		//  read the gzip file to std output
			@unlink( $tmp );
			$data	= ob_get_clean();																//  get decompressed data from output buffer
		}
		if( FALSE === $data )																		//  gzencode could decompress
			throw new \RuntimeException( 'Data not decompressable with gzdecode' );					//  throw exception
		return $data;																				//  return decompressed data
	}

	/**
	 *	Decompresses deflated String.
	 *	@access		protected
	 *	@param		string		$data				Data String to be decompressed
	 *	@return		string
	 */
	protected function gzipUncompress( $content ){
		$data	= @gzuncompress( $content );
		if( FALSE == $data )
			throw new \RuntimeException( "Data not decompressable with gzuncompress." );
		return $data;
	}
}
?>
