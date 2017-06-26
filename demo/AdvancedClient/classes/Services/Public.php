<?php
/**
 *	Public Net Services for Demonstration.
 *	@package		demos.NetServices
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@version		0.3
 */
/**
 *	Public Net Services for Demonstration.
 *	@package		demos.NetServices
 *	@extends		\CeusMedia\NetServices\Response
 *	@uses			Alg_Text_Filter
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@version		0.3
 */
class Services_Public extends \CeusMedia\NetServices\Response
{
	/**
	 *	Adds two Integers and returns Result.
	 *	@access		public
	 *	@param		string		$format			Response Format
	 *	@param		int			$a				First Integer
	 *	@param		int			$b				Second Integer
	 *	@return		string
	 */
	public function addIntegers( $format, $a, $b )
	{
		$result	= (int) $a + (int) $b;
		if( $format == "txt" )
			return $result;
		return $this->convertToOutputFormat( $result, $format );
	}

	public function getArray( $format )
	{
		$array	= array(
			'assoc' => array(
				'time'	=> date( "H:i:s" ),
				'date'	=> date( "j.m.Y" ),
			),
			array(
				1,
				2,
				3,
				'string',
				NULL,
				TRUE,
				array(
					'test'	=> "val",
				),
			)
		);
		return $this->convertToOutputFormat( $array, $format );
	}

	/**
	 *	Returns List of loaded Classes.
	 *	@access		public
	 *	@return		string
	 */
	public function getClassList( $format )
	{
		$list	= $GLOBALS['imported'];
		return $this->convertToOutputFormat( $list, $format );
	}

	public function getLargeRandomString( $format, $megaBytes )
	{
		$string	= "";
		$bytes	= abs( $megaBytes ) * 1024 * 1024;
		$steps	= $bytes / 32 ;
		for( $i=0; $i<$steps; $i++ )
			$string .= md5( uniqid() );
		$string	= substr( $string, 0, $bytes );
		return $this->convertToOutputFormat( $string, $format );
	}

	/**
	 *	Creates a Exception and throws it or returns it encoded.
	 *	@access		public
	 *	@param		string		$format			Response Format
	 *	@param		bool		$throw			Flag: throw Exception, otherwise return Exception
	 *	@return		string
	 */
	public function getTestException( $format, $throw = FALSE )
	{
		$method	= $throw ? "thrown" : "responded";
		$e	= new Exception( "Test Exception (".$method.")" );
		if( $throw )
			throw $e;
		return $this->convertToOutputFormat( $e, $format );
	}

	/**
	 *	Returns current Timestamp on Server.
	 *	@access		public
	 *	@param		string		$format			Response Format
	 *	@param		string		$output			Output Format, see http://php.net/date
	 *	@return		string
	 */
	public function getTimestamp( $format, $output )
	{
		$time	= $output ? date( $output, time() ) : time();
		if( $format == "txt" )
			return $time;
		return $this->convertToOutputFormat( $time, $format );
	}

	/**
	 *	Returns the given String back to the client, filtered by Script Tags.
	 *	@access		public
	 *	@param		string		$format			Response Format
	 *	@param		string		$input			Input String to reflect
	 *	@return		string
	 */
	public function reflectInput( $format, $input )
	{
		$input	= Alg_Text_Filter::stripScripts( $input );
#		$input	= Alg_Text_Filter::stripTags( $input );
		return $this->convertToOutputFormat( $input, $format );
	}

	/**
	 *	Applies rot13 filter to a string.
	 *	The string will be filtered and all HTML contents will be removed.
	 *	@access		public
	 *	@param		string		$format			Response format
	 *	@param		string		$string			String to apply rot13 to
	 *	@return		string
	 */
	public function rot13( $format, $string ){
		$string	= Alg_Text_Filter::stripScripts( $string );
		$string	= Alg_Text_Filter::stripTags( $string );
		if( !is_string( $string ) )
			throw new InvalidArgumentException( 'Parameter "string" must be of string' );
		return $this->convertToOutputFormat( str_rot13( $string ), $format );
	}
}
?>
