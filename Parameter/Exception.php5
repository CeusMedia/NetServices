<?php
class CMM_ENS_Parameter_Exception extends Exception implements Serializable
{

	/**
	 *	Returns serial of exception.
	 *	@access		public
	 *	@return		string
	 */
	public function serialize()
	{
		return serialize( array( $this->message, $this->code, $this->file, $this->line ) );
	}

	/**
	 *	Recreates an exception from its serial.
	 *	@access		public
	 *	@param		string		$serial			Serial string of an validation exception
	 *	@return		void
	 */
	public function unserialize( $serial )
	{
		list( $this->message, $this->code, $this->file, $this->line ) = unserialize( $serial );
	}
}
?>
