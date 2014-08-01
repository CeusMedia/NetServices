<?php
/**
 *	Validator for Service Parameters.
 *
 *	Copyright (c) 2007-2010 Christian Würker (ceusmedia.de)
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
 *	@category		cmModules
 *	@package		ENS.Parameter
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2010 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmmodules/
 *	@since			0.6.3
 *	@version		$Id$
 */
/**
 *	Validator for Service Parameters.
 *	@category		cmModules
 *	@package		ENS.Parameter
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2010 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmmodules/
 *	@since			0.6.3
 *	@version		$Id$
 *	@todo			Code Doc
 */
class CMM_ENS_Parameter_Validator
{
	/**
	 *	Validates a Parameter Value from Request by calling Validator Methods for Parameter Rules and throwing Exceptions.
	 *	@access		public
	 *	@static
	 *	@param		array		$rules			Parameter Rules
	 *	@param		string		$value			Parameter Value from Request
	 *	@return		void
	 */
	public static function validateParameterValue( $rules, $value )
	{
		try
		{
			if( $value === NULL && empty( $rules['mandatory'] ) )
				return;
			foreach( $rules as $ruleName => $ruleValue )
			{
				if( $ruleName == "title" )
					continue;
				if( $ruleName == "filters" )
					continue;
				if( $ruleValue )
					self::callMethod( "check".ucFirst( $ruleName ), $value, $ruleValue );
			}
		}
		catch( InvalidArgumentException $e )
		{
			if( $ruleName == 'preg' )
				$ruleName	.= ': '.$ruleValue;
			throw new InvalidArgumentException( $ruleName );
		}
	}
	
	/**
	 *	Calls validator method and throws exception if validation failed.
	 *	@access		protected
	 *	@static
	 *	@param		string		$method			Validation method to call
	 *	@param		string		$value			Value to validate
	 *	@param		string		$measure		Measure to validate against
	 *	@return		void
	 */
	protected static function callMethod( $method, $value, $measure = NULL )
	{
		if( !method_exists( __CLASS__, $method ) )
		{
			$message	= 'Service parameter validator method "'.$method.'" is not existing';
			throw new BadMethodCallException( $message );
		}
		if( !self::$method( $value, $measure ) )
			throw new InvalidArgumentException( $method );
	}
	
	/**
	 *	...
	 *	@access		protected
	 *	@static
	 *	@param		string		$value			Value to validate
	 *	@return		bool
	 */
	protected static function checkMandatory( $value )
	{
		switch( gettype( $value ) )
		{
			case 'array':	
				return (bool) count( $value );
			default:
				return (bool) strlen( $value );
		}
	}

	/**
	 *	...
	 *	@access		protected
	 *	@static
	 *	@param		string		$value			Value to validate
	 *	@return		bool
	 */
	protected static function checkMinlength( $value, $measure )
	{
		if( strlen( $value ) >= $measure )
			return TRUE;
		return FALSE;
	}

	/**
	 *	...
	 *	@access		protected
	 *	@static
	 *	@param		string		$value			Value to validate
	 *	@return		bool
	 */
	protected static function checkMaxlength( $value, $measure )
	{
		if( strlen( $value ) <= $measure )
			return TRUE;
		return FALSE;
	}

	/**
	 *	...
	 *	@access		protected
	 *	@static
	 *	@param		string		$value			Value to validate
	 *	@return		bool
	 */
	protected static function checkPreg( $value, $measure )
	{
		return preg_match( $measure, $value );
	}

	/**
	 *	...
	 *	@access		protected
	 *	@static
	 *	@param		string		$value			Value to validate
	 *	@return		boolean
	 */
	protected static function checkType( $value, $measure )
	{	
		$type	= gettype( $value );
		if( $type == $measure )
			return TRUE;
		$copy	= $value;
		switch( $measure )
		{
			case 'bool':
			case 'boolean':
				return (bool) $copy == $value;
			case 'int':
			case 'integer':
				return (int) $copy == $value;
			case 'double':
			case 'float':
			case 'real':
				return (float) $copy == $value;
			case 'string':
				return (string) $copy == $value;
			case 'array':
				return (array) $copy == $value;
		}
		return FALSE;
	}
}
?>