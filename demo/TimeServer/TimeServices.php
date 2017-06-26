<?php
class TimeServices extends \CeusMedia\NetServices\Response{

	public function time( $format, $output ){
		$time	= $output ? date( $output, time() ) : time();
		return $this->convertToOutputFormat( $time, $format );
	}
}
?>
