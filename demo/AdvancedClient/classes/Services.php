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
 *	@uses			Alg_InputFilter
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@version		0.3
 */
class Services extends \CeusMedia\NetServices\Response
{
	/**
	 *	Adds two Integers and returns Result.
	 *	@access		public
	 *	@param		string		$format			Response Format
	 *	@param		int			$a				First Integer
	 *	@param		int			$b				Second Integer
	 *	@return		string
	 */
	public function sayHello( $format, $name = NULL )
	{
		$result	= $name ? "Hello $name!" : "Hello!";
		return $this->convertToOutputFormat( $result, $format );
	}
}
?>
