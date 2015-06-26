<?php
require_once 'File.php';

class Log {
	private static $file = NULL;
	private static $logDir = 'Logs/';
	private static $logPrefix = 'log_';
	
	static function init() {
		if (self::$file == NULL) {
			if (!file_exists(self::$logDir)) mkdir(self::$logDir);
			self::$file = new File(self::$logDir.self::$logPrefix.date('Ymd').'.txt', 'a');
		}
	}
	
    static function w($s) {
		$uri = explode('/', $_SERVER['REQUEST_URI']);
		$tag = array_pop($uri);
		$time = date('H:i:s');
		
		// Variadic function: it has optional parameters
		if (func_num_args() == 2) {
			$tag.= ' - '.func_get_arg(1);
		}
		
		unset($uri);
		return fwrite(self::$file->f, $time.' ('.$tag.'): '.$s."\r\n");
    }
}
Log::init();
