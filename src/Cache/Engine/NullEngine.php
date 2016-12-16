<?php
/**
 * ZLX Cache
 *
 * Cache module for PHP sites
 * Made to make it easier to use cache on websites with a simple API
 *
 * Módulo de cache para sites PHP
 * Feito para facilitar o uso de cache em websites com uma API simples
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