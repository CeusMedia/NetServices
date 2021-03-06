<?php
/**
 *	Net Service Caller.
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
 *	@package		CeusMedia_NetServices
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2015 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/NetServices
 */
namespace CeusMedia\NetServices;
/**
 *	@category		Library
 *	@package		CeusMedia_NetServices
 *	@uses			CMM_ENS_Client
 *	@uses			CMM_ENS_Decoder
 *	@uses			Alg_Time_Clock
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2015 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/NetServices
 *	@todo			Unit Test
 */
class Caller{

	/**	@var		array		$calls		Array of called Services with Response */
	protected $calls	= array();

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		string		$url		Base URL of Net Service
	 *	@return		void
	 */
	public function __construct( $url ){
		$this->client	= new \CeusMedia\NetService\Client( $url );
	}

	/**
	 *	This Method masks Net Service Calls under local Method Calls.
	 *	Catches Method Calls on current Object and requests Service Response of Method Key.
	 *	@access		public
	 *	@param		string		$key		Method Key, Method called on current Object
	 *	@return		mixed
	 */
	public function __call( $key, $arguments ){
		$clock		= new \Alg_Time_Clock();
		$arguments	= $this->buildArgumentsForRequest( $arguments );
		$response	= $this->client->post( $key, "php", $arguments );
		$this->calls[]	= array(
			'service'	=> $key,
			'arguments'	=> $arguments,
			'response'	=> serialize( $response ),
			'time'		=> $clock->stop( 6, 0 ),
		);
		return $response;
	}

	/**
	 *	Builds Service Call Parameters from Array.
	 *	@access		protected
	 *	@param		array		$arguments	Arguments to build Service Call Parameters from
	 *	@return		array
	 */
	protected function buildArgumentsForRequest( $arguments ){
		if( !$arguments )
			return array();
		if( count( $arguments ) == 1 && is_array( $arguments[0] ) )
			return $arguments[0];
		return array( 'argumentsGivenByServiceCaller' => serialize( $arguments ) );
	}

	/**
	 *	Returns Array of called Services with Response.
	 *	@access		public
	 *	@return		array
	 */
	public function getCalls(){
		return $this->calls;
	}
}
?>
