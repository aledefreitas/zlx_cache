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

class CacheStorage
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * Cria uma chave com um valor no cache
     *
     * @param	string		$key			Nome da chave
     * @param	mixed		$value			Valor atribuído a chave
     *
     * @return boolean
     */
    public function set($key, $value)
    {
        $this->setData($key, $value);
    }

    /**
     * Retorna o valor de uma chave no cache
     *
     * @param	string		$key		Nome da chave
     *
     * @return mixed
     */
    public function get($key)
    {
        return $this->getData($key) ?? false;
    }

    /**
     * Apaga uma chave do cache
     *
     * @param	string		$key		Nome da chave
     *
     * @return boolean
     */
    public function delete($key)
    {
        $this->removeData($key);
    }

    /**
     * Apaga todas as chaves do cache
     *
     * @return boolean
     */
    public function clear()
    {
        foreach ($this->data as $key => $data) {
            $this->removeData($key);
        }
    }

    /**
     * @param  string  $key
     * @param  mixed  $value
     *
     * @return void
     */
    private function setData($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * @param  string  $key
     *
     * @return mixed
     */
    private function getData($key)
    {
        return $this->data[$key] ?? null;
    }

    /**
     * @param  string  $key
     *
     * @return void
     */
    private function removeData($key)
    {
        $this->data[$key] = null;
        unset($this->data[$key]);
    }
}
