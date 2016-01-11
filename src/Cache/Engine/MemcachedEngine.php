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

/**
 * Engine para o Memcached
 */
class MemcachedEngine extends CacheEngine {
	/**
	 * Array contendo as configurações padrões da classe
	 * @var array
	 */
	public $_defaultConfigs = [ 	"serializer" => "php",
									"host" => "127.0.0.1",
									"port" => "11211",
									"compress" => true ];
	
	/**
	 * Array contendo os serializers disponíveis da classe
	 * @var array
	 */				
	private $_serializers = [ 	"php" => Memcached::SERIALIZER_PHP,
								"igbinary" => Memcached::SERIALIZER_IGBINARY,
								"json" => Memcached::SERIALIZER_JSON,
								"msgpack" => Memcached::SERIALIZER_MSGPACK ];
	
	/**
	 * Variável que salva a instância da conexão do Memcached
	 * @var boolean | \Memcached
	 */
	private $connection = false;
	
	/**
	 * Método construtor
	 * Escolhe o serializador e conecta ao Memcached
	 *
	 * @return void
	 */
	public function __construct(array $config) {
		$this->_configs = array_merge($this->_defaultConfigs, $config);

		if($this->_configs['serializer'] == "msgpack"):
			if(!(defined('Memcached::HAVE_MSGPACK') and Memcached::HAVE_MSGPACK))
				$this->_configs['serializer'] = "php";
		endif;
		
		if(!isset($this->_serializers[$this->_configs['serializer']]))
			$this->_configs['serializer'] = "php";

		$this->connect();

		parent::__construct($this->_configs);				
	}
	
	/**
	 * Conecta a um servidor de Memcached
	 *
	 * @return void
	 */
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
	 * @param	string		$key		Chave a ser setada no cache
	 * @param	mixed		$value		Valor a ser salvo nesta chave
	 *
	 * @return boolean
	 */
	public function set($key, $value) {
		return $this->connection->set($this->_key($key), $value, $this->_configs['duration']);
	}
	
	/**
	 * Retorna o valor de uma chave no memcached
	 *
	 * @param	string		$key		Chave a ser retornada
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
	
	/**
	 * Apaga todas as entradas de cache do memcached, com exceção das prevenidas de clear automático
	 *
	 * @param	boolean		$ignore_prevents	Boolean que determina se o clear ignorará grupos no array de Prevent ou não
	 *
	 * @return void
	 */
	public function clear($ignore_prevents = false) {
		$memcached_keys = $this->connection->getAllKeys();
		
		foreach($memcached_keys as $cacheKey):
			if(strpos($cacheKey, $this->_configs['prefix']) == 0):
				if($ignore_prevents === false):
					foreach($this->_configs['prevent_clear'] as $preventGroup):
						if(strpos($cacheKey, $preventGroup.".")>-1)
							continue 2;
					endforeach;
				endif;
				
				$this->delete($cacheKey);
			endif;
		endforeach;
	}
}