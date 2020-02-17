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
use ZLX\Cache\Engine\Contracts\CacheEngineInterface;
use \Redis;

/**
 * Engine para o Redis
 */
class RedisEngine extends CacheEngine implements CacheEngineInterface
{
	/**
	 * Array contendo as configurações padrões da classe
	 * @var array
	 */
	public $_defaultConfigs = [
		"host" => "127.0.0.1",
		"port" => "6379",
		"database" => 0,
		"persistent" => false,
        'password' => null,
	];

	/**
	 * Variável que salva a instância da conexão do redis
	 * @var boolean | \Redis
	 */
	protected $connection = false;

	/**
	 * Método construtor
	 * Escolhe o serializador e conecta ao redis
	 *
	 * @return void
	 */
	public function __construct(array $config)
	{
		$this->_configs = array_merge($this->_defaultConfigs, $config);

		$this->connect();

		parent::__construct($this->_configs);
	}

	/**
	 * Método destrutor
	 *
	 * @return void
	 */
	public function __destruct()
	{
		if($this->connection)
			$this->connection->close();

		$this->connection = false;
	}

	/**
	 * Conecta a um servidor de redis
	 *
	 * @return void
	 */
	protected function connect()
	{
		if(!$this->connection):
			$this->connection = new Redis();

			if($this->_configs['persistent'] === true):
				$this->connection->pconnect($this->_configs['host'], $this->_configs['port']);
			else:
				$this->connection->connect($this->_configs['host'], $this->_configs['port']);
			endif;

            if (isset($this->_configs['password'])) {
                $this->connection->auth($this->_configs['password']);
            }

			$this->connection->select($this->_configs['database']);
		endif;
	}

	/**
	 * Desconecta de um servidor de redis
	 *
	 * @return void
	 */
	public function disconnect()
	{
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
	public function set($key, $value, $custom_ttl = false)
	{
		$ttl = $custom_ttl !== false ? $custom_ttl : $this->_configs['duration'];

		if(!is_int($value)) {
			$value = json_encode($value);
		}

		return $this->connection->setEx($this->_key($key), $ttl, $value);
	}

	/**
	 * Retorna o valor de uma chave no redis
	 *
	 * @param	string		$key		Chave a ser retornada
	 *
	 * @return mixed
	 */
	public function get($key, $is_stale = false)
	{
		$data = $this->connection->get($this->_key($key, $is_stale));

		if (preg_match('/^[-]?\d+$/', $data)) {
            return (int)$data;
        }

        if ($data !== false && is_string($data)) {
            return json_decode($data, true);
        }

        return $data;
	}

	/**
	 * Deleta uma chave no redis
	 *
	 * @return void
	 */
	public function delete($key)
	{
		$this->connection->del($this->_key($key));
		$this->connection->del($this->_key($key.'_stale_data'));
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
	public function add($key, $value, $ttl = 3)
	{
		if(!is_int($value)) {
			$value = json_encode($value);
		}

		$key = $this->_key($key);

		if ($this->connection->exists($key)) {
			return false;
		}

		$this->connection->setEx($key, $ttl, $value);

		return true;
	}

	/**
	 * Apaga todas as entradas de cache do redis, com exceção das prevenidas de clear automático
	 *
	 * @param	boolean		$ignore_prevents	Boolean que determina se o clear ignorará grupos no array de Prevent ou não
	 *
	 * @return void
	 */
	public function clear($ignore_prevents = false)
	{
		return $this->connection->flushAll();
	}
}
