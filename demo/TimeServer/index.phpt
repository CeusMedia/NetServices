<div class="container">
	<h1 class="muted">CeusMedia Components Demo</h1>
	<h2><a href="../">CeusMedia/NetServices</a>: Simple Time Server Demo</h2>
	<p>
		This is a simple RESTfull Net Service Point.<br/>
		It will serve the current time in several formats.
	</p>
	<h3>Demos</h3>
	See the following demo services by calling the URLs:
	<ul>
		<li>
			<h4><a href="./time">Get current timestamp</a> <small class="muted">as plain text</small></h4>
			<dl class="dl-horizontal">
				<dt>URL</dt>
				<dd>./time</dd>
				<dt>Effect</dt>
				<dd>Returns current UNIX timestamp. This is the main function of this demo service. But it can more as you will see in the next demo.</dd>
			</dl>
		</li>
		<li>
			<h4><a href="./time?format=xml">Get current timestamp as XML</a> <small class="muted">as XML</small></h4>
			<dl class="dl-horizontal">
				<dt>URL</dt>
				<dd>./time?format=xml</dd>
				<dt>Effect</dt>
				<dd>Returns current UNIX timestamp, this time as XML string.</dd>
				<dt>Format</dt>
				<dd>The response format is defined as XML. This service supports XML as response format and so the response is XML.<br/>You can use other formats, too (see below in definition).<br/>You can also extends the default service response class and add new formats.</dd>
			</dl>
		</li>
		<li>
			<h4><a href="./time?format=json&output=r">Get current datetime</a> <small class="muted">as JSON</small></h4>
			<dl class="dl-horizontal">
				<dt>URL</dt>
				<dd>./time?format=json&output=r</dd>
				<dt>Effect</dt>
				<dd>Returns current RFC 2822 formatted date,this time as JSON string.</dd>
				<dt>Parameter: output</dt>
				<dd>Output can be all formats available for PHP function <a href="http://www.php.net/manual/en/function.date.php">date</a>.<br/></dd>
			</dl>
		</li>
	</ul>
	<p>
		Services can have several parameters, which can be defined easy yet powerful within the service definition XML file, which comes below.<br/>
		But first have a look at the service class, which performs the requested demo service calls (handled by other classed from CeusMedia::NetServices).
	</p>
	<h3>Service class file</h3>
	The following code is an example class containing the demo services.<br/>
	<div class="code"><%codeClass%></div>
	It is just simple like that, because NetServices is handling all the other stuff like:
	<ul>
		<li>request decoding</li>
		<li>parameter filtering</li>
		<li>parameter validation</li>
		<li>reponse data encoding</li>
		<li>reponse wrapping and return</li>
	</ul>
	<p>
		You can extend this class or add more service classes like these.<br/>
		The definition file binds a specified services class to a service name where the service name is the method name in the service class, too.
	</p>

	<h3>Service definition XML file</h3>
	So, the central point of information is the service definition file, which is typically in XML but really simple and selfexplaining.<br/>
	Have a look for yourself before we get into details.
	<div class="code"><%codeXmlDef%></div>
	<h4>Formats</h4>
	As this demo definiton shows, there are several response formats available:
	<dl class="dl-horizontal">
		<dt>txt</dt>
		<dd>Plain text, no structure, just content. No suitable for services with complex response but for very simple ones.</dd>
		<dt>xml</dt>
		<dd><a href="http://en.wikipedia.org/wiki/XML">XML</a>, structured with primitive data types. Suitable for simple and complex responses.</dd>
		<dt>json</dt>
		<dd><a href="http://en.wikipedia.org/wiki/JSON">JSON</a>, structured with primitive data types. Suitable for simple and complex responses, especially for JavaScript clients.</dd>
		<dt>php</dt>
		<dd>Serial string produced by and readable for PHP. Very useful if the requesting client is written in PHP.</dd>
		<dt>wddx</dt>
		<dd><a href="http://en.wikipedia.org/wiki/WDDX">WDDX</a>, a structures interchange format suitable for clients in serveral programming languages with WDDX support.</dd>
	</dl>
	<h4>Parameters</h4>
	A service can have several parameters, defined by a name, which also is the method name in the service class.<br/>
	Parameters can habe rules which are tested against during input validation:
	<dl class="dl-horizontal">
		<dt>mandatory</dt>
		<dd>Indicates whether this parameter must be set an contain a value (0 is allowed). Possible rule values: 0, 1, yes, no, true, false.</dd>
		<dt>min-/maxlength</dt>
		<dd>Minimum and/or maximum length of parameter.</dd>
		<dt>preg</dt>
		<dd>Perl regular expression, with delimeters and modifiers</dd>
		<dt>type</dt>
		<dd>Primitive data type which is expected. Possible values are:
			<ul>
				<li>string</li>
				<li>integer, int</li>
				<li>boolean,bool</li>
				<li>double, float, real</li>
			</ul>
		</dd>
	</dl>
	Other parameter attributes are:
	<dl class="dl-horizontal">
		<dt>filters</dt>
		<dd>A list of filters which are applied before validation. Filters can be defined by extending the class <cite>\CeusMedia\NetServices\Parameter\Filter</cite>.</dd>
		<dt>title</dt>
		<dd>A one-line description of the service function.</dd>
	</dl>
	<p>
		It is recommended to write the service definitions first, followed by unit tests and afterwards the service class itself implementing the defined services.
	<h3>Main script</h3>
	To make the service point run, you need to glue things together now.<br/>
	This library delivers several classes which do the work in the end. But they are configurable and extendable.<br/>
	Although the main script cannot be a one-liner, it is still simple and adaptable in short time.<br/>
	<div class="code"><%codeMain%></div>
	<h4>Code parts</h4>
	<ul>
		<li>...</li>
	</ul>
	<p>
	</p>
	<h3>Rewriting: .htaccess</h3>
	To make the server work with these fine RESTful URIs you need to activate Apache's mod_rewrite and set configure rules like that:
	<div class="code"><%codeHtaccess%></div>
	This defines that all requests to not (in files) existing resources are rewritten to the service point.<br/>
	The path and query after the service points root path is forwared as service name and parameters.<br/>
	<br/>
	<b>Voila!</b>
	<br/>
	<br/>
	<hr/>
	<small><em>This demo has been made by <a href="http://ceusmedia.de/">Ceus Media</a> 2010</a>. Please donate if you want to support this open source product.</em></small>
	<br/>
</div>
