<?php
	require_once(__DIR__."/vendor/autoload.php");
	require_once(__DIR__."/src/Cache/Cache.php");
	
	use ZLX\Cache\Cache;
	
    $config = [ 'prefix' => 'cache_prefix',
                'instances' => [
                    'default' => [  'engine' => 'memcached',
									'prefix' => 'prefixo_default',
                                    'duration' => '+10 minutes',
                                    'groups' => [ 'Posts', 'Comments', 'Session' ],
									'namespaces' => [ 'Posts' ],
                                    'prevent_clear' => [ 'Session' ] ]
                ]
            ];
			
	Cache::init($config);
	
	Cache::set("Posts.post_1", "Post 1 no cache Default", "default");

	echo var_dump(Cache::get("Posts.post_1", "default")).PHP_EOL; // Deve retornar: "Post 1 no cache Default"
	
	
	$config = [ 'engine' => 'memcached',
				'prefix' => 'prefixo_memcached_meu_cache',
				'duration' => '+25 minutes',
				'groups' => [ 'Posts', 'Comments', 'Session' ],
				'namespaces' => [ 'Posts' ],
				'prevent_clear' => [ 'Session' ] ];
				
	Cache::create('meu_cache', $config);
	
	Cache::set("Posts.post_2", "Post 2 no cache", "meu_cache");
	Cache::clearGroup("Posts", "default");	
	
	echo var_dump(Cache::get("Posts.post_1", "default")).PHP_EOL; // Deve retornar: valor stale
	echo var_dump(Cache::get("Posts.post_2", "default")).PHP_EOL; // Deve retornar: valor stale
	echo var_dump(Cache::get("Posts.post_2", "meu_cache")).PHP_EOL; // Deve retornar: "Post 2 no cache"

	Cache::set("Posts.post_2", "Post 2 no cache Default", "default");
	Cache::clearNamespace("Posts");
	echo var_dump(Cache::get("Posts.post_2", "default")).PHP_EOL; // Deve retornar: valor stale
	echo var_dump(Cache::get("Posts.post_2", "meu_cache")).PHP_EOL; // Deve retornar: valor stale
	
	echo var_dump(Cache::remember("Posts.post_3", function() {
		return "Post 3";
	}, "meu_cache")).PHP_EOL; // Deve retornar: "Post 3"
	Cache::clearGroup("Posts", "meu_cache");	
	
	echo var_dump(Cache::remember("Posts.post_3", function() {
		return "Post 4";
	}, "meu_cache")).PHP_EOL; // Deve retornar: "Post 4"
	
	Cache::clearGroup("Posts", "meu_cache");	

	echo var_dump(Cache::get("Posts.post_3", "meu_cache")).PHP_EOL; // Deve retornar: false (Pois ele seta um novo stale)
	echo var_dump(Cache::get("Posts.post_3", "meu_cache")).PHP_EOL; // Deve retornar: "Post 4" (Usando o Stale)
	
	Cache::set("Posts.post_2", "Post 2 no cache", "meu_cache");
	Cache::set("Session.teste", "teste de session", "meu_cache");
	
	Cache::clear(false, "meu_cache");
	echo var_dump(Cache::get("Posts.post_2", "meu_cache")).PHP_EOL; // Deve retornar: false
	echo var_dump(Cache::get("Session.teste", "meu_cache")).PHP_EOL; // Deve retornar: "teste de session"

	Cache::clear(true, "meu_cache");
	echo var_dump(Cache::get("Session.teste", "meu_cache")).PHP_EOL; // Deve retornar: false