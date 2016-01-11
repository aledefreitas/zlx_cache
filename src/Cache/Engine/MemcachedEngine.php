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
use \Memcached;

class MemcachedEngine extends CacheEngine {
	protected $_configs = [ "serializer" => "php",
							"host" => "127.0.0.1",
							"port" => "11211",
							"compress" => true ];
							
	private $_serializers = [ 	"php" => Memcached::SERIALIZER_PHP,
								"igbinary" => Memcached::SERIALIZER_IGBINARY,
								"json" => Memcached::SERIALIZER_JSON,
								"msgpack" => Memcached::SERIALIZER_MSGPACK ];
	private $connection = false;
	
	
	public function __construct(array $config) {
		$this->_configs = array_merge($this->_configs, $config);

		if($this->_configs['serializer'] == "msgpack"):
			if(!(defined('Memcached::HAVE_MSGPACK') and Memcached::HAVE_MSGPACK))
				$this->_configs['serializer'] = "php";
		endif;
		
		if(!isset($this->_serializers[$this->_configs['serializer']]))
			$this->_configs['serializer'] = "php";

		$this->connect();

		parent::__construct($this->_configs);				
	}
	
	private function connect() {
		if(!$this->connection):
			$this->connection = new Memcached();
	
			$this->connection->addServer($this->_configs['host'], $this->_configs['port']);
			$this->connection->setOption(Memcached::OPT_COMPRESSION, $this->_configs['compress']);
			$this->connection->setOption(Memcached::OPT_SERIALIZER, $this->_serializers[$this->_configs['serializer']]);
		endif;
	}
	
	/**
	  * Desconecta de um servidor de memcached
	  *
	  * @return void
	  */
	public function disconnect() {
		if($this->connection)
			$this->connection->close();
			
		$this->connection = false;
	}
	
	/**
	  * Seta um valor dentro de uma chave no memcached 
	  *
	  * @return boolean
	  */
	public function set($key, $value) {
		return $this->connection->set($this->_key($key), $value, $this->_configs['duration']);
	}
	
	/**
	  * Retorna o valor de uma chave no memcached
	  *
	  * @return mixed
	  */
	public function get($key) {
		$data = $this->connection->get($this->_key($key));
		if(!$data) return false;
		
		return $data;
	}
	
	/**
	  * Deleta uma chave no memcached
	  *
	  * @return void
	  */
	public function delete($key) {
		$this->connection->delete($this->_key($key));
	}
	
	public function clear() {
		
	}
}