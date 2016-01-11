<?php
/**
 * ZLX Cache
 *
 * Módulo de cache para os sites dos servidores da PROJECT / ZLX. 
 * Feito afim de facilitar e padronizar a geração de caches nos sites com uma API simples.
 *
 * @license		MIT
 * 
 * @link		http://www.github.com/aledefreitas/zlx_cache/
 *
 * @author 		Alexandre de Freitas Caetano <alexandrefc2@hotmail.com>
 * @since		0.1
 * @namespace 	ZLX\Cache
 */
namespace ZLX\Cache;

require_once(__DIR__ . "/CacheEngine.php");

class Cache {
	private static $_instances = [];

	private static $_engines = [ 	"memcached" => 	"MemcachedEngine" ];

	private static $_configs = [ 	"prefix" => "default_zlx",
									"instances" => [
										"default" => [ 	"engine" => "memcached",
														"prefix" => "memcached_default",
														"namespaces" => [] ]
									]
								];
	
	
	private static $namespaces = [];
	private static $_groups = [];
	
	private static $_enabled = true;
	public 	static $_debug = false;
		
	public static function init(array $cache_config) {		
		self::$_configs = array_merge(self::$_configs, $cache_config);

		try {
			self::$_instances["_zlx_null_engine_"] = new \ZLX\Cache\Engine\NullEngine([]);
			
			foreach(self::$_configs['instances'] as $instance => $config):
				if($instance == "_zlx_null_engine_") continue;
				self::_buildEngine(strtolower($instance), $config);
			endforeach;
		} catch(\Exception $e) {
			self::_throwError($e->getMessage());
		}
	}
	
	private static function _throwError($message) {
		trigger_error("[ZLX_CACHE ERROR] ".$message, E_USER_WARNING);
	}
	
	private static function _buildEngine($cache_instance, array $config) {
		if(isset(self::$_engines[strtolower($config['engine'])])):
			$config['prefix'] = self::$_configs['prefix']."_".$config['prefix']."_";
			
			if(!empty($config['namespaces'])):
				foreach($config['namespaces'] as $namespace):
					self::$namespaces[$namespace][] = $cache_instance;
					self::$namespaces[$namespace] = array_unique(self::$namespaces[$namespace]);
				endforeach;
			endif;
						
			$class = "\ZLX\\Cache\\Engine\\".self::$_engines[strtolower($config['engine'])];
			
			self::$_instances[$cache_instance] = new $class($config);
		else:
			if(!get_class($config['engine']))
				throw new \Exception("A Engine '".$config['engine']."' não existe.");
			
			$instance = new $config['engine']($config);
			
			if(!($instance instanceof ZLX\Cache\CacheEngine))
				throw new \Exception("A Engine deve ser uma extensão da classe ZLX\Cache\CacheEngine.");
				
			self::$_instances[$cache_instance] = $instance;
			
			unset($instance);
		endif;
	}
	
	private static function instance($instance = "default") {
		return self::$_enabled?self::$_instances[$instance]:self::$_instances["_zlx_null_engine_"];
	}
	
	public static function set($key, $value, $instance = "default") {
		$engine = self::instance($instance);

		if(is_resource($value))
			return false;
		
		if($value==="")
			return false;
		
		$success = $engine->set($key, $value);

		if(!$success)
			self::_throwError(sprintf("Não foi possível salvar '%s' na instancia de '%s' (%s)", $key, $instance, get_class($engine)));

		return $success;
	}
	
	public static function get($key, $instance = "default") {
		$engine = self::instance($instance);
		
		return $engine->get($key);
	}
	
	public static function delete($key, $instance = "default") {
		$engine = self::instance($instance);
		
		return $engine->delete($key);
	}
	
	public static function remember($key, $callable, $instance = "default") {
		$engine = self::instance($instance);
		
		$value = $engine->get($key);
		
		if(!$value):
			$value = call_user_func($callable);
			$engine->set($key, $value);
		endif;
		
		return $value;
	}
	
	public static function clearNamespace($namespace) {
		if(isset(self::$namespaces[$namespace]) and !empty(self::$namespaces[$namespace])):
			foreach(self::$namespaces[$namespace] as $instance):
				$engine = self::instance($instance);

				$engine->clear();
			endforeach;
		endif;
	}
	
	public static function clearGroup($group, $instance = "default") {
		$engine = self::instance($instance);
		
		return $engine->clearGroup($group);
	}
	
	public static function enable() {
		self::$_enabled = true;
	}
	
	public static function disable() {
		self::$_enabled = false;
	}
}