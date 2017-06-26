<?php
/**
 *	Public Net Services for Demonstration.
 *	@package		demos.NetServices
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@version		0.3
 */
/**
 *	Public Net Services for Demonstration.
 *	@package		demos.NetServices
 *	@extends		\CeusMedia\NetServices\Response
 *	@uses			Alg_InputFilter
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@version		0.3
 */
class Services_Disclosure extends \CeusMedia\NetServices\Response
{
	public function getPaths( $format )
	{
		$list	= array();
		$regExp	= '/^services(.*)xml$/';
		$index	= new FS_File_RegexFilter( 'config/', $regExp );
		foreach( $index as $file )
		{
			$path	= preg_replace( $regExp, '\\1', $file->getFilename() );
			$path	= preg_replace( '/^\.(.+)/', '\\1', $path );
			$path	= str_replace( '.', '/', $path );
			$list[$path]	= $path;
		}
		ksort( $list );
		if( array_key_exists( '/', $list ) )
		{
			unset( $list['/'] );
			array_unshift( $list, '/' );
		}
		$list	= array_values( $list );
		return $this->convertToOutputFormat( $list, $format );
	}

	public function getPathDescription( $format, $path )
	{
		$title	= $this->getServicePointFromPath( $path )->getDescription();
		return $this->convertToOutputFormat( $title, $format );
	}

	public function getPathTitle( $format, $path )
	{
		$title	= $this->getServicePointFromPath( $path )->getTitle();
		return $this->convertToOutputFormat( $title, $format );
	}

	protected function getServiceFileFromPath( $path )
	{
		$path	= preg_replace( '/^(\/)*/', '', $path );
		$path	= preg_replace( '/(\/)*$/', '', $path );
		if( $path )
			$path	.= '/';
		$path	= str_replace( '/', '.', $path );
		$file	= 'config/services.'.$path.'xml';
		if( !file_exists( $file ) )
			throw new InvalidArgumentException( 'Service path is invalid' );
		return $file;
	}

	protected function getServicePointFromPath( $path )
	{
		$fileName	= $this->getServiceFileFromPath( $path );
		return new \CeusMedia\NetServices\Point( $fileName );
	}

	public function getServicesFromPath( $format, $path )
	{
		$point		= $this->getServicePointFromPath( $path );
		$list		= $point->getServices();
		natcasesort( $list );
		return $this->convertToOutputFormat( array_values( $list ), $format );
	}

	public function getServiceDescription( $format, $path, $service )
	{
		$point		= $this->getServicePointFromPath( $path );
		$list		= $point->getServiceDescription( $service );
		return $this->convertToOutputFormat( $list, $format );
	}

	public function getServiceParameters( $format, $path, $service )
	{
		$point		= $this->getServicePointFromPath( $path );
		$list		= $point->getServiceParameters( $service );
		return $this->convertToOutputFormat( $list, $format );
	}

	public function getServiceFilters( $format, $path, $service )
	{
		$point		= $this->getServicePointFromPath( $path );
		$list		= $point->getServiceFilters( $service );
		return $this->convertToOutputFormat( $list, $format );
	}

	public function getServiceFormats( $format, $path, $service )
	{
		$point		= $this->getServicePointFromPath( $path );
		$list		= $point->getServiceFormats( $service );
		return $this->convertToOutputFormat( $list, $format );
	}

	public function getServiceRoles( $format, $path, $service )
	{
		$point		= $this->getServicePointFromPath( $path );
		$list		= $point->getServiceRoles( $service );
		return $this->convertToOutputFormat( $list, $format );
	}
}
?>
