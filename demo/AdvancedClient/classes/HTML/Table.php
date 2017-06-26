<?php
/**
 *	...
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
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2010 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmmodules/
 *	@version		$Id: Table.php5 679 2010-05-18 17:01:11Z christian.wuerker $
 */
/**
 *	...
 *	@category		cmModules
 *	@package		ESA.Client.HTML
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2010 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmmodules/
 *	@version		$Id: Table.php5 679 2010-05-18 17:01:11Z christian.wuerker $
 *	@todo			Code Doc
 */
class HTML_Table
{
	protected $servicePoint;
	protected $formats;

	public function __construct( \CeusMedia\NetServices\Point $servicePoint )
	{
		$this->servicePoint		= $servicePoint;
		$this->formats			= $servicePoint->getAllFormats();
	}

	/**
	 *	Return HTML Table of Services with their available Formats.
	 *	@access		public
	 *	@return		string			HTML of Service Table
	 */
	public function buildContent( $path = NULL )
	{
		$rows		= array();
		$services	= $this->servicePoint->getServices();
		natcasesort( $services );
		$heads		= array();

		$heads	= array( "<th>Service</th><th>Parameter</th>" );
		$cols	= array( "<col width='35%'/><col width='30%'/>" );
		foreach( $this->formats as $format )
		{
			$width		= round( ( 100 - 65 ) / count( $this->formats ), 0 );
			$cols[]		= UI_HTML_Tag::create( 'col', NULL, array( 'width' => $width.'%' ) );
			$attributes	= array( 'class' => 'format-switch', 'onclick' => "$('#filter_format').val('".$format."').trigger('change');" );
			$span		= UI_HTML_Tag::create( 'span', strtoupper( $format ), $attributes );
			$heads[]	= UI_HTML_Tag::create( 'th', $span );
		}
		$cols		= UI_HTML_Tag::create( 'colgroup', implode( "", $cols ) );
		$heads		= UI_HTML_Tag::create( 'tr', join( $heads ) );
		$counter	= 0;
		foreach( $services as $service )
		{
			$counter ++;
			//  --  FORMATS  --   //
			$cells		= array();
			$formats	= $this->servicePoint->getServiceFormats( $service );
			$default	= $this->servicePoint->getDefaultServiceFormat( $service );
			foreach( $this->formats as $format )
			{
				if( $format == $default )
					$cells[]	= "<td class='preferred'><span class='".$format."'>+</span></td>";
				else if( in_array( $format, $formats ) )
					$cells[]	= "<td class='yes'><span class='".$format."'>+</span></td>";
				else
					$cells[]	= "<td class='no'>-</td>";
			}
			$parameterList	= $this->getParameterList( $service );
			$attributes		= array( 'href' => $path.$service.'?___test', 'title' => "Run this service" );
			$linkService	= UI_HTML_Tag::create( "a", $service, $attributes );
			$data	= array(
				'formats'		=> implode( " ", $formats ),
				'className'		=> $this->servicePoint->getServiceClass( $service ),
				'linkService'	=> $linkService,
				'description'	=> $this->servicePoint->getServiceDescription( $service ),
				'parameters'	=> implode( "<br/>", $parameterList ),
				'cellsFormat'	=> implode( '', $cells ),
			);
			$rows[]	= UI_Template::render( 'templates/table.row.html', $data );
			if( $counter % 10 == 0 )
				$rows[]	= $heads;
		}
		$data	= array(
			'columns'	=> $cols,
			'heads'		=> $heads,
			'rows'		=> implode( "", $rows ),
		);
		return UI_Template::render( 'templates/table.html', $data );
	}

	protected function getParameterList( $service )
	{
		//  --  PARAMETERS  --   //
		$parameterList	= array();
		$parameters	= $this->servicePoint->getServiceParameters( $service );
		foreach( $parameters as $parameter => $rules )
		{
			$rules	= $this->renderRules( $parameter, $rules );
			$parameterList[]	= $rules.$parameter;
		}
		return $parameterList;
	}

	protected function renderRules( &$parameter, $rules )
	{
		$ruleList	= array();
		$mandatory	= FALSE;
		$type		= NULL;
		if( $rules )
		{
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
				$spanKey	= UI_HTML_Tag::create( "span", $ruleKey.":", array( 'class' => "key" ) );
				$spanValue	= UI_HTML_Tag::create( "span", $ruleValue, array( 'class' => "value" ) );
				$ruleList[]	= $spanKey.$spanValue;
			}
		}
		if( isset( $rules['title'] ) )
			$parameter	= UI_HTML_Elements::Acronym( $parameter, $rules['title'] );

		$parameter	= $type.$parameter;

		if( !$mandatory )
			$parameter	= '['.$parameter.']';

		if( !$ruleList )
			return '';
		$rules	= implode( ", ", $ruleList );
		$rules	= UI_HTML_Tag::create( 'div', $rules, array( 'class' => 'rules' ) );
		return $rules;
	}
}
?>
