# Lychee Container

## 安装

```bash
composer require lychee/container:^0.1.0
```

## 简单使用

实例化容器：

```PHP
<?php

use Lychee\Container;

$container = new Container;
```

- 绑定类到容器

```PHP
class Foo
{
    public function sayHello()
    {
        echo 'Hello from Foo' . PHP_EOL;
    }
}

// 直接绑定实例
$container->bind('foo', new Foo());
// 也可以传递类的名字
$container->bind('foo', Foo::class);
// 还可以通过返回值是类实例的闭包函数
$container->bind('foo', function () {
    return new Foo;
});
```

- 注册单例到容器

```PHP
$container->singleton('foo', new Foo());
// 参数跟 bind() 都是一样的，下面不再赘述
```

- 从容器取出实例

```PHP
$foo = $container->make('foo');
$foo->sayHello();
```

## 进阶用法

容器还可以帮你管理依赖，在获取实例时自动从容器中搜寻已注册的依赖，自动注入到构造方法中：

```PHP
class Foo
{
    private $bar;

    public function __construct(Bar $bar)
    {
        $this->bar = $bar;
    }

    public function sayHello()
    {
        echo 'Hello, I\'m ' . $this->bar->getName() . PHP_EOL;
    }
}

class Bar
{
    public function getName(): string
    {
        return 'Y!an';
    }
}

$container->bind('bar', new Bar());
$container->bind('foo', Foo::class);

$foo = $container->make('foo');
$foo->sayHello(); // Hello, I'm Y!an
```
