<?php
function renderFacts( $facts ){
	$list	= array();
	foreach( $facts as $key => $values ){
		$list[]	= new UI_HTML_Tag( 'dt', $key );
		if( is_string( $values ) || is_int( $values ) || is_float( $values ) )
			$values	= array( $values );
		else if( !is_array( $values ) || !$values )
			$values	= array( json_encode( $values ) );
		foreach( $values as $value )
			$list[]	= new UI_HTML_Tag( 'dd', $value );
	}
	if( $list )
		return new UI_HTML_Tag( 'dl', $list, array( 'class' => 'dl-horizontal' ) );
}
?>
