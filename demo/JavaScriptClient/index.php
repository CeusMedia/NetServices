<?php
(@include '../../vendor/autoload.php') or die('Please use composer to install required packages.' . PHP_EOL);

$body	= '
<div class="container">
	<h1 class="muted">CeusMedia Components Demo</h1>
	<h2><a href="../">CeusMedia/NetServices</a>: JavaScript Demo</h2>
	<h3>Services</h3>
	<div id="services"></div>
	<form id="form" onsubmit="jQuery(\'form-submit\').trigger(\'click\'); return false;">
		<h3>Parameters</h3>
		<div id="parameters"></div>
		<button type="submit" class="btn" id="form-submit">request</button>
	</form>
	<hr/>

	<h4>Request Result</h4>
	<pre id="result">No response, yet.</pre>

	<div>
		Time: <span id="time">-</span> ms
	</div>
	<h4>Parameter Definitions</h4>
	<pre id="def"></pre>
</div>
';

$page	= new UI_HTML_PageFrame();
$page->addBody( $body );
$page->addStylesheet( 'https://cdn.ceusmedia.de/css/bootstrap.min.css' );
$page->addStylesheet( 'https://cdn.ceusmedia.de/css/bootstrap-responsive.min.css' );
//$page->addStylesheet( 'https://cdn.ceusmedia.de/css/cmForm/cmForm.css' );
$page->addStylesheet( 'style.css' );
$page->addJavaScript( 'https://cdn.ceusmedia.de/js/jquery/1.10.2.min.js' );
$page->addJavaScript( 'https://cdn.ceusmedia.de/js/ENSClient.js' );
$page->addJavaScript( 'https://cdn.ceusmedia.de/js/ENSCache.js' );
$page->addJavaScript( 'https://cdn.ceusmedia.de/js/Storage.js' );
$page->addJavaScript( 'https://cdn.ceusmedia.de/js/String.deparam.js' );
$page->addJavaScript( 'https://cdn.ceusmedia.de/js/String.repeat.js' );
$page->addJavaScript( 'https://cdn.ceusmedia.de/js/String.formatJSON.js' );
$page->addJavaScript( 'UI.ENSClient.js' );
$page->addJavaScript( 'script.js' );
print( $page->build() );
//print( $page->build( array( 'style' => 'margin: 1em' ) ) );
?>
