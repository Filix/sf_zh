#在Symfony2中创建页面

在Symfony2中创建页面只需要两步：

* 创建一个路由规则：路由定义了页面的url（如/about），并且指定了一个当进来的请求匹配这条路由时Symfony2将会执行的控制器（就是一个php方法）。
* 创建控制器： 控制器就是一个php方法，它接收进入的请求，然后把它转变成Symfony2的Response对象，并且返回给用户。

这种简单的方式是优雅的，因为这完全符合Web请求的工作原理。
Web上的每个交互都是从一个HTTP请求开始的。你的应用的主要工作就是解析这个请求，返回适当的HTTP响应。

Symfony2遵循这个原理，并且提供开发者工具和约定，以使随着用户和复杂性的增加你的应用仍旧保持良好的组织性。

##环境和前端控制器

每个Symfony应用程序都运行在一个[环境](http://symfony.com/doc/current/glossary.html#term-environment)中，
环境就是一些特定配置和载入的bundle的集合，用字符串表示。通过运行不同的环境，同一个应用可以采用不同的配置。
Symfony2自带了3个环境：dev、test和prod，你也可以创建自己的环境。

环境是很有用的，它可以让一个应用拥有一个集成了debug功能的环境或者是一个优化了执行速度的生成环境。
你也可以根据环境不同选择性的载入特定的bundle。比如，Symfony2自带了WebProfilerBundle（下面介绍），它只在开发和测试环境开启。

Symfony2自带了两个web可直接访问的前端控制器：app_dev.php提供了开发环境，app.php提供了生产环境。
所有Symfony2的web访问都通过这两个前端控制器中的一个。（测试环境通常只用在跑单元测试的时候，因此它没有一个专门的前端控制器，
控制台工具提供了可以用在任何环境的前端控制器）

当前端控制器初始化kernel（核心）时，它提供了两个参数：环境和是否运行debug模式。为了使你的应用响应的更快，
Symfony2在app/cache目录下维护了一个缓存。当debug模式开启（比如app_dev.php默认使用的），当你修改了任何代码或者配置这个缓存被清空。
当运行debug模式时，Symfony2运行的慢一些，但是你的代码改变可以直观的反应出来，而不用手动清理缓存。

###"Hello Symfony!"页面

开始创建一个典型的"Hello World!"应用。完成时，用户可以通过访问下面的url获得一个问候：

    http://localhost/app_dev.php/hello/Symfony

事实上，你可以用其他名字代替Symfony。为了创建这个页面，根据刚才说的简单的两步操作。

*教程假设您已经下载了Symfony2并且配置好了web服务器。上面的url假设localhost指向新Symfony2项目的web目录下。
这一步的详细信息可以查看你使用的web服务器的文档。下面是您可能使用的web服务器的相关文档页面：
[Apache](http://httpd.apache.org/docs/2.0/mod/mod_dir.html) ，
[Nginx](http://wiki.nginx.org/HttpCoreModule#location)*

##开始之前，创建bundle

开始之前，你需要创建一个bundle。在Symfony2中，bundle就像一个插件，你的应用的所有代码都在一个bundle中。

bundle就是一个目录，包含了特定特性的所有东西，包括php类，配置甚至样式表和js文件（查看[bundle系统](http://symfony.com/doc/current/book/page_creation.html#page-creation-bundles)）

为了创建一个AcmeHelloBundle(本章需要创建的一个演示bundle)，运行下面的命令，并按照屏幕上的指示操作（所有都使用默认值）
    
    $ php app/console generate:bundle --namespace=Acme/HelloBundle --format=yml

在背后，这个bundle的一个目录已经在src/AcmeHelloBundle下创建了。app/AppKernel.php中也自动的添加了一行，这个bundle已经在核心中注册了：

    // app/AppKernel.php
    public function registerBundles()
    {
        $bundles = array(
            ...,
            new Acme\HelloBundle\AcmeHelloBundle(),
        );
        // ...

        return $bundles;
    }

现在你已经创建了一个bundle，你可以在这个bundle中创建你的应用程序了。

##第一步，创建路由

默认的，路由配置在app/config/routing.yml文件中。和Symfony2中的其他配置一样，你也可以选择使用xml或者php代码。

如果你查看主配置文件，你会发现Symfony已经自动添加了一个条目在你创建AcmeHelloBundle的时候：

    # app/config/routing.yml
    acme_hello:
        resource: "@AcmeHelloBundle/Resources/config/routing.yml"
        prefix:   /

这个条目非常的基本，它告诉Symfony从AcmeHelloBundle的Resources/config/routing.yml文件中载入配置。
这意味着你可以直接在app/config/routing.yml中写路由，也可以在你的应用中组织路由，然后在这里导入他们。

现在bundle中的routing.yml文件已经被导入了，添加一条我们想要创建页面的路由：

    # src/Acme/HelloBundle/Resources/config/routing.yml
    hello:
        path:     /hello/{name}
        defaults: { _controller: AcmeHelloBundle:Hello:index }

路由包含两部分：path是这条路由会匹配的url；defaults数组，指定了哪个控制器会被执行。
path中{name}占位符语法是个通配符。他表示/hello/Ryan,/hello/Fabbin或者其他类似的url都匹配这条路由。
{name}占位符参数会被传递给控制器，因此你可以使用它的值问候这个人。

*路由系统还有更多的特性，可以创建更灵活和更强大的url结构。想了解更多，请阅读[路由](http://symfonycn.com/the_book/v2.4.0/routing)*
    
##第二步，创建控制器

当像/hello/Ryan这样的url被应用处理的时候，hello这条路由被匹配，AcmeHelloBundle:Hello:index控制器被框架执行。
创建页面过程中的第二步就是创建这个控制器。

AcmeHelloBundle:Hello:index是控制器的逻辑名，它映射到Acme\HelloBundle\Controller\HelloController中的一个indexAction方法。
在AcmeHelloBundle中开始创建这个文件：

    // src/Acme/HelloBundle/Controller/HelloController.php
    namespace Acme\HelloBundle\Controller;

    class HelloController
    {
    }

事实上，控制器就是一个由你创建然后Symfony执行的php方法。在这个方法中，你从请求中获取信息然后准备被请求的资源。
除了在一些高级的例子中，一般控制器的返回值总是相同的：一个Symfony2的Response对象。

创建indexAction方法，当hello这条路由被匹配时，这样方法会被调用：

    // src/Acme/HelloBundle/Controller/HelloController.php
    namespace Acme\HelloBundle\Controller;

    use Symfony\Component\HttpFoundation\Response;

    class HelloController
    {
        public function indexAction($name)
        {
            return new Response('<html><body>Hello '.$name.'!</body></html>');
        }
    }

这个控制器很简单：它创建一个新的Response对象，其中第一个参数是响应中被用到的内容（该例子中就是一个简单的HTML页面）。

祝贺你，在创建了一个路由和控制器后，你已经有了一个功能齐全的页面。如果所有东西都安装正确，你的应用会跟你说hello：

    http://localhost/app_dev.php/hello/Ryan

*你也可以通过访问下面的地址查看生成环境：*
 
    http://localhost/app.php/hello/Ryan

*如果看到错误，很可能是你需要清理你的缓存，运行下面的：*

    $ php app/console cache:clear --env=prod --no-debug


可以选择不做，但是通常都会做的就是第三步，创建模板。

*控制器是你的代码的主要接入点，并且是创建页面的关键部分。更多信息可以在[控制器](http://symfonycn.com/the_book/v2.4.0/controller)查看*


##第三步，创建模板

模板允许你将所有的展示层内容放在一个单独的文件中，并且复用页面布局的不同部分。
不在控制器中写html，而用渲染模板代替：

    // src/Acme/HelloBundle/Controller/HelloController.php
    namespace Acme\HelloBundle\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    
    class HelloController extends Controller
    {
        public function indexAction($name)
        {
            return $this->render(
                'AcmeHelloBundle:Hello:index.html.twig',
                array('name' => $name)
            );

            // render a PHP template instead
            // return $this->render(
            //     'AcmeHelloBundle:Hello:index.html.php',
            //     array('name' => $name)
            // );
        }
    }

*为了使用render()方法，你的控制器必须继承[Controller](http://api.symfony.com/2.4/Symfony/Bundle/FrameworkBundle/Controller/Controller.html)类
它提供了一些在控制器中常用到的方法。在上面的例子中，在第四行添加一个use声明，然后在第6行继承Controller就行。*

rander()方法使用提供的参数创建Response对象，渲染模板。和其他控制器一样，最后返回Response对象。

请注意，有两个例子去渲染模板。默认情况下，Symfony2提供两个模板语言：传统的php和简洁但是强大的twig。
不要恐慌，你可以自由地选择其一或二者皆选。

控制器渲染了 AcmeHelloBundle:Hello:index.html.twig模板，它使用下面的命名约定：

    BundleName:ControllerName:TemplateName

这是模板的逻辑名，它通过下面的约定映射到一个物理位置：

    /path/to/BundleName/Resources/views/ControllerName/TemplateName

在这个例子中，AcmeHelloBundle是bundle名，Hello是控制器名，index.html.twig是模板：

    {# src/Acme/HelloBundle/Resources/views/Hello/index.html.twig #}
     {% extends '::base.html.twig' %}

     {% block body %}
         Hello {{ name }}!
     {% endblock %}

一行一行浏览twig模板：

* 第二行：extends关键字定义了一个父模板。这个模板明确的定义了一个布局，它的一部分会被替换掉。
* 第四行： block关键字说明，在body块中的所有内容都会被替换掉。你会看到最后渲染body这个block块是父模板的任务。

父模板::base.html.twig，缺省了bundle名和控制器名。这意味着这个模板在这个bundle之外，在app目录中：

    {# app/Resources/views/base.html.twig #}
    <!DOCTYPE html>
    <html>
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <title>{% block title %}Welcome!{% endblock %}</title>
            {% block stylesheets %}{% endblock %}
            <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" />
        </head>
        <body>
            {% block body %}{% endblock %}
            {% block javascripts %}{% endblock %}
        </body>
    </html>

这个最基础的模板定义了html的布局并且渲染了你在index.html.twig中定义的body块。
它同时还渲染title块，你可以选择性的在index.html.twig中定义。由于你没有定义title块，它默认为：Welcome！。

模板是一种强大的渲染和组织页面方法。模板可以渲染任何东西，从html到css，或者控制器想返回的任何东西。

在处理一个请求的生命周期内，模板引擎是一个选择性的工具。
回想一下，控制器的目的就是返回Response对象，模板是一个强大的，但是有选择性的用来创建Response对象内容的工具。

###目录结构

在阅读完几段文章之后，你已经知道了Symfony2中创建和渲染页面的机制。
你也开始看到Symfony2项目是如何搭建和组织的。在本章的最后，你将会了解在哪去找或存放不同类型的文件，并了解为什么这么做。

尽管Symfony2是完全灵活的，但是每个Symfony还是有一些相同的基本和推荐的目录结构：

* app/: 这个目录包括应用的配置
* src/： 项目的所有php代码都在这个目录
* vendor/： 按照约定所有的vendor类库都放在这
* web/： 这是web根目录，包含所有公开的、可访问的文件

##web目录

web根目录是所有公开的和包括图片、样式表、js文件在内的静态文件的所在目录。所有前端控制器也在这里：


    // web/app.php
    require_once __DIR__.'/../app/bootstrap.php.cache';
    require_once __DIR__.'/../app/AppKernel.php';

    use Symfony\Component\HttpFoundation\Request;

    $kernel = new AppKernel('prod', false);
    $kernel->loadClassCache();
    $kernel->handle(Request::createFromGlobals())->send();


前端控制器文件是真实的php文件，当使用Symfony2项目时他们会被执行，它的作用是使用核心类： AppKernel启动应用。

拥有前端控制器意味着比原生php应用拥有不同和更灵活的url。当使用前端控制器时，url下面的格式：

    http://localhost/app.php/hello/Ryan

前端控制器app.php被执行，在内部url /hello/Ryan根据路由配置被路由。通过使用apache的mod_rewrites规则，你可以强制app.php被执行而无需在url中指定它：

    http://localhost/hello/Ryan

虽然前端控制器在每个请求中都是必要的，但是你很少会改动或者在意到他们。他们会在环境部分再被提及。


##app目录

如你在前端控制器中所见，AppKernel类是应用的主要入口点，它负责所有的配置。因此，它存在app目录下。

这个类必须实现两个方法，这两个方法定义了Symfony需要了解的所有内容。你甚至不需要关心这两个方法，Symfony自动使用一些明显的默认值实现了他们。

* registerBundle()： 返回应用需要的所有bundle的数组形式
* registerContainerConfiguration()： 载入应用的主要配置文件

在每日的开发中，你会经常使用app/目录去修改配置和app/config目录下的路由信息。
这个目录还包含缓存目录（app/cache），日志目录（app/logs），和应用级的资源文件，比如模板（app/Resources）。
在稍后的章节中，你会学习到这些目录的作用。

*自动载入   
当Symfony载入时，一个特殊的文件-vendor/autoload.php被引入。这个文件是被composer创建的，它会自动载入所有src目录中的文件，
已经在composer.json文件中提及的第三方类库。    
因为自动载入器，你无需关心使用include或者require。composer使用命名空间去决定它的位置，然后在你需要的时候自动载入进来。        
自动载入器被配置的可以搜索src/目录去查找所有php类。因为使用了自动载入器，类名和文件的路径必须遵循下面的形式：*

    Class Name:
        Acme\HelloBundle\Controller\HelloController
    Path:
        src/Acme/HelloBundle/Controller/HelloController.php

##src目录

简单的说，src/目录包含了所有驱动你的应用的真实代码（php代码，模板，配置文件，样式表等）。
在开发中，你绝大多数工作都是在这个目录下的一个或多个bundle中进行的。

但是bundle到底是什么呢？

###bundle系统

bundle类似其他软件中的插件，但是比他们更好。主要的区别是Symfony2中任何东西都是一个bundle，包括框架的核心功能和你的应用的代码。
bundle是Symfony2中的第一个成员。这提供你灵活的使用第三方包或者发布自己的bundle。
这使得选择在你的应用中开启那个特性和按照自己的意愿优化他们变得非常容易。

*这里你只能了解到皮毛，整个cookbook致力于bundle的组织和最佳实践*


bundle就是在一个目录中实现了某个特性的、有组织性的文件集合。
你可以创建一个BlogBundle、ForumBundle或者一个管理用户的bundle（已经有很多这样的开源bundle了）。
每个目录都包含和这个特性相关的所有内容，包括php文件、模板、样式表、js文件、测试用例和其他的东西。
这个特性的每个部分都在一个bundle中，并且每个特性都在一个bundle中。

一个应用程序是由AppKernel类中的registerBundle()方法定义的一些bundle组成的：

    // app/AppKernel.php
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\DoctrineBundle\DoctrineBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Acme\DemoBundle\AcmeDemoBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

通过registerBundle()方法，你可以全局的控制在你的应用中使用哪些bundle（包括Symfony的核心bundle）。

*一个bundle可以在任意的位置，只要它能被自动载入（通过app/autoload.php中配置的自动载入器）*

##创建一个Bundle

Symfony标准版自带了一个方便的创建功能齐全的bundle的task。当然，手动创建一个bundle也是非常容易的。

为了向你展示bundle系统是多么的简单，我们创建一个新的bundle：AcmeTestBundle，并且启用它。

*Acme部分是一个假的名字，你可以用表示你或你的组织的vendor名替换它（比如ABC公司可以使用ABCTestBundle）。*

开始创建src/Acme/TestBundle目录，并且添加一个新文件，AcmeTestBundle.php：

    // src/Acme/TestBundle/AcmeTestBundle.php
    namespace Acme\TestBundle;

    use Symfony\Component\HttpKernel\Bundle\Bundle;

    class AcmeTestBundle extends Bundle
    {
    }

*AcmeTestBundle的名字遵循标准的[Bundle命名约定](http://symfony.com/doc/current/cookbook/bundles/best_practices.html#bundles-naming-conventions)。
你可以选择缩短这个名字为TestBundle，只需要以TestBundle命名这个类（文件改名为TestBundle.php）。*

这个空的类是创建新bundle唯一需要的东西。尽管大多数情况下是空的，这个类却是很有用的，可以用来自定义bundle的行为。

既然你已经创建了bundle，在AppKernel类中启用它：

    // app/AppKernel.php
    public function registerBundles()
    {
        $bundles = array(
            ...,
            // register your bundles
            new Acme\TestBundle\AcmeTestBundle(),
        );
        // ...

        return $bundles;
    }

尽管目前它什么都没做，但是AcmeTestBundle现在已经可以被使用了。

和手动创建一样简单的是，Symfony也提供了一个命令行接口去创建基本的bundle骨架：

    $ php app/console generate:bundle --namespace=Acme/TestBundle

bundle的骨架创建了基本的控制器、模板和可自定义的路由。稍后，你将会学习到更多的Symfony2的命令行工具。

*无论自己创建新的bundle还是使用第三方的bundle，确定这个bundle在registerBundles()中启用了。
当使用generate：bundle命令时，它会自动帮你完成这步。*

##Bundle目录结构

bundle的目录结构使简单而灵活的。默认情况，bundle系统遵循一系列的约定，这可以使得Symfony2的bundle保持一致性。
看看AcmeHelloBundle，它包含了一个bundle中最常见的一些内容：

* Controller/ 包含这个bundle的控制器
* DependencyInjection/ 存放一些依赖注入扩展类，他们可能需要导入一些服务配置，注册编译器入口（这个目录不是必须的）
* Resources/views/ 管理配置，包括路由配置
* Resources/views/ 存放公共控制器名组织的模板
* Resources/public/ 包含web资源（图片、样式表等），通过assets:install命令它被复制或符号链接到项目的web/目录
* Tests/ 存放bundle的测试用例

根据实现功能，一个bundle可大可小。它只包含你需要的文件，除此之外没有其他的。

随着阅读这本书的深入，你会了解如何持久化对象到数据库、创建和验证表单、创建翻译器、写测试用例和其他更多的内容。
他们中的每一个在bundle中都有他们自己的位置和角色。

###应用配置

一个应用由一系列实现应用特性和功能的bundle组成，每个bundle都可以通过YAML,XML或php被配置。
默认情况下，主要的配置文件在app/config/目录，名称为config.yml、config.xml或config.php，这取决于你使用哪种格式：

    # app/config/config.yml
    imports:
        - { resource: parameters.yml }
        - { resource: security.yml }

    framework:
        secret:          "%secret%"
        router:          { resource: "%kernel.root_dir%/config/routing.yml" }
        # ...

    # Twig Configuration
    twig:
        debug:            "%kernel.debug%"
        strict_variables: "%kernel.debug%"

    # ...

*在下面的环境一节中你将会学到如何载入每个文件或格式*

每个顶级条目像：framework或twig定义了某个特定bundle的配置。
比如，framework定义了Symfony核心FrameworkBundle的配置，还包括路由配置、模板和其他的核心系统。

现在，不要关心每部分的配置，配置文件有默认的配置。随着你对Symfony每个部分了解的深入，你将会学到每个特性的特有配置。

##默认配置导出

你可以在终端中使用config:dump-reference命令导出某个bundleYAML格式的配置。
下面是一个导出默认的FrameworkBundle配置的例子：

    $ app/console config:dump-reference FrameworkBundle

也可以使用扩展的别名：

    $ app/console config:dump-reference framework

*阅读cookbook的文章：[How to expose a Semantic Configuration for a Bundle](http://symfony.com/doc/current/cookbook/bundles/extension.html),
了解为自己的bundle添加配置信息。*

###环境

一个应用可以运行在多个环境中，不同的环境共享相同的代码（暂时先不说前端控制器的不同），但是使用不同的配置。
例如，开发环境会记录警告和错误，而生产环境只记录错误。
有些文件总是在每个请求中重建（为了开发者的便利），但是在生产环境中被缓存。
所有的环境处在同一个机器上，并且执行同一个应用。

尽管建一个新的环境很简单，Symfony2项目还是自带了三个环境（开发，生产和测试）。
你可以通过在浏览器中改变前端控制器，从而轻易的查看不同的环境。
为了查看开发环境，通过下面的前端控制器访问应用：

    http://localhost/app_dev.php/hello/Ryan

如果你想看看生产环境的行为，调用生产环境的前端控制器：

    http://localhost/app.php/hello/Ryan

由于生产环境优化了速度，配置、路由和Twig模板都被编译成php类并且被缓存。当要查看生产环境的变化时，
你需要清除这些缓存文件，然后让他们重建：

    $ php app/console cache:clear --env=prod --no-debug

如果你打开了web/app.php文件，你会发现，它被明确的配置实用生产环境：

    $kernel = new AppKernel('prod', false);

你可以通过复制这个文件创建新的环境，只要把prod改成别的值。

*测试环境用来跑自动化测试，它不能直接通过浏览器访问。阅读[测试](http://symfonycn.com/the_book/v2.4.0/testing)了解更多内容*

##环境配置

AppKernel类负责根据你的选择载入配置：

    // app/AppKernel.php
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(
            __DIR__.'/config/config_'.$this->getEnvironment().'.yml'
        );
    }

你已经知道了.yml扩展可以被改成.xml或.php,如果你想使用xml或者php去写配置的话。
注意每个环境载入它自己的配置文件。思考一下开发环境的配置：

    # app/config/config_dev.yml
    imports:
        - { resource: config.yml }

    framework:
        router:   { resource: "%kernel.root_dir%/config/routing_dev.yml" }
        profiler: { only_exceptions: false }

    # ...

import关键字类似于php中的include语法，保证主配置文件首先被载入。
文件的剩余部分是对于增加的日志功能的默认配置和其他有利于开发环境的设置。

生产和测试环境也遵循这个模式：每个环境导入基本的配置，然后修改配置的值以适应当前的环境。
这只是一个约定，但是允许你复用你配置的大部分内容以及在不同的环境中特定的修改一部分。

##总结

祝贺你！你可以了解了Symfony2的每个基本方面，并且发现它是多么的简单和灵活。由于下面还有很多的特性，记住下面的几点：

* 创建页面只要三步，包括路由、控制器和模板。
* 每个项目保护几个主要的目录：web/、app/、src/和vendor/
* Symfony2中的每个特性都被组织在一个bundle中，它是这个特性的文件集合
* 每个bundle的配置在Resources/config目录中，可以指定为YAML,XML或php。
* 全局的配置在app/config目录
* 每个环境通过不同的前端控制器访问，并且载入不同的配置文件

从这章开始，每个章节会向你介绍越来越有用的工具和高级的概念。
随着你对Symfony2了解的越多，你就越能体会到它的灵活性和它加速开发的能力。


###文档下载

Doc: [Word版下载](http://pan.baidu.com/s/1gdgL5mV)

Pdf: [Pdf版下载](http://pan.baidu.com/s/1mgjZgMk)


<a style="float:left; display: block; width:49%; padding: 20px 0px;; text-decoration: none; font-size: 18px; border: 1px solid #ccc; text-align:center; margin: 50px 0px;" href="http://symfonycn.com/the_book/v2.4.0/symfony2-and-http-fundamentals">上一篇： 安装和配置Symfony</a>
<a style="float:left; display: block; width:50%; padding: 20px 0px;; text-decoration: none; font-size: 18px; border: 1px solid #ccc; text-align:center; margin: 50px 0px;" href="http://symfonycn.com/the_book/v2.4.0/installing-and-configuring-symfony">下一篇： 控制器</a>



