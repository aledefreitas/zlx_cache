# ZLX Cache
===============
ZLX Cache v1.0.0 - 11 de Janeiro de 2016

por Alexandre de Freitas Caetano
http://github.com/aledefreitas/

baseado em CakePHP Cache
http://github.com/cakephp/cakephp/


### Introdução
---------
A classe ZLX\Cache traz uma forma elegante de utilizar cache na aplicação, facilitando a implementação de sistemas de Cache em sua aplicação.
Com esta classe é possível criar várias instâncias separadas de Cache para sua aplicação, sem forçar que sua aplicação se restrinja a apenas uma implementação de cache.
Você poderá alterar ou criar engines de cache com sua própria lógica, sobre-escrevendo a classe CacheEngine.
Também pode apagar dados de várias instâncias de uma só vez, através dos namespaces atribuídos às instâncias!


### Instalação
--------- 
É possível instalar o ZLX\Cache através do Composer. Adicione no seu ```composer.json```:
```
{
   "repositories": [
       {
           "url": "https://github.com/aledefreitas/zlx_cache.git",
           "type": "git"
       }
   ],
   "require": {
       "aledefreitas/zlx_cache": "*"
   }
}
```

Ou então você pode baixar a release que quiser no [nosso repositório](https://github.com/aledefreitas/zlx_cache/releases), e fazer um require no inicio de seu código:
```php
		require_once("path/to/zlx/cache/Cache.php");
```

### Requerimentos
---------
 - ***PHP 5.6+***
 - ***Memcached***

### Modo de uso
---------
Abaixo citaremos as funções que são utilizadas no gerenciamento do cache na sua aplicação.
Primeiramente, iniciamos o Cache, no início de seu código de inicialização da aplicação:
```php
 	use ZLX\Cache\Cache;
	$config = [ 'prefix' => 'cache_prefix',
				'instances' => [
					// Utilizamos default, pois é a configuração padrão do cache
					'default' => [	'engine' => 'memcached',
									'duration' => '+10 minutes',
									'groups' => [ 'Posts', 'Comments', 'Session' ],
									'prevent_clear' => [ 'Session' ] ]
				]
			];
	// Iniciamos o cache
 	Cache::init($config);
```
#### Atenção!
Tenha muita atenção ao setar a configuração 'prefix' na inicialização do Cache. Ela é o prefixo para as entradas de cache de seu site,
e não deve ser duplicada com nenhum outro prefixo igual em qualquer outro site no servidor inteiro.

#### Criando instâncias on-the-fly (em tempo de execução)
Podemos criar instancias novas programáticamente em tempo de execução com a função ```Cache::create()```:
```php
 	use ZLX\Cache\Cache;
	$config = [	'engine' => 'memcached',
				'duration' => '+10 minutes',
				'groups' => [ 'Posts', 'Comments', 'Session' ],
				'prevent_clear' => [ 'Session' ] 
			];
			
 	Cache::create('meu_cache', $config);
```
#### Atributos das instâncias:
- 'engine': É o Engine a ser utilizado. Ex.: 'memcached'
- 'duration': Duração das chaves de cache. Ex.: '+40 minutes'
- 'prefix': Prefixo da instância. Ex.: 'prefixo_zlx'
- 'namespaces': Array contendo os namespaces ao qual a instância pertence. Ex.: [ 'Posts', 'Admin' ]
- 'groups': Array contendo os grupos da instância. Ex.: [ 'Comments', 'Session', 'Users' ]
- 'prevent_clear': Array contendo os grupos que são ignorados quando o método clear(false)

Após inicializado, poderemos utilizar todas suas funcionalidades:
#### Atenção!
Todas as funções tem como padrão a instância 'default'. Caso seja omitido este parâmetro, a instância utilizada será a 'default'. Você pode
especificar o parametro para utilizar outra instância.

#### set(key, value [, instance = 'default')
Salva uma chave e seu valor no cache. Retorna ```(boolean)``` com true quando foi salvo com sucesso, e false quando não foi salvo com sucesso.
```php
		Cache::set('chave', 'teste de valor', 'default');
```

#### get(key [, instance = 'default')
Retorna o valor de uma chave do cache. Retorna ```(boolean) false``` caso não seja encontrado nada.
```php
		Cache::get('chave', 'default');
```

#### delete(key [, instance = 'default')
Apaga o valor de uma chave do cache. Retorna ```(boolean)``` com true caso delete, e false caso não delete.
```php
		Cache::delete('chave', 'default');
```

#### remember(key, callable [, instance = 'default')
Pesquisa o valor da chave requisitado, caso o mesmo não exista, executa a função ```callable``` e salva na chave requisitada (e retorna) seu retorno.
```php
		Cache::remember('chave', function() {
 		// Inclua sua lógica aqui
 		return $retorno;
		}, 'default');
```

#### clearGroup(group [, instance = 'default')
Invalida todas as chaves de um grupo determinado na instancia escolhida
```php
		Cache::clearGroup('Grupo', 'default');
```

#### clear([ ignore_prevents = false [, instance = 'default')
Apaga todas as entradas de cache da instancia. Caso ignore_prevents seja setado como ```true```, ignorará até os grupos em 'prevent_clear'.
```php
		Cache::clear(false, 'default');
```

#### clearNamespace(namespace)
Invoca o método clear() de todas as instâncias sob o namespace escolhido
```php
		Cache::clearNamespace('Namespace');
```

### Funcionamento dos Grupos de Cache
---------
A funcionalidade de grupos de Cache no ZLX\Cache é interessante para invalidar/resetar apenas entradas de cache em um grupo específico.
Suponhamos que em determinado momento da sua lógica, você cria uma entrada de Cache sob o grupo 'Posts', para guardar os dados de uma postagem
específica:
```php
		Cache::set("Posts.data.".$id_post, [ "title" => "Meus dados do post", "body" => "Corpo do meu post" ]);
```
Ao utilizar o padrão 'Grupo.chave' para salvar, deletar, ou retornar uma chave (***set()***,**get()***,**delete()***,**remember()***), caso
o grupo esteja no array de grupos da sua instância, ele será salvo sob este grupo.

Ao invocar o método**clearGroup()***, invalidamos todas as chaves sob este grupo, de forma que na próxima requisição de qualquer chave deste
grupo, ela não será encontrada no cache, e portanto será renovada!

### Funcionamento dos Namespaces
---------
É muito parecido com o funcionamento de grupos, porém funciona num escopo acima dos grupos. Os grupos pertencem à instancia, e as instâncias pertencem aos 
namespaces. 
Quando o método**clearNamespace()*** for invocado, ele irá executar um clear (***ignorando os prevents***) em todas as instâncias pertencentes ao
namespace a ser resetado.

### Criando e utilizando uma Engine personalizada
---------
O ZLX\Cache permite que sejam criadas Engines personalizadas para que possa-se desenvolver as lógicas necessárias do programador
através de abstração da classe ZLX\Cache\CacheEngine!

Abaixo segue o exemplo de uma classe personalizada:
```php
use ZLX\Cache\CacheEngine;

	class CustomCacheEngine extends CacheEngine {
	public $_defaultConfigs; // Configurações padrões
		 
	public function __construct(array $config) {
		// Lógica do método construtor

		$this->_configs = array_merge($this->_defaultConfigs, $config); // Merge das configurações padrões. É necessário caso haja configurações padrões.
		parent::__construct($config);	
	}
	
	public function set($key, $value) {
		// Lógica de salvamento de valores no cache	
	}
	
	public function get($key) {
		// Lógica de busca de valores no cache	
	}
	
	public function delete($key) {
		// Lógica de apagamento de valor no cache	
	}
	
	public function clear($ignore_prevents) {
		// Lógica para reset do cache	
	}
}
```

Então, será possível adicionar instâncias desta classe de duas maneiras diferentes:
```php
	// Através da inicialização do Cache
 	use ZLX\Cache\Cache;
	$config = [ 'prefix' => 'cache_prefix',
				'instances' => [
					'meu_cache' => [	'engine' => 'CustomCacheEngine',
										'duration' => '+10 minutes',
										'groups' => [ 'Posts', 'Comments', 'Session' ],
										'prevent_clear' => [ 'Session' ] ]
				]
			];
			
 	Cache::init($config);
```


```php
	// Através da inicialização do Cache
 	use ZLX\Cache\Cache;
	$config = [	'engine' => 'CustomCacheEngine',
				'duration' => '+10 minutes',
				'groups' => [ 'Posts', 'Comments', 'Session' ],
				'prevent_clear' => [ 'Session' ] 
			];
			
 	Cache::create('meu_cache', $config);
```

Desta forma, você pode criar engines que utilizam a lógica que for necessária implementada.
