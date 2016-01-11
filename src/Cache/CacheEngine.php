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

require_once(__DIR__ . "/Engine/MemcachedEngine.php");
require_once(__DIR__ . "/Engine/NullEngine.php");

abstract class CacheEngine {
	private $_defaultConfigs = [	
		"duration" => "+30 minutes",
		"prefix" => "zlx_cache_engine_default",
		"namespaces" => [],
		"groups" => [ "Teste" ],
		"prevent_clear" => []
	];
	
	protected $_configs = [];
	protected $_groups = [];
	protected $_prevent_clear = [];
	protected $_namespaces = [];
	
	abstract public function set($key, $value);
	abstract public function get($key);
	abstract public function delete($key);
	abstract public function clear();
	
	public function __construct(array $config) {
		$this->_configs = array_merge($this->_defaultConfigs, $this->_configs);			
		
		if(is_array($this->_configs['groups'])):
			foreach($this->_configs['groups'] as $group):
				$this->_groups[$group] = 0;
			endforeach;
		endif;
		
		$this->_configs['duration'] = strtotime($this->_configs['duration'], 0);
		
		$this->_prefix = $this->_configs['prefix'];

		$cacheGroups = $this->get($this->_prefix."CacheComponentGroups");
		
		$this->_groups = $cacheGroups?$cacheGroups:$this->_groups;
	}
	
	protected function _key($key) {
		$key = $this->sanitizeKey($key);

		if($group = explode(".", $key))
			$group = $group[0];
		else
			$group = "";
		
		$groupToCompare = $group;
		
		if(isset($this->_groups[$groupToCompare]))
			return $group."_".$this->_groups[$groupToCompare]."_".$key;
		
		return $key;
	}
	
	/**
	 * Sobre-escrita do método clearGroup da Memcached.
	 * Nele, incrementamos o valor de um ao grupo, simulando um delete completo no mesmo
	 *
	 * @param	string	$groupKey	Key do grupo a ser limpado
	 *
	 * @return void
	 */ 
	public function clearGroup($groupKey) {
		if(isset($this->_groups[$groupKey])):
			$this->_groups[$groupKey]++;

			if($this->_groups[$groupKey]>999)
				$this->_groups[$groupKey] = 0;
				
			$this->saveGroups();
		endif;
	}
	
	/**
	 * Método que salva e renova o tempo de vida dos dados persistidos de grupos no cache
	 *
	 * @return void
	 */
	private function saveGroups() {
		$this->set($this->_prefix."CacheComponentGroups", $this->_groups);
	}

	
	protected function sanitizeKey($key) {
		return preg_replace("/([^a-z0-9\._-]+)/i","_",$key);
	}
	
}