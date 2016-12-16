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
use \Memcache;

/**
 * Engine para o Memcache
 */
class MemcacheEngine extends CacheEngine {
	/**
	 * Array contendo as configurações padrões da classe
	 * @var array
	 */
	public $_defaultConfigs = [ 	"serializer" => "php",
									"host" => "127.0.0.1",
									"port" => "11211" ];
	
	/**
	 * Variável que salva a instância da conexão do Memcache
	 * @var boolean | \Memcache
	 */
	private $connection = false;
	
	/**
	 * Método construtor
	 * Escolhe o serializador e conecta ao Memcache
	 *
	 * @return void
	 */
	public function __construct(array $config) {
		$this->_configs = array_merge($this->_defaultConfigs, $config);
		$this->connect();

		parent::__construct($this->_configs);				
	}
	
	/**
	 * Conecta a um servidor de Memcache
	 *
	 * @return void
	 */
	private function connect() {
		if(!$this->connection):
			$this->connection = new Memcache();
	
			$this->connection->addServer($this->_configs['host'], $this->_configs['port']);
		endif;
	}
	
	/**
	 * Desconecta de um servidor de Memcache
	 *
	 * @return void
	 */
	public function disconnect() {
		if($this->connection)
			$this->connection->close();
			
		$this->connection = false;
	}
	
	/**
	 * Seta um valor dentro de uma chave no Memcache 
	 *
	 * @param	string		$key			Chave a ser setada no cache
	 * @param	mixed		$value			Valor a ser salvo nesta chave
	 * @param	boolean		$custom_ttl		Tempo personalizado de vida da chave
	 *
	 * @return boolean
	 */
	public function set($key, $value, $custom_ttl = false) {
		$ttl = $custom_ttl !== false ? $custom_ttl : $this->_configs['duration'];
		
		return $this->connection->set($this->_key($key), $value, MEMCACHE_COMPRESSED, $ttl);
	}
	
	/**
	 * Retorna o valor de uma chave no Memcache
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
	 * Deleta uma chave no Memcache
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
		return $this->connection->add($this->_key($key), $value, MEMCACHE_COMPRESSED, $ttl);
	}
	
	/**
	 * Apaga todas as entradas de cache do Memcache, com exceção das prevenidas de clear automático
	 *
	 * @param	boolean		$ignore_prevents	Boolean que determina se o clear ignorará grupos no array de Prevent ou não
	 *
	 * @return void
	 */
	public function clear($ignore_prevents = false) {
		return $this->connection->flush();
	}
}