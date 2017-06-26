<?php
$list	= array();
foreach( $parameters as $parameter )
	$list[]	= UI_HTML_Elements::ListItem( $parameter['label'].$parameter['rules']."<br/>".$parameter['input'] );
$parameters	= UI_HTML_Elements::unorderedList( $list );

if( $filters )
{
	$list	= array();
	foreach( $filters as $filterMethod => $filterTitle )
		$list[]	= UI_HTML_Elements::Acronym( $filterMethod, $filterTitle );
	$filters	= implode( ", ", $list );
}
else
	$filters	= "<em>none</em>";

return '
<div id="test">
	<div id="header">
		<div class="container">
			<h1><a href="./'.$path.'" alt="back to Index" title="back to Index">'.$title.'</a> - '.$service.'</h1>
		</div>
	</div>
	<div id="content">
		<div class="container">

			<!--  INFO  -->
			<div id="info">
				<h3>Information</h3>
				<dl>
					<dt>Service Class</dt>
					<dd>'.$class.'</dd>
					<dt>Service Name</dt>
					<dd><acronym title="'.$description.'">'.$service.'</acronym></dd>
					<dt>Default Format</dt>
					<dd>'.$defaultFormat.'</dd>
					<dt>Request Format</dt>
					<dd>'.$format.'</dd>
					<dt>Service Request URL</dt>
					<dd>
						<a href="'.$requestUrl.'" title="'.basename( $requestUrl ).'">URL</a>
						<a href="'.$requestUrl.'" title="'.basename( $requestUrl ).'" target="_blank">^</a>
					</dd>
					<dt>Service Test URL</dt>
					<dd>
						<a href="'.$testUrl.'" title="'.basename( $testUrl ).'">URL</a>
						<a href="'.$testUrl.'" title="'.basename( $testUrl ).'" target="_blank">^</a>
					</dd>
					<dt>Response Time</dt>
					<dd><acronym title="'.$time.' &micro;s">'.round( $time / 1000, 1 ).' ms</acronym></dd>
					<dt>DOM Elements</dt>
					<dd id="domElements"></dd>
					<div style="clear: both"></div>
				</dl>
				<br/>
			</div>

			<!--  FORM  -->
			<!--      &laquo;&nbsp;<a href="./">back to Index</a>
			<h2>'.$service.'</h2>-->
			<br/>
			<em>'.$description.'</em><br/><br/>
			Filters applied to all parameters before validation: <b>'.$filters.'</b><br/><br/>
			<div id="control">
				<h3>Parameters</h3>
				<form action="./'.$path.$service.'?___test" method="POST">
					'.$parameters.'
					<button type="submit" name="call" class="btn btn-primary">request</button>
				</form>
				<br/>
			</div>

			<!--  RESPONSE  -->
			<div id="response">
				'.$tabs.'
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
$(document).ready(function(){
	$("dl.exception").cmExceptionView();
	$("#domElements").html(document.getElementsByTagName("*").length);
});
  </script>
</html>';
?>
