<?php
/**
 *	Access Point for Service Calls.
 *	Different classes for validation, filtering and definition loading can be set.
 *	They need to be loaded before or available using an autoloader.
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
 *	If a different Loader Class should be used, it needs to be imported before.
 *	@category		cmModules
 *	@package		ENS
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2010 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmmodules/
 *	@since			0.6.3
 *	@version		$Id: Point.php5 667 2010-05-18 15:16:09Z christian.wuerker $
 */
/**
 *	Access Point for Service Calls.
 *	Different classes for validation, filtering and definition loading can be set.
 *	They need to be loaded before or available using an autoloader.
 *	@category		cmModules
 *	@package		ENS
 *	@implements		CMM_ENS_Interface_Point
 *	@uses			CMM_ENS_Parameter_Validator
 *	@uses			CMM_ENS_Parameter_Filter
 *	@uses			CMM_ENS_Definition_Loader
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2010 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmmodules/
 *	@since			0.6.3
 *	@version		$Id: Point.php5 667 2010-05-18 15:16:09Z christian.wuerker $
 *	@todo			make use of CMCs predicate validator
 */
class CMM_ENS_Point implements CMM_ENS_Interface_Point
{
	/**	@var		string			$defaultLoader		Default Definition Loader Class */
	protected $defaultLoader		= "CMM_ENS_Definition_Loader";
	/**	@var		string			$defaultValidator	Default Validator Class */
	protected $defaultFilter		= "CMM_ENS_Parameter_Filter";
	/**	@var		string			$defaultValidator	Default Validator Class */
	protected $defaultValidator		= "CMM_ENS_Parameter_Validator";
	/**	@var		string			$validatorClass		Definition Loader Class to use */
	public static $loaderClass		= "CMM_ENS_Definition_Loader";
	/**	@var		string			$filterClass		Filter Class to use */
	public static $filterClass		= "CMM_ENS_Parameter_Filter";
	/**	@var		string			$validatorClass		Validator Class to use */
	public static $validatorClass	= "CMM_ENS_Parameter_Validator";
	/**	@var		array			$services			Array of Services */	
	protected $services				= array();
	/**	@var		mixed			$validator			Validator Class */	
	protected $validator			= null;
	
	/**
	 *	Constructor Method.
	 *	@access		public
	 *	@param		string			$fileName			Service Definition File Name
	 *	@param		string			$cacheFile			Service Definition Cache File Name
	 *	@return		void
	 */
	public function __construct( $fileName, $cacheFile = NULL )
	{
		$this->loadServices( $fileName, $cacheFile );												//  load Service Definition from File
		$this->filter		= Alg_Object_Factory::createObject( self::$filterClass );				//  create Filter Object
		$this->validator	= Alg_Object_Factory::createObject( self::$validatorClass );			//  create Validator Object
	}

	/**
	 *	Constructor Method.
	 *	@access		public
	 *	@param		string			$serviceName		Name of Service to call 
	 *	@param		string			$responseFormat		Format of Service Response
	 *	@param		array			$requestData		Array (or Object with ArrayAccess Interface) of Request Data
	 *	@return		string								Response String of Service	
	 */
	public function callService( $serviceName, $responseFormat = NULL, $requestData = NULL )
	{
		$this->checkServiceDefinition( $serviceName );
		$this->checkServiceMethod( $serviceName );
		$this->checkServiceFormat( $serviceName, $responseFormat );
		$this->checkServiceParameters( $serviceName, $requestData );
		if( !$responseFormat )
			$responseFormat	= $this->getDefaultServiceFormat( $serviceName );

		$parameters	= array( 'format' => $responseFormat );
		
		if( isset( $this->services['services'][$serviceName]['parameters'] ) )
		{
			$names	= $this->services['services'][$serviceName]['parameters'];
			foreach( $names as $name => $rules )
			{
				if( !isset( $requestData[$name] ) )													//  no Value given by Request
				{
					$default	= !isset( $rules['default'] ) ? NULL : $rules['default'];			//  get Default Value
					$value		= $default;
				}
				else
				{
					$type		= empty( $rules['type'] ) ? "string" : $rules['type'];				//  get Type of Parameter
					$value		= $requestData[$name];
					if( $type == "array" && is_string( $value ) )
						$value	= parse_str( $value );												//  realise Request Value
					$value	= $this->realizeParameterType( $value, $type );
				}
				$serviceFilters	= $this->services['services'][$serviceName]['filters'];				//  global Service Filters
				foreach( array_keys( $serviceFilters ) as $filterMethod )							//  iterate
				{
					$value	= $this->filter->applyFilter( $filterMethod, $value );					//  apply Filter to Paramater Value
				}
				if( !empty( $rules['filters'] ) )													//  local Parameter Filters
				{
					foreach( $rules['filters'] as $filter )											//  iterate
					{
						$value	= $this->filter->applyFilter( $filter, $value );					//  apply Filter to Paramater Value
					}
				}
				$parameters[$name]	= $value;
			}
		}
		$className	= $this->getServiceClass( $serviceName );										//  get service class name							
		$object		= Alg_Object_Factory::createObject( $className );								//  create service object
		$response	= Alg_Object_MethodFactory::call( $object, $serviceName, $parameters );			//  call service method
		return $response;
	}

	/**
	 *	Checks Service and throws Exception if Service is not existing.
	 *	@access		public
	 *	@param		string			$serviceName		Name of Service to call 
	 *	@return		void	
	 */
	public function checkServiceDefinition( $serviceName )
	{
		if( !isset( $this->services['services'][$serviceName] ) )
			throw new BadFunctionCallException( 'Service "'.$serviceName.'" is not existing' );
		if( !isset( $this->services['services'][$serviceName]['class'] ) )
			throw new RuntimeException( 'No service class definied for service "'.$serviceName.'"' );
	}

	/**
	 *	Checks Service Method and throws Exception if Service Method is not existing.
	 *	@access		protected
	 *	@param		string			$serviceName		Name of Service to call 
	 *	@return		void	
	 */
	protected function checkServiceMethod( $serviceName )
	{
		if( !isset( $this->services['services'][$serviceName] ) )
			throw new BadFunctionCallException( 'Service "'.$serviceName.'" is not existing' );
		$className	= $this->services['services'][$serviceName]['class'];
		if( !class_exists( $className ) && !$this->loadServiceClass( $className ) )
			throw new RuntimeException( 'Service class "'.$className.'" is not existing' );
		$methods	= get_class_methods( $className );
		if( in_array( $serviceName, $methods ) )
			return;
		$message	= 'Method "'.$serviceName.'" does not exist in service class "'.$className.'"';
		throw new BadMethodCallException( $message );
	}

	/**
	 *	Checks Service Response Format and throws Exception if Format is invalid or no Format and no default Format is set.
	 *	@access		protected
	 *	@param		string			$serviceName		Name of Service to call 
	 *	@param		string			$responseFormat		Format of Service Response
	 *	@return		void	
	 */
	protected function checkServiceFormat( $serviceName, $responseFormat )
	{
		if( $responseFormat )
		{
			$formats	= $this->services['services'][$serviceName]['formats'];
			if( in_array( $responseFormat, $formats ) )
				return;
			$message	= 'Response format "'.$responseFormat.'" for service "'.$serviceName.'" is not available';
			throw new InvalidArgumentException( $message );
		}
		if( $this->getDefaultServiceFormat( $serviceName ) )										//  a default format there is defined 
			return;
		$message	= 'No response format given / set by default for service "'.$serviceName.'"';
		throw new RuntimeException( $message );
	}

	/**
	 *	Checks Service Parameters and throws Exception is something is wrong.
	 *	@access		protected
	 *	@param		string			$serviceName		Name of Service to call 
	 *	@param		arrray			$parameters			Array of requested Parameters
	 *	@return		void	
	 */
	protected function checkServiceParameters( $serviceName, $parameters )
	{
		if( !isset( $this->services['services'][$serviceName]['parameters'] ) )
			return;
		foreach( $this->services['services'][$serviceName]['parameters'] as $name => $rules )
		{
			if( !$rules )
				continue;

			$type	= empty( $rules['type'] ) ? "string" : $rules['type'];							//  get Type of Parameter
			$value	= empty( $rules['default'] ) ? NULL : $rules['default'];						//  get Default Value
			if( isset( $parameters[$name] ) )
			{
				$value	= $parameters[$name];
				if( $type == "array" && is_string( $value ) )
					$value	= parse_str( $value );
			}
			try
			{
				$this->validator->validateParameterValue( $rules, $value );
			}
			catch( InvalidArgumentException $e )
			{
				$message	= 'Parameter "'.$name.'" for service "'.$serviceName.'" failed rule "'.$e->getMessage().'"';
				throw new CMM_ENS_Parameter_Exception( $message );
			}
		}
	}

	/**
	 *	Returns a List of all possibly supported formats.
	 *	@access		public
	 *	@return		array		List of formats
	 */
	public function getAllFormats()
	{
		$formats	= array();
		foreach( $this->services['services'] as $service )											//  iterate all services
			foreach( $service['formats'] as $format )												//  iterate service formats
				if( !in_array( $format, $formats ) )												//  format has not been noted yet
					array_push( $formats, $format );												//  append format to list
		return $formats;																			//  return format list
	}

	/**
	 *	Returns preferred Output Formats if defined.
	 *	@access		public
	 *	@param		string			$serviceName		Name of Service to call 
	 *	@return		string								Default Service Response Format, if defined
	 */
	public function getDefaultServiceFormat( $serviceName )
	{
		$this->checkServiceDefinition( $serviceName );
		$responseFormats	= $this->services['services'][$serviceName]['formats'];
		if( !isset( $this->services['services'][$serviceName]['preferred'] ) )
			return '';
		$default	=  $this->services['services'][$serviceName]['preferred'];
		if( !in_array( $default, $responseFormats ) )
			return '';
		return $default;
	}

	/**
	 *	Returns Description of Service Point.
	 *	@access		public
	 *	@return		string								Title of Service Point
	 */
	public function getDescription()
	{
		return $this->services['description'];	
	}

	/**
	 *	Returns Class of Service.
	 *	@access		public
	 *	@param		string			$serviceName		Name of Service to call 
	 *	@return		string								Class of Service
	 */
	public function getServiceClass( $serviceName )
	{
		$this->checkServiceDefinition( $serviceName );
		return $this->services['services'][$serviceName]['class'];
	}

	/**
	 *	Returns default Type of Service Parameter.
	 *	@access		public
	 *	@param		string			$serviceName		Name of Service to call 
	 *	@param		arrray			$parameterName		Name oif Parameter to get default Type for
	 *	@return		string
	 */
	public function getServiceDefaultParameterType( $serviceName, $parameterName )
	{
		$type	= "unknown";
		$parameters	= $this->getServiceParameters( $serviceName );
		if( !$parameters )
			throw new InvalidArgumentException( 'Service "'.$serviceName.'" does not receive any parameters' );
		if( !isset( $parameters[$parameterName] ) )
			throw new InvalidArgumentException( 'Parameter "'.$parameterName.'" for service "'.$serviceName.'" is not defined' );
		$parameter	= $parameters[$parameterName];
		if( isset( $parameter['type'] ) )
			$type	= $parameter['type'];
		return $type;
	}

	/**
	 *	Returns Description of Service.
	 *	@access		public
	 *	@param		string			$serviceName		Name of Service to call 
	 *	@return		string								Description of Service
	 */
	public function getServiceDescription( $serviceName )
	{
		$this->checkServiceDefinition( $serviceName );
		if( isset( $this->services['services'][$serviceName]['description'] ) )
			return $this->services['services'][$serviceName]['description'];
		return '';
	}

	/**
	 *	Returns available Response Formats of Service.
	 *	@access		public
	 *	@param		string			$serviceName		Name of Service to call 
	 *	@return		array								Response Formats of this Service
	 */
	public function getServiceFilters( $serviceName )
	{
		$this->checkServiceDefinition( $serviceName );
		return $this->services['services'][$serviceName]['filters'];
	}

	/**
	 *	Returns available Response Formats of Service.
	 *	@access		public
	 *	@param		string			$serviceName		Name of Service to call 
	 *	@return		array								Response Formats of this Service
	 */
	public function getServiceFormats( $serviceName )
	{
		$this->checkServiceDefinition( $serviceName );
		return $this->services['services'][$serviceName]['formats'];
	}
	
	/**
	 *	Returns available Formats of Service.
	 *	@access		public
	 *	@param		string			$serviceName		Name of Service to call 
	 *	@return		array								Parameters of Service
	 */
	public function getServiceParameters( $serviceName )
	{
		if( isset( $this->services['services'][$serviceName]['parameters'] ) )
			return $this->services['services'][$serviceName]['parameters'];
		return array();
	}

	/**
	 *	Returns Roles having Access to Service.
	 *	@access		public
	 *	@param		string			$serviceName		Name of Service to call 
	 *	@return		array								List of allowed Roles
	 */
	public function getServiceRoles( $serviceName )
	{
		$this->checkServiceDefinition( $serviceName );
		return $this->services['services'][$serviceName]['roles'];
	}
	
	/**
	 *	Returns Services of Service Point.
	 *	@access		public
	 *	@return		array								Services in Service Point
	 */
	public function getServices()
	{
		return array_keys( $this->services['services'] );
	}

	/**
	 *	Returns Syntax of Service Point.
	 *	@access		public
	 *	@return		string								Syntax of Service Point
	 */
	public function getSyntax()
	{
		return $this->services['syntax'];
	}

	/**
	 *	Returns Title of Service Point.
	 *	@access		public
	 *	@return		string								Title of Service Point
	 */
	public function getTitle()
	{
		return $this->services['title'];
	}
	
	/**
	 *	Loads Service Class, to be overwritten.
	 *	@access		protected
	 *	@param		string			$className			Class Name of Class to load
	 *	@return		bool
	 *	@deprecated	use an autoloader instead, will be removed in 0.2.0
	 *	@todo		remove in 0.2.0
	 */
	protected function loadServiceClass( $className )
	{
		throw new RuntimeException( 'No Service Class Loader implemented. Service Class "'.$className.'" has not been loaded' );
	}
	
	/**
	 *	Loads Service Definitions from XML or YAML File.
	 *	@access		protected
	 *	@param		string			$fileName			Service Definition File Name
	 *	@param		string			$cacheFile			Service Definition Cache File Name
	 *	@return		void
	 *	@deprecated	use an autoloader instead, will be removed in 0.2.0
	 *	@todo		remove in 0.2.0
	 */
	protected function loadServices( $fileName, $cacheFile = NULL )
	{
		$this->loader	= new self::$loaderClass;
		$this->services	= $this->loader->loadServices( $fileName, $cacheFile );
	}

	/**
	 *	Indicates whether a Service is available by its name.
	 *	@access		public
	 *	@param		string			$serviceName		Name of Service to check
	 *	@return		boolean
	 */
	public function hasService( $serviceName )
	{
		return in_array( $serviceName, $this->getServices() );
	}

	protected function realizeParameterType( $value, $type )
	{
		switch( $type )
		{
			case 'array':
				if( is_string( $value ) )
					$value	= parse_str( $value );
				break;
			case 'int':
			case 'integer':
			case 'float':
			case 'double':
			case 'real':
			case 'bool':
			case 'boolean':
				settype( $value, $type );
				break;
		}
		return $value;
	}
}
?>