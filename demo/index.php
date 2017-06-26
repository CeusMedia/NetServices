<?php
(@include '../vendor/autoload.php') or die('Please use composer to install required packages.' . PHP_EOL);

$body	= '
<div class="container">
	<h1 class="muted">CeusMedia Components Demo</h1>
	<h2>CeusMedia/NetServices</h2>
	<p>
		Welcome to the demonstrations of <acronym title="Ceus Media Library for Network Services">CeusMedia/NetServices</acronym>!
	</p>
	<h3>About</h3>
	<p>
		CeusMedia/NetServices is a RESTful HTTP server and client to provide and call services via network.
	</p>
	<div class="row-fluid">
		<div class="span5">
			<h4>NetServices is...</h4>
			<ul>
				<li>an open source code library</li>
				<li>a framework for network services</li>
				<li>running servers for callable HTTP services <span class="muted">(Service Point)</span></li>
				<li>providing clients for callable HTTP services <span class="muted">(Service Client)</span></li>
				<li>written in PHP <span class="muted">(Server and Client)</span> and JavaScript <span class="muted">(Client)</span></li>
				<li>originated in 2005, maintained until today</li>
				<li>allowing server to be self-explanatory <span class="muted">(API AutoIndex)<span></li>
			</ul>
		</div>
		<div class="span4">
			<h4>Features</h4>
			<ul>
				<li>Server
					<ul>
						<li>RESTful HTTP dispatcher</li>
						<li>calling custom services</li>
						<li>defined by API definition</li>
						<li>noted in one of several formats</li>
					</ul>
				</li>
				<li>Client
					<ul>
						<li>PHP client using HTTP via cURL</li>
						<li>JavaScript client using AJAX</li>
					</ul>
				</li>
			</ul>
		</div>
		<div class="span3">
			<h4>Response formats</h4>
			<ul>
				<li>PHP Serial</li>
				<li><a href="http://en.wikipedia.org/wiki/XML">XML</a></li>
				<li><a href="http://en.wikipedia.org/wiki/JSON">JSON</a></li>
				<li><a href="http://en.wikipedia.org/wiki/WDDX">WDDX</a></li>
			</ul>
			<p>
				Further formats can be added easily.<br/>
				Please feel free to make a <a href="mailto:office@ceusmedia.de?subject=NetServices Format Suggestion">suggestion</a>!<br/>
			</p>
		</div>
	</div>
	<h3>Demos</h3>
	<div class="row-fluid">
		<div class="span5">
			<h4>Client Demos</h4>
			<a href="./SimplestClient/" class="btn btn-block">
				<br/>
				<big><big>Simplest Client</big></big><br/>
				<span>Simplest demonstration of NetServices Client in one page.</span><br/>
				<br/>
			</a>
			<br/>
			<a href="./InformativeClient/" class="btn btn-block">
				<br/>
				<big><big>Informative Client</big></big><br/>
				<span>More informative demonstration of NetServices Client.</span><br/>
				<br/>
			</a>
			<hr/>
			<a href="./SimplestClientDemo/" class="btn btn-block">
				<br/>
				<big><big>Simplest Client</big></big><br/>
				<span>Simplest demonstration of NetServices Client in one page.</span><br/>
				<br/>
			</a>
		</div>
	</div>
<!--
	<div class="row-fluid">
		<h4>Server Demos</h4>
		<dl>
			<dl>
				<dt>
					<a href="./SimpleClient/">Simple Client Demo</a>
				</dt>
				<dd>
					Simple demonstration of ENS ServiceClient with code example.
				</dd>
				<dt>
					<a href="./AdvancedClient/">AdvancedClient</a>
				</dt>
				<dd>
					ENS ServicePoint with advanced verbose web client with informative indexing and possibility to test services.
				</dd>
				<dt>
					<a href="./JavaScriptClient/">JavaScriptClient</a>
				</dt>
				<dd>
					Simple demonstration of JavaScript ENS Client.
				</dd>
			</dl>
		</div>
		<div class="span5 offset1">
			<h4>Server Demos</h4>
			<dl>
				<dt>
					<a href="./TimeServer/">Time Server Demo</a>
				</dt>
				<dd>
					Simple NetServices service point for time information.
				</dd>
				<dt>
					<a href="./DisclosingServer/">Disclosing Server Demo</a>
				</dt>
				<dd>
					ENS ServicePoint with web client without an advanced user interface. All services known from AdvancedClient are available.
				</dd>
			</dl>
		</div>-->;

	<h3>License</h3>
	<p>
		This software is a product of <a href="https://ceusmedia.de/">Ceus Media</a>.<br/>
		It is open source software and published unter the <acronym title="GNU Public Licence">GPL 3</acronym>.
		This means, you are free to use or copy this software as long all copyright and license information are included.<br/>
	</p>
</div>
';

$cssLibPath	= 'https://cdn.ceusmedia.de/css/';
$jsLibPath	= 'https://cdn.ceusmedia.de/js/';

$page	= new UI_HTML_PageFrame();
$page->setTitle( 'ENS Demos' );
$page->addStylesheet( $cssLibPath.'bootstrap.min.css' );
$page->addStylesheet( $cssLibPath.'bootstrap-responsive.min.css' );
$page->addJavaScript( $jsLibPath.'jquery/1.10.2.min.js' );
$page->addJavaScript( $jsLibPath.'jquery/ui/1.8.4/min.js' );
$page->addStylesheet( $jsLibPath.'jquery/ui/1.8.4/css/smoothness.css' );
//$page->addHead( $head );
$page->addBody( $body );

$response	= new Net_HTTP_Response();
$response->setBody( $page->build() );
Net_HTTP_Response_Sender::sendResponse( $response );
?>
