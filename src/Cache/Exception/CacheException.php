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
 * @namespace 	ZLX\Cache
 */
namespace ZLX\Cache\Exception;

use Psr\Cache\CacheException as PSR_Cache_Exception;
use \Exception;

/**
 * Class to handle thrown Cache implementation Exceptions
 *
 * Classe para lidar com Exceptions da implementação Cache
 */
class CacheException extends Exception implements PSR_Cache_Exception {
	
}