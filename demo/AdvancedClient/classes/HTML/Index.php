<?php
/**
 *	Service Handler which indexes with HTML Output.
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
 *	@package		ESA.Client.HTML
 *	@extends		\CeusMedia\NetServices\Handler
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2010 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmmodules/
 *	@since			18.06.2007
 *	@version		$Id: Index.php5 679 2010-05-18 17:01:11Z christian.wuerker $
 */
/**
 *	Service Handler which indexes with HTML Output.
 *	@category		cmModules
 *	@package		ESA.Client.HTML
 *	@extends		\CeusMedia\NetServices\Handler
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2010 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmmodules/
 *	@since			18.06.2007
 *	@version		$Id: Index.php5 679 2010-05-18 17:01:11Z christian.wuerker $
 */
class HTML_Index
{
	protected $servicePoint;

	public function __construct( \CeusMedia\NetServices\Point $servicePoint )
	{
		$this->servicePoint		= $servicePoint;
	}

	/**
	 *	Shows Index Page of Service.
	 *	@access		public
	 *	@return		string		HTML of Service Index
	 */
	public function buildContent( $path = NULL )
	{
		$formats	= $this->servicePoint->getAllFormats();
		$table		= new HTML_Table( $this->servicePoint, $formats );

		//  --  TYPES FOR FILTER  --  //
		$optFormat	= array( '<option value=""> -- all -- </option>' );
		foreach( $formats as $format )
			$optFormat[$format]	= "<option>".$format."</option>";

		$data	= array(
			'title'			=> $this->servicePoint->getTitle(),									//  Services Title
			'description'	=> $this->servicePoint->getDescription(),							//  Services Description
			'table'			=> $table->buildContent( $path ),
			'optFormat'		=> implode( '', $optFormat )
		);
		return UI_Template::render( 'templates/index.html', $data );
	}
}
?>
