<?php
/**
 *	Checks order of Services in a Service Definition File (YAML and XML).
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
 *	@package		CeusMedia_NetServices_Definition
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2015 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/NetServices
 */
namespace CeusMedia\NetServices\Definition;
/**
 *	Checks order of Services in a Service Definition File (YAML and XML).
 *	@category		Library
 *	@package		CeusMedia_NetServices_Definition
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2015 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/NetServices
 */
class NameSortCheck{

	private $fileName		= "";
	private $originalList	= array();
	private $sortedList		= array();
	private $compared		= FALSE;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		string		$fileName		URL of Service Definition File
	 *	@return		void
	 */
	public function __construct( $fileName ){
		if( !file_exists( $fileName ) )
			throw new \RuntimeException( "File '".$fileName."' is not existing." );
		$this->fileName	= $fileName;
	}

	/**
	 *	Indicates whether all services are in correct order.
	 *	@access		public
	 *	@return		bool
	 */
	public function compare(){
		$this->originalList	= array();
		$this->compared		= TRUE;
		$content	= file_get_contents( $this->fileName );
		$info	= pathinfo( $this->fileName );
		switch( $info['extension'] ){
			case 'yaml':	$regEx	= "@^  ([a-z]+)[:]@i";
							break;
			case 'xml':		$regEx	= "@^\s*<service .*name=\"(\w+)\"@i";
							$content	= preg_replace( "@<!--.*-->@u", "", $content );
							break;
			default:		throw new \InvalidArgumentException( 'Extension "'.$info['extension'].'" is not supported.' );
		}


		$lines		= explode( "\n", $content );
		foreach( $lines as $line ){
			$matches	= array();
			preg_match_all( $regEx, $line, $matches, PREG_SET_ORDER );
			foreach( $matches as $match )
				$this->originalList[] = $match[1];
		}
		$this->sortedList	= $this->originalList;
		natCaseSort( $this->sortedList );
		return $this->sortedList === $this->originalList;
	}

	/**
	 *	Returns List of methods in original order.
	 *	@access		public
	 *	@return		array
	 */
	public function getOriginalList(){
		if( !$this->compared )
			throw new \RuntimeException( "Not compared yet." );
		return $this->originalList;
	}

	/**
	 *	Returns List of methods in correct order.
	 *	@access		public
	 *	@return		array
	 */
	public function getSortedList(){
		if( !$this->compared )
			throw new \RuntimeException( "Not compared yet." );
		return $this->sortedList;
	}
}
?>
