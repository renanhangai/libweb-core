<?php
namespace LibWeb;

/**
 * Class config
 */
class Config {

	private static $config = array();
	/**
	 * Feed a json file
	 */
	public static function feedJSON( $file ) {
		$contents = @file_get_contents( $file );
		if ( $contents === false )
			throw new \Exception( "Invalid file '".$file."'" );
		$obj = json_decode( $contents, true );
		$err = json_last_error();
		if ( $err )
			throw new \Exception( "Could not decode file '".$file."'" );
		self::feed( $obj );
	}
	/**
	 * Feed a single object
	 */
	public static function feed( $obj ) {
		self::$config = self::mergeConfig( self::$config, $obj );
	}
	/**
	 * Get a configuration
	 */
	public static function get( $name ) {
		$name  = explode( '.', $name );
		$value = self::$config;
		for ( $i = 0, $len = count( $name ); $i < $len; ++$i ) {
			$value = @$value[ $name[ $i ] ];
		}
		return $value;
	}
	/**
	 * Set a configuration
	 */
	public static function set( $name, $value ) {
		$name  = explode( '.', $name );
		self::setInternal( self::$config, $name, $value );
		return $value;
	}
	/**
	 * Internally set a configuration object
	 */
    private static function setInternal( &$config, $path, $value, $i = 0 ) {
		if ( is_string( $path ) )
			return self::setInternal( $config, explode( ".", $path ), $value, $i );
		if ( $i >= count( $path ) ) {
			$config = $value;
			return;
		}
		if ( !is_array( $config ) )
			$config = array();
		self::setInternal( $config[ $path[ $i ] ], $path, $value, $i + 1 );
	}
	/**
	 * Merge two config objects
	 */
	public static function mergeConfig( $config1, $config2 )
	{
		$config = $config1;
		foreach ( $config2 as $key => $value )
		{
			$overwrite = false;
			$newkey    = $key;
			if ( $key[0] === '!' ) {
				$overwrite = true;
				$newkey = substr( $key, 1 );
			}
			if ( !$overwrite && is_array( $value ) && isset($config[$key]) && is_array ($config[$key]) ) {
				$merged = self::mergeConfig( $config[$key], $value );
				self::setInternal( $config, $newkey, $merged );
			} else {
				self::setInternal( $config, $newkey, $value );
			}
		}
		return $config;
	}
	// Get the raw config object
	public static function raw() {
	    return self::$config;
	}
	
}

