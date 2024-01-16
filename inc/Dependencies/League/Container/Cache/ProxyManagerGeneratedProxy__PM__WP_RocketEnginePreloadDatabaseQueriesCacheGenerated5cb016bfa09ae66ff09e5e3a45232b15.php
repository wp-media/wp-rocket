<?php

namespace ProxyManagerGeneratedProxy\__PM__\WP_Rocket\Engine\Preload\Database\Queries\Cache;

class Generated5cb016bfa09ae66ff09e5e3a45232b15 extends \WP_Rocket\Engine\Preload\Database\Queries\Cache implements \ProxyManager\Proxy\VirtualProxyInterface
{
    /**
     * @var \WP_Rocket\Engine\Preload\Database\Queries\Cache|null wrapped object, if the proxy is initialized
     */
    private $valueHolder5c912 = null;

    /**
     * @var \Closure|null initializer responsible for generating the wrapped object
     */
    private $initializerb4055 = null;

    /**
     * @var bool[] map of public properties of the parent class
     */
    private static $publicProperties3f2b9 = [
        'query_vars' => true,
        'items' => true,
    ];

    private static $signature5cb016bfa09ae66ff09e5e3a45232b15 = 'YTo0OntzOjk6ImNsYXNzTmFtZSI7czo0NzoiV1BfUm9ja2V0XEVuZ2luZVxQcmVsb2FkXERhdGFiYXNlXFF1ZXJpZXNcQ2FjaGUiO3M6NzoiZmFjdG9yeSI7czo1MDoiUHJveHlNYW5hZ2VyXEZhY3RvcnlcTGF6eUxvYWRpbmdWYWx1ZUhvbGRlckZhY3RvcnkiO3M6MTk6InByb3h5TWFuYWdlclZlcnNpb24iO3M6NDg6InYxLjAuMTZAZWNhZGJkYzkwNTJlNGFkMDhjNjBjOGEwMjI2ODcxMmU1MDQyN2Y3YyI7czoxMjoicHJveHlPcHRpb25zIjthOjA6e319';

    public function create_or_update(array $resource)
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'create_or_update', array('resource' => $resource), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->create_or_update($resource);
    }

    public function create_or_nothing(array $resource)
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'create_or_nothing', array('resource' => $resource), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->create_or_nothing($resource);
    }

    public function get_rows_by_url(string $url)
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'get_rows_by_url', array('url' => $url), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->get_rows_by_url($url);
    }

    public function delete_by_url(string $url)
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'delete_by_url', array('url' => $url), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->delete_by_url($url);
    }

    public function get_old_cache(float $delay = 1, string $unit = 'month') : array
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'get_old_cache', array('delay' => $delay, 'unit' => $unit), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->get_old_cache($delay, $unit);
    }

    public function remove_all_not_accessed_rows(float $delay = 1, string $unit = 'month')
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'remove_all_not_accessed_rows', array('delay' => $delay, 'unit' => $unit), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->remove_all_not_accessed_rows($delay, $unit);
    }

    public function get_pending_jobs(int $total = 45)
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'get_pending_jobs', array('total' => $total), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->get_pending_jobs($total);
    }

    public function make_status_inprogress(int $id)
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'make_status_inprogress', array('id' => $id), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->make_status_inprogress($id);
    }

    public function make_status_complete(string $url)
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'make_status_complete', array('url' => $url), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->make_status_complete($url);
    }

    public function has_pending_jobs()
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'has_pending_jobs', array(), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->has_pending_jobs();
    }

    public function revert_in_progress()
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'revert_in_progress', array(), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->revert_in_progress();
    }

    public function revert_old_in_progress()
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'revert_old_in_progress', array(), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->revert_old_in_progress();
    }

    public function revert_old_failed()
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'revert_old_failed', array(), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->revert_old_failed();
    }

    public function set_all_to_pending()
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'set_all_to_pending', array(), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->set_all_to_pending();
    }

    public function is_preloaded(string $url) : bool
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'is_preloaded', array('url' => $url), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->is_preloaded($url);
    }

    public function is_pending(string $url) : bool
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'is_pending', array('url' => $url), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->is_pending($url);
    }

    public function remove_all()
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'remove_all', array(), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->remove_all();
    }

    public function lock(string $url)
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'lock', array('url' => $url), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->lock($url);
    }

    public function unlock_all()
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'unlock_all', array(), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->unlock_all();
    }

    public function unlock(string $url)
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'unlock', array('url' => $url), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->unlock($url);
    }

    public function make_status_failed(int $id)
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'make_status_failed', array('id' => $id), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->make_status_failed($id);
    }

    public function update_last_access(int $id)
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'update_last_access', array('id' => $id), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->update_last_access($id);
    }

    public function get_outdated_in_progress_jobs(int $delay = 3)
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'get_outdated_in_progress_jobs', array('delay' => $delay), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->get_outdated_in_progress_jobs($delay);
    }

    public function query($query = [], bool $use_cache = true)
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'query', array('query' => $query, 'use_cache' => $use_cache), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->query($query, $use_cache);
    }

    public function set_query_var($key = '', $value = '')
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'set_query_var', array('key' => $key, 'value' => $value), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->set_query_var($key, $value);
    }

    public function is_query_var_default($key = '')
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'is_query_var_default', array('key' => $key), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->is_query_var_default($key);
    }

    public function get_item($item_id = 0)
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'get_item', array('item_id' => $item_id), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->get_item($item_id);
    }

    public function get_item_by($column_name = '', $column_value = '')
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'get_item_by', array('column_name' => $column_name, 'column_value' => $column_value), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->get_item_by($column_name, $column_value);
    }

    public function add_item($data = [])
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'add_item', array('data' => $data), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->add_item($data);
    }

    public function copy_item($item_id = 0, $data = [])
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'copy_item', array('item_id' => $item_id, 'data' => $data), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->copy_item($item_id, $data);
    }

    public function update_item($item_id = 0, $data = [])
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'update_item', array('item_id' => $item_id, 'data' => $data), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->update_item($item_id, $data);
    }

    public function delete_item($item_id = 0)
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'delete_item', array('item_id' => $item_id), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->delete_item($item_id);
    }

    public function filter_item($item = [])
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'filter_item', array('item' => $item), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->filter_item($item);
    }

    public function get_results($cols = [], $where_cols = [], $limit = 25, $offset = null, $output = 'OBJECT')
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'get_results', array('cols' => $cols, 'where_cols' => $where_cols, 'limit' => $limit, 'offset' => $offset, 'output' => $output), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->get_results($cols, $where_cols, $limit, $offset, $output);
    }

    public function to_array()
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'to_array', array(), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->to_array();
    }

    /**
     * Constructor for lazy initialization
     *
     * @param \Closure|null $initializer
     */
    public static function staticProxyConstructor($initializer)
    {
        static $reflection;

        $reflection = $reflection ?? new \ReflectionClass(__CLASS__);
        $instance   = $reflection->newInstanceWithoutConstructor();

        unset($instance->query_vars, $instance->items, $instance->logger, $instance->table_name, $instance->table_alias, $instance->table_schema, $instance->item_name, $instance->item_name_plural, $instance->item_shape, $instance->cache_group, $instance->last_changed, $instance->columns, $instance->query_clauses, $instance->request_clauses, $instance->meta_query, $instance->date_query, $instance->compare_query, $instance->query_var_originals, $instance->query_var_defaults, $instance->query_var_default_value, $instance->found_items, $instance->max_num_pages, $instance->request, $instance->db_global, $instance->prefix, $instance->last_error);

        $instance->initializerb4055 = $initializer;

        return $instance;
    }

    public function __construct(\WP_Rocket\Logger\Logger $logger, $query = [])
    {
        static $reflection;

        if (! $this->valueHolder5c912) {
            $reflection = $reflection ?? new \ReflectionClass('WP_Rocket\\Engine\\Preload\\Database\\Queries\\Cache');
            $this->valueHolder5c912 = $reflection->newInstanceWithoutConstructor();
        unset($this->query_vars, $this->items, $this->logger, $this->table_name, $this->table_alias, $this->table_schema, $this->item_name, $this->item_name_plural, $this->item_shape, $this->cache_group, $this->last_changed, $this->columns, $this->query_clauses, $this->request_clauses, $this->meta_query, $this->date_query, $this->compare_query, $this->query_var_originals, $this->query_var_defaults, $this->query_var_default_value, $this->found_items, $this->max_num_pages, $this->request, $this->db_global, $this->prefix, $this->last_error);

        }

        $this->valueHolder5c912->__construct($logger, $query);
    }

    public function __get($name)
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, '__get', ['name' => $name], $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        if (isset(self::$publicProperties3f2b9[$name])) {
            return $this->valueHolder5c912->$name;
        }

        return $this->valueHolder5c912->__get($name);
    }

    public function __set($name, $value)
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, '__set', array('name' => $name, 'value' => $value), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        if (isset(self::$publicProperties3f2b9[$name])) {
            return ($this->valueHolder5c912->$name = $value);
        }

        $realInstanceReflection = new \ReflectionClass('WP_Rocket\\Engine\\Preload\\Database\\Queries\\Cache');

        if (! $realInstanceReflection->hasProperty($name)) {
            $targetObject = $this->valueHolder5c912;

            $targetObject->$name = $value;

            return $targetObject->$name;
        }

        $targetObject = $this->valueHolder5c912;
        $accessor = function & () use ($targetObject, $name, $value) {
            $targetObject->$name = $value;

            return $targetObject->$name;
        };
        $backtrace = debug_backtrace(true, 2);
        $scopeObject = isset($backtrace[1]['object']) ? $backtrace[1]['object'] : new \ProxyManager\Stub\EmptyClassStub();
        $accessor = $accessor->bindTo($scopeObject, get_class($scopeObject));
        $returnValue = & $accessor();

        return $returnValue;
    }

    public function __isset($name)
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, '__isset', array('name' => $name), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        if (isset(self::$publicProperties3f2b9[$name])) {
            return isset($this->valueHolder5c912->$name);
        }

        $realInstanceReflection = new \ReflectionClass('WP_Rocket\\Engine\\Preload\\Database\\Queries\\Cache');

        if (! $realInstanceReflection->hasProperty($name)) {
            $targetObject = $this->valueHolder5c912;

            return isset($targetObject->$name);
        }

        $targetObject = $this->valueHolder5c912;
        $accessor = function () use ($targetObject, $name) {
            return isset($targetObject->$name);
        };
        $backtrace = debug_backtrace(true, 2);
        $scopeObject = isset($backtrace[1]['object']) ? $backtrace[1]['object'] : new \ProxyManager\Stub\EmptyClassStub();
        $accessor = $accessor->bindTo($scopeObject, get_class($scopeObject));
        $returnValue = $accessor();

        return $returnValue;
    }

    public function __unset($name)
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, '__unset', array('name' => $name), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        if (isset(self::$publicProperties3f2b9[$name])) {
            unset($this->valueHolder5c912->$name);

            return;
        }

        $realInstanceReflection = new \ReflectionClass('WP_Rocket\\Engine\\Preload\\Database\\Queries\\Cache');

        if (! $realInstanceReflection->hasProperty($name)) {
            $targetObject = $this->valueHolder5c912;

            unset($targetObject->$name);

            return;
        }

        $targetObject = $this->valueHolder5c912;
        $accessor = function () use ($targetObject, $name) {
            unset($targetObject->$name);

            return;
        };
        $backtrace = debug_backtrace(true, 2);
        $scopeObject = isset($backtrace[1]['object']) ? $backtrace[1]['object'] : new \ProxyManager\Stub\EmptyClassStub();
        $accessor = $accessor->bindTo($scopeObject, get_class($scopeObject));
        $accessor();
    }

    public function __clone()
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, '__clone', array(), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        $this->valueHolder5c912 = clone $this->valueHolder5c912;
    }

    public function __sleep()
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, '__sleep', array(), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return array('valueHolder5c912');
    }

    public function __wakeup()
    {
        unset($this->query_vars, $this->items, $this->logger, $this->table_name, $this->table_alias, $this->table_schema, $this->item_name, $this->item_name_plural, $this->item_shape, $this->cache_group, $this->last_changed, $this->columns, $this->query_clauses, $this->request_clauses, $this->meta_query, $this->date_query, $this->compare_query, $this->query_var_originals, $this->query_var_defaults, $this->query_var_default_value, $this->found_items, $this->max_num_pages, $this->request, $this->db_global, $this->prefix, $this->last_error);
    }

    public function setProxyInitializer(\Closure $initializer = null) : void
    {
        $this->initializerb4055 = $initializer;
    }

    public function getProxyInitializer() : ?\Closure
    {
        return $this->initializerb4055;
    }

    public function initializeProxy() : bool
    {
        return $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'initializeProxy', array(), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;
    }

    public function isProxyInitialized() : bool
    {
        return null !== $this->valueHolder5c912;
    }

    public function getWrappedValueHolderValue()
    {
        return $this->valueHolder5c912;
    }
}
