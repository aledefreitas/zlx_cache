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

/**
 * Classe abstrata das Engines de Cache
 * Todas as classes que forem ser utilizadas no ZLX\Cache como Engine devem ser filhas desta classe.
 *
 * ### Criando e utilizando uma Engine personalizada
 * ---------
 * O ZLX\Cache permite que sejam criadas Engines personalizadas para que possam-se desenvolver as lógicas necessárias do programador
 * através de abstração da classe ZLX\Cache\CacheEngine!
 *
 * Abaixo segue o exemplo de uma classe personalizada:
 * ```php
 *	use ZLX\Cache\CacheEngine;
 *	
 * 	class CustomCacheEngine extends CacheEngine {
 *		public $_defaultConfigs; // Configurações padrões
 *			 
 *		public function __construct(array $config) {
 *			// Lógica do método construtor
 *
 *			$this->_configs = array_merge($this->_defaultConfigs, $config); // Merge das configurações padrões. É necessário caso haja configurações padrões.
 *			parent::__construct($config);	
 *		}
 *		
 *		public function set($key, $value) {
 *			// Lógica de salvamento de valores no cache	
 *		}
 *		
 *		public function get($key) {
 *			// Lógica de busca de valores no cache	
 *		}
 *		
 *		public function delete($key) {
 *			// Lógica de apagamento de valor no cache	
 *		}
 *		
 *		public function clear($ignore_prevents) {
 *			// Lógica para reset do cache	
 *		}
 *	}
 * ```
 *
 * Então, será possível adicionar instâncias desta classe de duas maneiras diferentes:
 * ```php
 *		// Através da inicialização do Cache
 *  	use ZLX\Cache\Cache;
 *		$config = [ 'prefix' => 'cache_prefix',
 *					'instances' => [
 *						'meu_cache' => [	'engine' => 'CustomCacheEngine',
 *											'duration' => '+10 minutes',
 *											'groups' => [ 'Posts', 'Comments', 'Session' ],
 *											'prevent_clear' => [ 'Session' ] ]
 *					]
 *				];
 *				
 *  	Cache::init($config);
 * ```
 *
 *
 * ```php
 *		// Através da inicialização do Cache
 *  	use ZLX\Cache\Cache;
 *		$config = [	'engine' => 'CustomCacheEngine',
 *					'duration' => '+10 minutes',
 *					'groups' => [ 'Posts', 'Comments', 'Session' ],
 *					'prevent_clear' => [ 'Session' ] 
 *				];
 *				
 *  	Cache::create('meu_cache', $config);
 * ```
 *
 * Desta forma, você pode criar engines que utilizam a lógica que for necessária implementada.
 *
 * @param	array	$config		Configurações da Engine
 * @return	void
 */
abstract class CacheEngine {
	/** 
	 * Array contendo as configurações padrões da Engine
	 * @var array
	 */
	private $_defaultConfigs = [	
		"duration" => "+30 minutes",
		"prefix" => "zlx_cache_engine_default",
		"namespaces" => [],
		"groups" => [],
		"prevent_clear" => []
	];
	
	/**
	 * Array contendo as configurações finais da Engine, com o merge das default com as enviadas pelo programador
	 * @var array
	 */
	protected $_configs = [];
	
	/**
	 * Array contendo os grupos da Engine
	 * @var array
	 */
	protected $_groups = [];
	
	/**
	 * Array contendo os grupos que devem ser ignorados num eventual clear
	 * @var array
	 */
	protected $_prevent_clear = [];
	
	/**
	 * Array contendo os namespaces a que esta Engine pertence
	 * @var array
	 */
	protected $_namespaces = [];
	
	/**
	 * Cria uma chave com um valor no cache
	 * 
	 * @param	string		$key		Nome da chave
	 * @param	mixed		$value		Valor atribuído a chave
	 * 
	 * @return boolean
	 */
	abstract public function set($key, $value);

	/**
	 * Retorna o valor de uma chave no cache
	 * 
	 * @param	string		$key		Nome da chave
	 * 
	 * @return mixed
	 */
	abstract public function get($key);

	/**
	 * Apaga uma chave do cache
	 * 
	 * @param	string		$key		Nome da chave
	 * 
	 * @return boolean
	 */
	abstract public function delete($key);

	/**
	 * Apaga todas as chaves do cache
	 * 
	 * @param	boolean		$ignore_prevents	Boolean que determina se o clear ignorará grupos no array de Prevent ou não
	 *
	 * @return boolean
	 */
	abstract public function clear($ignore_prevents = false);
	
	/**
	 * Método construtor
	 * Faz merge das configurações enviadas pela classe filha e as padrões, salva os grupos
	 *
	 * @return void
	 */
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
	
	/**
	 * Retorna uma chave com o padrão de grupo e o prefixo embutidos
	 *
	 * @param	string		$key		Chave a ser filtrada
	 *
	 * @return string
	 */
	protected function _key($key) {
		$key = $this->sanitizeKey($key);

		if($group = explode(".", $key))
			$group = $group[0];
		else
			$group = "";
		
		$groupToCompare = $group;
		
		if(isset($this->_groups[$groupToCompare]))
			return $this->_configs['prefix'].$group."_".$this->_groups[$groupToCompare]."_".$key;
		
		return $key;
	}
	
	/**
	 * Método que apaga um grupo do Cache
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

	/**
	 * Sanitiza uma chave, retirando caracteres inválidos
	 *
	 * @param	string		$key		Chave a ser filtrada
	 *
	 * @return string
	 */
	protected function sanitizeKey($key) {
		return preg_replace("/([^a-z0-9\._-]+)/i","_",$key);
	}
	
}