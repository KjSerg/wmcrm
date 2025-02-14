<?php

namespace FOF\SVGFAVICON\Traits;

trait Singleton {
	private static $instance;
	
	public final function __construct() {}
	public final function __clone() {}
	public final function __wakeup() {}
	
	public final static function get_instance() 
	{
		if(!self::$instance) {
			self::$instance = new self;    
		}
		
		return self::$instance;
	}
}