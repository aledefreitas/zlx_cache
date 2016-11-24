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
use \Redis;

/**
 * Engine para o Redis
 */
class RedisEngine extends CacheEngine {
	/**
	 * Array contendo as configurações padrões da classe
	 * @var array
	 */
	public $_defaultConfigs = [ 	"serializer" => "php",
									"host" => "127.0.0.1",
									"port" => "6379",
									"persistent" => false ];
	
	/**
	 * Array contendo os serializers disponíveis da classe
	 * @var array
	 */				
	private $_serializers = [ 	"php" => Redis::SERIALIZER_PHP,
								"igbinary" => Redis::SERIALIZER_IGBINARY,
								"none" => Redis::SERIALIZER_NONE ];

	/**
	 * Variável que salva a instância da conexão do redis
	 * @var boolean | \Redis
	 */
	private $connection = false;
	
	/**
	 * Método construtor
	 * Escolhe o serializador e conecta ao redis
	 *
	 * @return void
	 */
	public function __construct(array $config) {
		$this->_configs = array_merge($this->_defaultConfigs, $config);

		if(!isset($this->_serializers[$this->_configs['serializer']]))
			$this->_configs['serializer'] = "none";

		$this->connect();

		parent::__construct($this->_configs);				
	}
	
	/**
	 * Conecta a um servidor de redis
	 *
	 * @return void
	 */
	private function connect() {
		if(!$this->connection):
			$this->connection = new Redis();
	
			if($this->_configs['persistent'] === true):
				$this->connection->pconnect($this->_configs['host'], $this->_configs['port']);
			else:
				$this->connection->connect($this->_configs['host'], $this->_configs['port']);
			endif;

			$this->connection->setOption(Redis::OPT_SERIALIZER, $this->_serializers[$this->_configs['serializer']]);
		endif;
	}
	
	/**
	 * Desconecta de um servidor de redis
	 *
	 * @return void
	 */
	public function disconnect() {
		if($this->connection)
			$this->connection->close();
			
		$this->connection = false;
	}
	
	/**
	 * Seta um valor dentro de uma chave no redis 
	 *
	 * @param	string		$key			Chave a ser setada no cache
	 * @param	mixed		$value			Valor a ser salvo nesta chave
	 * @param	boolean		$custom_ttl		Tempo personalizado de vida da chave
	 *
	 * @return boolean
	 */
	public function set($key, $value, $custom_ttl = false) {
		$ttl = $custom_ttl !== false ? $custom_ttl : $this->_configs['duration'];
		
		return $this->connection->set($this->_key($key), $value, $ttl);
	}
	
	/**
	 * Retorna o valor de uma chave no redis
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
	 * Deleta uma chave no redis
	 *
	 * @return void
	 */
	public function delete($key) {
		$this->connection->delete($this->_key($key));
	}
	
	/**
	 * Adiciona um valor a uma chave do cache
	 *
	 * @param	string		$key		Chave do cache
	 * @param	mixed		$value		Valor a adicionar a chave
	 * @param	int			$ttl		Tempo de vida do cache, em segundos
	 *
	 * @return boolean
	 */
	public function add($key, $value, $ttl = 3) {
		return $this->connection->sAdd($this->_key($key), $value);
	}
	
	/**
	 * Apaga todas as entradas de cache do redis, com exceção das prevenidas de clear automático
	 *
	 * @param	boolean		$ignore_prevents	Boolean que determina se o clear ignorará grupos no array de Prevent ou não
	 *
	 * @return void
	 */
	public function clear($ignore_prevents = false) {
		return $this->connection->flushAll();
	}
}