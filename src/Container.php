<?php

/**
 * 依赖注入容器
 *
 * @package Lychee
 */

namespace Lychee;

class Container
{
    /**
     * 单例标记，存储别名
     *
     * @var array
     */
    protected $singletons = [];

    /**
     * 类实例
     *
     * @var array
     */
    protected $instances = [];

    /**
     * 类名
     *
     * @var array
     */
    protected $binds = [];

    /**
     * 绑定类到容器中
     *
     * @param string $alias
     * @param object|string|closure $class
     * @return void
     */
    public function bind(string $alias, $class)
    {
        $this->register($alias, $class);
    }

    /**
     * 以单例形式绑定类到容器中
     *
     * @param string $alias
     * @param object|string|closure $class
     * @return void
     */
    public function singleton(string $alias, $class)
    {
        $this->register($alias, $class, true);
    }

    /**
     * 注册类到容器中
     *
     * @param string $alias
     * @param object|string|closure $class
     * @param boolean $is_singleton
     * @return void
     */
    private function register(string $alias, $class, bool $is_singleton = false)
    {
        // 标记单例
        if ($is_singleton) {
            $this->singletons[] = $alias;
        }

        // 以闭包形式传入
        if (is_callable($class)) {
            $instance = $class();
            $this->instances[$alias] = $instance;
            $this->binds[$alias] = get_class($instance);
        }

        // 以实例形式传入
        if (is_object($class) && get_class($class) != "Closure") {
            $this->instances[$alias] = $class;
            $this->binds[$alias] = get_class($class);
        }

        // 以类名传入
        if (is_string($class)) {
            $this->binds[$alias] = $class;
        }
    }

    /**
     * 取出实例
     *
     * @param string $alias
     * @param array $params 构造方法中的参数
     * @throws \Exception
     * @return object
     */
    public function make(string $alias, array $params = []):object
    {
        if (! isset($this->instances[$alias])) {
            if (! isset($this->binds[$alias])) {
                throw new \Exception('Object[' . $alias . '] not found.');
            }
            $this->instances[$alias] = $this->getNewInstance($alias, $params);
        }

        if (in_array($alias, $this->singletons)) {
            // 单例
            return $this->instances[$alias];
        }
        return clone $this->instances[$alias];
    }

    /**
     * 取得新实例
     *
     * @param string $alias
     * @param array $params
     * @return object
     * @throws \Exception
     */
    private function getNewInstance(string $alias, array $params = []):object
    {
        $class = $this->binds[$alias];

        try {
            $reflect = new \ReflectionClass($class);
        } catch (\ReflectionException $e) {
            throw new \Exception(sprintf('Class "%s" not found', $class));
        }

        $constructor = $reflect->getConstructor();
        if ($constructor) {
            $objParams = [];
            $constructParams = $constructor->getParameters();

            // 如果构造方法存在参数
            if ($constructParams) {
                foreach ($constructParams as $param) {
                    $interface = $param->getClass();

                    // 如果参数是某个类，则从容器中自动注入
                    if ($interface) {
                        $objParams[] = $this->getNewInstanceByClassName($instance->name);
                    }
                }
            }
            return $reflect->newInstanceArgs(array_merge($objParams, $params));
        }
        return $reflect->newInstance();
    }

    /**
     * 根据类名取得实例
     *
     * @param string $className
     * @return object
     * @throws \Exception
     */
    private function getNewInstanceByClassName(string $className):object
    {
        $boundClasses = array_flip($this->binds);
        if (isset($boundClasses[$className])) {
            return $this->make($boundClasses[$className]);
        }
        throw new \Exception(sprintf('Class "%s" not found', $className));
    }
}
