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
 */

use PHPUnit\Framework\TestCase;
use ZLX\Cache\Cache;

final class RedisTest extends TestCase
{
    public function testCanStartCache()
    {
        try {
            $config = [
        		'prefix' => 'cache_prefix',
                'instances' => [
                    'default' => [
                        'engine' => 'redis',
    					'prefix' => 'prefixo_default',
                        'database' => 0,
                        'duration' => '+10 minutes',
                        'groups' => [ 'Posts', 'Comments', 'Session' ],
    					'namespaces' => [ 'Posts' ],
                        'prevent_clear' => [ 'Session' ]
                    ]
                ]
            ];

        	Cache::init($config);
            Cache::clear(true);

            $this->assertNull(null);
        } catch(\Exception $e) {
            $this->fail(sprintf('Failed starting cache with Redis: %s', $e->getMessage()));
        }
    }

    public function testCanUseDifferentDatabase()
    {
        try {
            $config = [
                'engine' => 'redis',
                'prefix' => 'prefixo_default',
                'database' => 1,
                'duration' => '+10 minutes',
                'groups' => [ 'Posts', 'Comments', 'Session' ],
                'namespaces' => [ 'Posts' ],
                'prevent_clear' => [ 'Session' ]
            ];

            Cache::create('another_database', $config);
            Cache::set('Posts.post_another_database', 'Post 1 value', 'another_database');

            $this->assertFalse(Cache::get('Posts.post_another_database'));
            $this->assertEquals(
                'Post 1 value',
                Cache::get('Posts.post_another_database', 'another_database')
            );
        } catch(\Exception $e) {
            $this->fail(sprintf('Failed creating cache instance: %s', $e->getMessage()));
        }
    }

    public function testCanCreateDynamicInstances()
    {
        try {
            $config = [
                'engine' => 'redis',
                'prefix' => 'prefix_meu_cache_',
                'database' => 0,
                'duration' => '+10 minutes',
                'groups' => [ 'Posts', 'Comments', 'Session' ],
                'namespaces' => [ 'Posts' ],
                'prevent_clear' => [ 'Session' ]
            ];

            Cache::create('meu_cache', $config);
            Cache::set('Posts.post_meu_cache', 'Post 1 value', 'meu_cache');

            $this->assertFalse(Cache::get('Posts.post_meu_cache'));
            $this->assertEquals(
                'Post 1 value',
                Cache::get('Posts.post_meu_cache', 'meu_cache')
            );
        } catch(\Exception $e) {
            $this->fail(sprintf('Failed creating cache instance: %s', $e->getMessage()));
        }
    }

    public function testCacheIsSaved()
    {
        $value = 'Teste de cache';
        Cache::set('test_key', $value);

        $this->assertEquals(
            $value,
            Cache::get('test_key')
        );
    }

    public function testUsesStaleCache()
    {
        $value = 'Post 1 no cache Default';
        Cache::set('Posts.post_1', $value, 'default');
        Cache::clearGroup('Posts');
        // Sets the stale but returns false
        Cache::get('Posts.post_1');

        $this->assertEquals(
            false,
            Cache::get('Posts.post_1')
        );
    }

    public function testCantAddTwice()
    {
        Cache::add('test_add_key', 1);

        $this->assertEquals(
            false,
            Cache::add('test_add_key', 1)
        );
    }

    public function testCanDisableStaleCache()
    {
        $value = 'Post 2 no cache Default sem stale cache';
        Cache::set('Posts.post_2', $value, 'default');
        Cache::clearGroup('Posts');

        $this->assertEquals(
            false,
            Cache::get('Posts.post_2', 'default', false)
        );
    }

    public function testRememberUsesCacheFirst()
    {
        $value = 'Post 3 value';

        Cache::set('Posts.post_3', $value, 'default');

        $this->assertEquals(
            $value,
            Cache::remember('Posts.post_3', function() {
                return 'Wrong value';
            })
        );
    }

    public function testRememberCreatesNewCache()
    {
        $value = 'Post 4 value';
        $result = Cache::remember('Posts.post_4', function() use($value) {
            return $value;
        });

        $this->assertEquals(
            $value,
            Cache::get('Posts.post_4')
        );
    }

    public function testRememberGeneratesNewCacheOnGroupClears()
    {
        Cache::remember('Comments.post_5', function() {
            return 'Post 5 old comments';
        });
        Cache::clearGroup('Comments');

        $this->assertEquals(
            'Post 5 new comments',
            Cache::remember('Comments.post_5', function() {
                return 'Post 5 new comments';
            })
        );
    }
}
