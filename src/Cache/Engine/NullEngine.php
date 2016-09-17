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
 * @namespace 	ZLX\Cache\Engine
 */

namespace ZLX\Cache\Engine;

use ZLX\Cache\CacheEngine;

/**
 * Engine nula
 */
class NullEngine extends CacheEngine {
	public function set($key, $value, $custom_ttl = false) {
		return true;
	}
	
	public function get($key) {
		return false;
	}

	public function delete($key) {
		return true;
	}
	
	public function clear($ignore_prevents = false) {
		return false;
	}
	
	public function add($key, $value, $ttl = 3) {
		return true;	
	}
}