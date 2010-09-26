<?php
/**
 *	Basic Server for Net Services.
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
 *	@package		ENS
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2010 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmmodules/
 *	@since			0.6.5
 *	@version		$Id: BaseServer.php5 667 2010-05-18 15:16:09Z christian.wuerker $
 */
/**
 *	Basic Server for Net Services.
 *
 *	@category		cmModules
 *	@package		ENS
 *	@extends		UI_HTML_Service_Index
 *	@uses			Net_HTTP_Request_Receiver
 *	@uses			CMM_ENS_Point
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2010 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmmodules/
 *	@since			0.6.5
 *	@version		$Id: BaseServer.php5 667 2010-05-18 15:16:09Z christian.wuerker $
 */
class CMM_ENS_BaseServer extends UI_HTML_Service_Index
{
	/**
	 *	Constructor, sets up new Service Point.
	 *	@access		public
	 *	@param		string			$fileName			Service Definition File Name
	 *	@param		array			$allowedFormats		Allowed Response Formats
	 *	@return		void
	 */
	public function __construct( $fileName = "services.xml", $allowedFormats = array() )
	{
		$requestHandler	= new Net_HTTP_Request_Receiver();
		$formatList		= array(
			'wddx',
			'php',
			'json',
			'xml',
			'rss',
			'atom',
			'txt'
		);
		if( is_array( $allowedFormats ) && count( $allowedFormats ) )
			$formatList	= $allowedFormats;

		try
		{
			$servicePoint	= new CMM_ENS_Point( $fileName );
			parent::__construct( $servicePoint, $formatList );
			if( $requestHandler->has( 'index' ) )
				$this->showServiceIndex( $servicePoint, $allowedFormats );
			$responseLength	= $this->handle( $requestHandler->getAll(), TRUE );
			$this->logRequest( $responseLength );
		}
		catch( Exception $e )
		{
			die( $e->getMessage() );
		}
	}
	
	/**
	 *	Tries to load Service Class for .
	 *	@access		protected
	 *	@param		string			$fileName			Service Definition File Name
	 *	@param		array			$allowedFormats		Allowed Response Formats
	 *	@return		void
	 *	@deprecated	seems to be unused
	 */
	protected function loadServiceClass( $className )
	{
		$class	= $this->services['services'][$serviceName]['class'];
		if( !class_exists( $class ) )
		{
			$parts	= explode( "_", $class );
			$last	= array_pop( $parts );
			$parts	= array_map( create_function( '$item', 'return strtolower( $item );' ), $parts );
			$path	= implode( ".",$parts  );
			$file	= $pathPrefix.$path.".".$last;
			import( $file );
		}
	}

	/**
	 *	Records Response Time and Length in Log File.
	 *	@access		protected
	 *	@param		int				$length				Length of Response
	 *	@return		void
	 */
	protected function logRequest( $length )
	{
		error_log( time().":".$length."\n", 3, "response.log" );
	}

	/**
	 *	Builds *very* simple Service List and exits.
	 *	Overwrite this Method to customize.
	 *	@access		protected
	 *	@param		CMM_ENS_Point	$servicePoint		Service Point Instance
	 *	@param		array				$allowedFormats		List of allowed Response Formats
	 *	@return		string
	 */
	protected function showServiceIndex( $servicePoint, $allowedFormats )
	{
		die( $this->getServiceList() );
	}
}
?>