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

/**
 * # ZLX Cache
 * ===============
 * ZLX Cache v1.0.0 - 11 de Janeiro de 2016
 *
 * por Alexandre de Freitas Caetano
 * http://github.com/aledefreitas/
 *
 * baseado em CakePHP Cache
 * http://github.com/cakephp/cakephp/
 *
 *
 * ### Introdução
 * ---------
 * A classe ZLX\Cache traz uma forma elegante de utilizar cache na aplicação, facilitando a implementação de sistemas de Cache em sua aplicação.
 * Com esta classe é possível criar várias instâncias separadas de Cache para sua aplicação, sem forçar que sua aplicação se restrinja a apenas uma implementação de cache.
 * Você poderá alterar ou criar engines de cache com sua própria lógica, sobre-escrevendo a classe CacheEngine.
 * Também pode apagar dados de várias instâncias de uma só vez, através dos namespaces atribuídos às instâncias!
 *
 *
 * ### Instalação
 * ---------
 * É possível instalar o ZLX\Cache através do Composer. Adicione no seu ```composer.json```:
 * ```
 * {
 *    'repositories': [
 *        {
 *            'url': 'https://github.com/aledefreitas/zlx_cache.git',
 *            'type': 'git'
 *        }
 *    ],
 *    'require': {
 *        'aledefreitas/zlx_cache': '*'
 *    }
 * }
 * ```
 * ***É necessário que o servidor que utilizará o composer tenha sua SSH-KEY cadastrada neste repositório***
 *
 * Ou então você pode baixar a release que quiser no (nosso repositório)[https://github.com/aledefreitas/zlx_cache/releases], e fazer um require no inicio de seu código:
 * ```php
 * 		require_once('path/to/zlx/cache/Cache.php');
 * ```
 *
 * ### Requerimentos
 * ---------
 *	- ***PHP 5.6+***
 *  - ***Memcached***
 *
 * ### Modo de uso
 * ---------
 * Abaixo citaremos as funções que são utilizadas no gerenciamento do cache na sua aplicação.
 * Primeiramente, iniciamos o Cache, no início de seu código de inicialização da aplicação:
 * ```php
 *  	use ZLX\Cache\Cache;
 *		$config = [ 'prefix' => 'cache_prefix',
 *					'instances' => [
 *						// Utilizamos default, pois é a configuração padrão do cache
 *						'default' => [	'engine' => 'memcached',
 *										'duration' => '+10 minutes',
 *										'groups' => [ 'Posts', 'Comments', 'Session' ],
 *										'prevent_clear' => [ 'Session' ] ]
 *					]
 *				];
 *		// Iniciamos o cache
 *  	Cache::init($config);
 * ```
 * #### Atenção!
 * Tenha muita atenção ao setar a configuração 'prefix' na inicialização do Cache. Ela é o prefixo para as entradas de cache de seu site,
 * e não deve ser duplicada com nenhum outro prefixo igual em qualquer outro site no servidor inteiro.
 *
 * #### Criando instâncias on-the-fly (em tempo de execução)
 * Podemos criar instancias novas programáticamente em tempo de execução com a função ```Cache::create()```:
 * ```php
 *  	use ZLX\Cache\Cache;
 *		$config = [	'engine' => 'memcached',
 *					'duration' => '+10 minutes',
 *					'groups' => [ 'Posts', 'Comments', 'Session' ],
 *					'prevent_clear' => [ 'Session' ]
 *				];
 *
 *  	Cache::create('meu_cache', $config);
 * ```
 * #### Atributos das instâncias:
 * - 'engine': É o Engine a ser utilizado. Ex.: 'memcached'
 * - 'duration': Duração das chaves de cache. Ex.: '+40 minutes'
 * - 'prefix': Prefixo da instância. Ex.: 'prefixo_zlx'
 * - 'namespaces': Array contendo os namespaces ao qual a instância pertence. Ex.: [ 'Posts', 'Admin' ]
 * - 'groups': Array contendo os grupos da instância. Ex.: [ 'Comments', 'Session', 'Users' ]
 * - 'prevent_clear': Array contendo os grupos que são ignorados quando o método clear(false)
 *
 * Após inicializado, poderemos utilizar todas suas funcionalidades:
 * #### Atenção!
 * Todas as funções tem como padrão a instância 'default'. Caso seja omitido este parâmetro, a instância utilizada será a 'default'. Você pode
 * especificar o parametro para utilizar outra instância.
 *
 * #### set(key, value [, instance = 'default')
 * Salva uma chave e seu valor no cache. Retorna ```(boolean)``` com true quando foi salvo com sucesso, e false quando não foi salvo com sucesso.
 * ```php
 * 		Cache::set('chave', 'teste de valor', 'default');
 * ```
 *
 * #### get(key [, instance = 'default')
 * Retorna o valor de uma chave do cache. Retorna ```(boolean) false``` caso não seja encontrado nada.
 * ```php
 * 		Cache::get('chave', 'default');
 * ```
 *
 * #### delete(key [, instance = 'default')
 * Apaga o valor de uma chave do cache. Retorna ```(boolean)``` com true caso delete, e false caso não delete.
 * ```php
 * 		Cache::delete('chave', 'default');
 * ```
 *
 * #### remember(key, callable [, instance = 'default')
 * Pesquisa o valor da chave requisitado, caso o mesmo não exista, executa a função ```callable``` e salva na chave requisitada (e retorna) seu retorno.
 * ```php
 * 		Cache::remember('chave', function() {
 *	 		// Inclua sua lógica aqui
 *	 		return $retorno;
 * 		}, 'default');
 * ```
 *
 * #### clearGroup(group [, instance = 'default')
 * Invalida todas as chaves de um grupo determinado na instancia escolhida
 * ```php
 * 		Cache::clearGroup('Grupo', 'default');
 * ```
 *
 * #### clear([ ignore_prevents = false [, instance = 'default')
 * Apaga todas as entradas de cache da instancia. Caso ignore_prevents seja setado como ```true```, ignorará até os grupos em 'prevent_clear'.
 * ```php
 * 		Cache::clear(false, 'default');
 * ```
 *
 * #### clearNamespace(namespace)
 * Invoca o método clear() de todas as instâncias sob o namespace escolhido
 * ```php
 * 		Cache::clearNamespace('Namespace');
 * ```
 *
 * ### Funcionamento dos Grupos de Cache
 * ---------
 * A funcionalidade de grupos de Cache no ZLX\Cache é interessante para invalidar/resetar apenas entradas de cache em um grupo específico.
 * Suponhamos que em determinado momento da sua lógica, você cria uma entrada de Cache sob o grupo 'Posts', para guardar os dados de uma postagem
 * específica:
 * ```php
 * 		Cache::set('Posts.data.'.$id_post, [ 'title' => 'Meus dados do post', 'body' => 'Corpo do meu post' ]);
 * ```
 * Ao utilizar o padrão 'Grupo.chave' para salvar, deletar, ou retornar uma chave (***set()***, ***get()***, ***delete()***, ***remember()***), caso
 * o grupo esteja no array de grupos da sua instância, ele será salvo sob este grupo.
 *
 * Ao invocar o método ***clearGroup()***, invalidamos todas as chaves sob este grupo, de forma que na próxima requisição de qualquer chave deste
 * grupo, ela não será encontrada no cache, e portanto será renovada!
 *
 * ### Funcionamento dos Namespaces
 * ---------
 * É muito parecido com o funcionamento de grupos, porém funciona num escopo acima dos grupos. Os grupos pertencem à instancia, e as instâncias pertencem aos
 * namespaces.
 * Quando o método ***clearNamespace()*** for invocado, ele irá executar um clear (***ignorando os prevents***) em todas as instâncias pertencentes ao
 * namespace a ser resetado.
 *
 * ### Criando e utilizando uma Engine personalizada
 * ---------
 * O ZLX\Cache permite que sejam criadas Engines personalizadas para que possa-se desenvolver as lógicas necessárias do programador
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
 */
class Cache {
	/**
	 * Array contendo as instâncias diferentes de cache criadas
	 *
	 * @var array
	 */
	protected static $_instances = [];

	/**
	 * Engines disponíveis para utilização com o ZLX Cache
	 *
	 * @var array
	 */
	protected static $_engines = [
		'memcached' =>	'MemcachedEngine',
		'memcache' => 'MemcacheEngine',
		'redis' => 'RedisEngine'
	];

	/**
	 * Configurações padrões para a classe ZLX Cache
	 *
	 * @var array
	 */
	protected static $_configs = [
		'prefix' => 'default_zlx',
		'instances' => [
			'default' => [
				'engine' => 'memcached',
				'prefix' => 'memcached_default',
				'namespaces' => []
			]
		]
	];

	/**
	 * Array que armazena os namespaces e as instancias de cache pertencentes aos mesmos
	 *
	 * @var array
	 */
	protected static $namespaces = [];

	/**
	 * Boolean que determina se o ZLX Cache está ligado, ou desligado.
	 * Caso o mesmo esteja desligado, será utilizada a NullEngine, que não utiliza nenhum cache.
	 *
	 * @var boolean
	 */
	protected static $_enabled = true;

	/**
	 * Número de threads máximo para execução simultanea de locks de execução
	 *
	 * @var int
	 */
	protected static $number_of_threads = 1;

	/**
	 * Função inicializadora do ZLX Cache.
	 *
	 * @param	array	$cache_config		Configurações do ZLX Cache (Veja modo de uso para mais informações)
	 *
	 * @return void
	 */
	public static function init(array $cache_config = []) {
		// Fazemos um merge entre as configurações padrões e as configurações enviadas à classe
		self::$_configs = array_merge(self::$_configs, $cache_config);

		try {
			 // Criamos a instância de NullEngine para quando estivermos com cache desabilitado
			self::$_instances['_zlx_null_engine_'] = new \ZLX\Cache\Engine\NullEngine([]);

			// Iteramos entre o array de instâncias, adicionando as instâncias à classe
			foreach(self::$_configs['instances'] as $instance => $config):
				// Caso a instância criada tenha o nome reservado para NullEngine, ela será ignorada
				if($instance == '_zlx_null_engine_') continue;

				// Construímos a instancia
				self::_buildInstance(strtolower($instance), $config);
			endforeach;
		} catch(\Exception $e) {
			self::_throwError($e->getMessage());
		}
	}

	/**
	 * Função utilizada para criar uma nova instância utilizável em tempo de execução.
	 *
	 * ### Exemplo
	 * ```php
	 *     Cache::create('long_cache', [ 	'engine' => 'memcached',
	 *										'prefix' => 'long_cache',
	 * 										'duration' => '+10 hours',
	 *										'groups' => [ 'Posts', 'Comments' ] ]);
	 *
	 * ```
	 *
	 * @param	string		$instance_name		Nome da instância
	 * @param	array		$config				Configurações da instância de cache
	 *
	 * @uses ZLX\Cache\_buildInstance()
	 * @uses ZLX\Cache\_throwError()
	 *
	 * @return void
	 */
	public static function create($instance_name, array $config = []) {
		try {
			self::_buildInstance($instance_name, $config);
		} catch(\Exception $e) {
			self::_throwError($e->getMessage());
		}
	}

	/**
	 * Função que envia um erro ao PHP com a mensagem desejada
	 * Esta função envia os erros da classe ao PHP
	 *
	 * @param	string		$message		Mensagem de erro
	 *
	 * @return void
	 */
	protected static function _throwError($message) {
		if(\php_sapi_name() != 'cli')
			trigger_error('[ZLX_CACHE ERROR] '.$message, E_USER_WARNING);
	}

	/**
	 * Função que constrói uma instância de cache e a adiciona ao array de instâncias da classe.
	 * Aceita nomes de classes, ou os nomes das opções de engines built-in da classe.
	 *
	 * Atenção: Só é possível utilizar classes personalizadas desde que as mesmas sejam filhas da ZLX\Cache\CacheEngine
	 *
	 * @param	string		$cache_instance		Nome da instância
	 * @param	array		$config				Configurações da instância
	 *
	 * @throws \Exception
	 *
	 * @return void
	 */
	protected static function _buildInstance($cache_instance, array $config) {
		// Caso exista suporte à engine enviada nas configurações, então criamos a partir dela
		if(isset(self::$_engines[strtolower($config['engine'])])):
			// Adicionamos o prefixo da classe de Cache ao prefixo da instância
			$config['prefix'] = self::$_configs['prefix'].@$config['prefix'];

			// Caso hajam namespaces, iteramos entre eles e salvamos a instância atual no array deste namespace
			if(!empty($config['namespaces'])):
				foreach($config['namespaces'] as $namespace):
					self::$namespaces[$namespace][] = $cache_instance;
					self::$namespaces[$namespace] = array_unique(self::$namespaces[$namespace]);
				endforeach;
			endif;

			$class = '\ZLX\\Cache\\Engine\\'.self::$_engines[strtolower($config['engine'])];
			self::$_instances[$cache_instance] = new $class($config);
		// Senão existir suporte built-in, tentamos utilizar a classe personalizada enviada
		else:
			if(!class_exists($config['engine']))
				throw new \Exception('A Engine \''.$config['engine'].'\' não existe.');

			// Criamos uma instância da classe enviada, e checamos se ela é filha de ZLX\Cache\Engine
			$instance = new $config['engine']($config);
			if(!($instance instanceof \ZLX\Cache\Engine\Contracts\CacheEngineInterface))
				throw new \Exception('A Engine deve ser uma extensão da classe ZLX\Cache\CacheEngine.');

			// Caso seja, ela é adicionada à lista de instâncias
			self::$_instances[$cache_instance] = $instance;

			unset($instance);
		endif;
	}

	/**
	 * Função que retorna uma instância salva dentro da classe
	 *
	 * @param	string		$instance		Nome da instancia a ser retornada
	 *
	 * @return ZLX\Cache\CacheEngine
	 */
	protected static function instance($instance = 'default') {
		$instance = strtolower($instance);

		if(!isset(self::$_instances[$instance])):
			self::_throwError(sprintf('Não foi possível encontrar a instância \'%s\', portanto o cache foi desabilitado para funções desta instância.', $instance));
			return self::$_instances['_zlx_null_engine_'];
		endif;

		return self::$_enabled?self::$_instances[$instance]:self::$_instances['_zlx_null_engine_'];
	}

	/**
	 * Função estática para setar o valor de uma chave no cache
	 *
	 * @param	string		$key		Chave a ser setada no cache
	 * @param	mixed		$value		Valor a ser salvo nesta chave
	 * @param	string		$instance	Instância de Cache a utilizar
	 *
	 * @return boolean
	 */
	public static function set($key, $value, $instance = 'default') {
		$engine = self::instance($instance);

		if(is_resource($value))
			return false;

		if($value==='')
			return false;

        $engine->setStaleData($key, $value);
		$success = $engine->set($key, $value);

		if(!$success)
			self::_throwError(sprintf('Não foi possível salvar \'%s\' na instancia de \'%s\' (%s)', $key, $instance, get_class($engine)));

		return $success;
	}

	/**
	 * Função estática que retorna um valor de uma chave no cache
	 *
	 * @param	string		$key		Chave a ser retornada do cache
	 * @param	string		$instance	Instância de Cache a utilizar
	 * @param	boolean		$use_stale	Boolean que determina se usará stale cache ou não
	 *
	 * @return mixed
	 */
	public static function get($key, $instance = 'default', $use_stale = true) {
		$engine = self::instance($instance);
		$value = $engine->get($key);

		if($value === false and $use_stale === true):
			$stale = $engine->getStaleData($key);

			if($stale)
				return $stale;

			$stale_data = $engine->readLastClearedData($key);

			if($stale_data)
				$engine->setStaleData($key, $stale_data);

			return false;
		endif;

		return $value;
	}

	/**
	 * Função estática que deleta uma chave no cache
	 *
	 * @param	string		$key		Chave a ser retornada do cache
	 * @param	string		$instance	Instância de Cache a utilizar
	 *
	 * @return boolean
	 */
	public static function delete($key, $instance = 'default') {
		$engine = self::instance($instance);

		$engine->deleteStaleData($key);
		$success = $engine->delete($key);

		return $success;
	}

	/**
	 * Função estática que deleta todas as entradas do cache de uma instância
	 *
	 * @param	boolean		$ignore_prevents	Boolean que determina se o clear ignorará grupos no array de Prevent ou não
	 * @param	string		$instance			Instância de Cache a utilizar
	 *
	 * @return void
	 */
	public static function clear($ignore_prevents = false, $instance = 'default') {
		$engine = self::instance($instance);

		$engine->clear($ignore_prevents);
	}

	/**
	 * Função estática que pesquisa uma chave no cache, e caso não haja valor, cria a chave com o valor do método enviado
	 *
	 * @param	string		$key		Chave a ser retornada do cache
	 * @param	function	$callable	Método que retorna o valor da chave a ser setado, caso a mesma não exista
	 * @param	string		$instance	Instância de Cache a utilizar
     * @param   bool        $use_stable Use stale or not
	 */
	public static function remember($key, $callable, $instance = 'default', $use_stale = true) {
        $existing = self::get($key, $instance, $use_stale);

        if ($existing !== false)
            return $existing;

		$lock_key = $key;
		$lock_acquired = self::acquire_lock($lock_key, 5, $instance);

		$max_tries = 30;
		$tries = 0;

		while(!$lock_acquired and $tries < $max_tries):
			$lock_acquired = self::acquire_lock($lock_key, 5, $instance);

			$tries++;
			usleep(100000);
		endwhile;

        if ($lock_acquired === false) {
            $engine = self::instance($instance);
            $existing = $engine->get($key);

            if ($existing !== false) {
                return $existing;
            }
        }

        $results = call_user_func($callable);
        self::set($key, $results, $instance);

		if($lock_acquired !== false) {
			self::release_lock($lock_acquired, $lock_key, $instance);
        }

        return $results;
	}

	/**
	 * Função de ADD no cache
	 *
	 * @param	string		$key		Chave do cache
	 * @param	mixed		$value		Valor a adicionar a chave
	 * @param	string		$instance	Instancia do cache
	 * @param	int			$ttl		Tempo de vida do cache, em segundos
	 *
	 * @return boolean
	 */
	public static function add($key, $value, $instance = 'default', $ttl = 5) {
		$engine = self::instance($instance);

		return $engine->add($key, $value, $ttl);
	}

	/**
	 * Tenta adquirir um lock de execução para escrita no cache para uma chave única
	 *
	 * @param	string		$key			Chave em que será feito o lock
	 * @param	int			$ttl			Tempo de vida do lock, em segudos
	 * @param	string		$instance		Config do cache a ser utilizada
	 *
	 * @return boolean
	 */
	protected static function acquire_lock($key, $ttl = 5, $instance = 'default') {
		for($thread = 1; $thread <= self::$number_of_threads; $thread++)
			if(self::add($key.'__lock_thread_'.$thread.'__', 1, $instance, $ttl))
				return (int)$thread;

		return false;

		return ;
	}

	/**
	 * Solta o lock de execução de escrita no cache para uma chave única
	 *
	 * @param	int			$thread		Número da thread de lock
	 * @param	string		$key		Chave em que será feito o lock
	 * @param	string		$instance		Config do cache a ser utilizada
	 *
	 * @return boolean
	 */
	protected static function release_lock($thread, $key, $instance = 'default') {
		return self::delete($key.'__lock_thread_'.$thread.'__', $instance);
	}


	/**
	 * Função estática que invoca a função clear() de todas as instâncias contidas no namespace enviado
	 *
	 * @param	string		$namespace		Namespace a ter o cache resetado
	 *
	 * @return void
	 */
	public static function clearNamespace($namespace) {
		if(isset(self::$namespaces[$namespace]) and !empty(self::$namespaces[$namespace])):
			foreach(self::$namespaces[$namespace] as $instance):
				$engine = self::instance($instance);

				$engine->clear();
			endforeach;
		endif;
	}

	/**
	 * Função estática que invoca a clearGroup da instância, invalidando o valor das entradas daquele grupo naquela instância de cache
	 *
	 * @param	string		$group		Nome do grupo a ser resetado
	 * @param	string		$instance	Nome da instância de cache
	 *
	 * @return boolean
	 */
	public static function clearGroup($group, $instance = 'default') {
		$engine = self::instance($instance);

		return $engine->clearGroup($group);
	}

	/**
	 * Liga as funções de Cache
	 *
	 * @return void
	 */
	public static function enable() {
		self::$_enabled = true;
	}

	/**
	 * Desliga as funções de Cache
	 *
	 * @return void
	 */
	public static function disable() {
		self::$_enabled = false;
	}
}
