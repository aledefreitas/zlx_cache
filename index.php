<?php

require(__DIR__."/src/Cache/Cache.php");
	
use ZLX\Cache\Cache;
$config = [];

Cache::init($config);
	
	echo Cache::remember("Teste.teste", function() {
		return "teste";	
	});
	
	echo Cache::get("Teste.teste");