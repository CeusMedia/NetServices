<?php

class HTML_Tabs{
	
	protected $tabs		= array();

	public function addTab( $label, $content, $fragmentId = NULL, $class = NULL ){
		$this->tabs[]	= array(
			'label'			=> $label,
			'content'		=> $content,
			'fragmentId'	=> $fragmentId,
			'class'			=> $class
		);
	}

	public function render( $id ){
		$number		= 0;
		$list		= array();
		$options	= array();
		foreach( $this->tabs as $tab ){
			$fragmentId	= $tab['fragmentId'];
			if( empty( $fragmentId ) )
				$fragmentId	= $id.'-fragment-'.( ++$number );
			$label		= UI_HTML_Tag::create( 'span', $tab['label'] );
			$link		= UI_HTML_Elements::Link( '#'.$fragmentId, $label, $tab['class'] );
			$tabs[]		= UI_HTML_Elements::ListItem( $link );

			$attributes	= array( 'id' => $fragmentId );
			$divs[]		= UI_HTML_Tag::create( 'div', $tab['content'], $attributes );
		}
		$tabs	= UI_HTML_Elements::unorderedList( $tabs );
		$divs	= implode( $divs );
		$script	= UI_HTML_JQuery::buildPluginCall( 'tabs', '#'.$id, $options );
		$script = UI_HTML_Tag::create( 'script', $script );
		$html	= $tabs.$divs.$script;
		return UI_HTML_Tag::create( 'div', $html, array( 'id' => $id  ) );
	}
}
?>
