#Symfony2 VS 原生的PHP

**为什么说Symfony2比打开一个文件，然后直接开始写php代码要好？**

如果你从没有用过框架、或者不熟悉MVC概念、或者只是被Symfony2的一些讨论所吸引，那么，这章是为你准备的。
我们不会直接告诉你Symfony2比用原生的PHP使得开发更快速、软件质量更好，你自己会体会到的。

在这章中，你将会用原生的PHP写一个小应用，然后把它重构，使之更有组织性。
你将会穿越时空，看到在过去的这些年里，web开发进化到如今这个地步的背后的一些决策。

最后，你会发现Symfony2是如何把你从庸俗的任务中解救出来，然后可以掌控自己的代码。

##原生php的小博客

在这章，你将会用原生的php搭建一个象征性的博客应用。首先，创建一个页面用来展示存在数据库中的所有博客条目。
使用原生的php是很快的，同时也很混乱。


    <?php
    // index.php
    $link = mysql_connect('localhost', 'myuser', 'mypassword');
    mysql_select_db('blog_db', $link);

    $result = mysql_query('SELECT id, title FROM post', $link);
    ?>

    <!DOCTYPE html>
    <html>
        <head>
            <title>List of Posts</title>
        </head>
        <body>
            <h1>List of Posts</h1>
            <ul>
                <?php while ($row = mysql_fetch_assoc($result)): ?>
                <li>
                    <a href="/show.php?id=<?php echo $row['id'] ?>">
                        <?php echo $row['title'] ?>
                    </a>
                </li>
                <?php endwhile; ?>
            </ul>
        </body>
    </html>

    <?php
    mysql_close($link);
    ?>

上面的代码很快就可以写好，执行的也很快，但是随着应用的增长，维护几乎是不可能的。
有几个主要的问题需要说明：

* 没有错误检查；如果连接数据库失败怎么办？
* 代码缺乏组织性；随着应用的增长，一个文件将变的不可能维护。在哪处理表单提交？怎么样验证数据？发送邮件的代码应该写在哪？
* 无法复用代码；由于任何逻辑都是在一个文件里，应用的其他页面无法复用任何一部分代码

*另外一个没提到的问题是数据库写死了使用MySql。虽然本章不介绍，Symfony2整合了[Doctrine](http://www.doctrine-project.org/),
这是一个专注于数据库抽象和映射的类库*

让我们继续去解决这些问题

###独立表现层

代码很容易从分离应用程序逻辑和表现层HTML中获益：

    <?php
    // index.php
    $link = mysql_connect('localhost', 'myuser', 'mypassword');
    mysql_select_db('blog_db', $link);

    $result = mysql_query('SELECT id, title FROM post', $link);

    $posts = array();
    while ($row = mysql_fetch_assoc($result)) {
        $posts[] = $row;
    }

    mysql_close($link);

    // include the HTML presentation code
    require 'templates/list.php';

现在HTML代码写在单独的文件中(template/list.php)，天本质上就是一个使用php模板语言的HTML文件。

    <!DOCTYPE html>
    <html>
        <head>
            <title>List of Posts</title>
        </head>
        <body>
            <h1>List of Posts</h1>
            <ul>
                <?php foreach ($posts as $post): ?>
                <li>
                    <a href="/read?id=<?php echo $post['id'] ?>">
                        <?php echo $post['title'] ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </body>
    </html>

按照惯例，包含应用程序逻辑的文件-index.php-被称作控制器。不管你使用什么语言的框架，控制器这个词你将会经常见到。
它连接处理用户输入和准备响应两个过程。

在这个例子中，控制器从数据库中准备数据，然后包含一个模板去展示这些数据。通过和控制器分离，
如果你想使用其他格式渲染博客条目只需要改变包含的模板文件就可以了（比如json格式使用list.json.php）。

###分离应用程序逻辑

目前为止，这个应用只有一个页面。如果第二个页面也需要使用相同的数据库连接，或者相同的博客文章数组要怎么办呢？
重构代码使核心行为和应用的数据访问方法独立到单独的文件中，我们叫model.php:


    <?php
    // model.php
    function open_database_connection()
    {
        $link = mysql_connect('localhost', 'myuser', 'mypassword');
        mysql_select_db('blog_db', $link);

        return $link;
    }

    function close_database_connection($link)
    {
        mysql_close($link);
    }

    function get_all_posts()
    {
        $link = open_database_connection();

        $result = mysql_query('SELECT id, title FROM post', $link);
        $posts = array();
        while ($row = mysql_fetch_assoc($result)) {
            $posts[] = $row;
        }
        close_database_connection($link);

        return $posts;
    }

*文件名使用model.php是因为，应用逻辑和数据访问习惯上被称作模型层。在一个组织性良好的项目中，
业务逻辑的主要代码都在模型中，而不是在控制器中。不像这个例子，只有一部分（或没有）模型关心访问数据库*

控制器现在非常简单：

    <?php
    require_once 'model.php';

    $posts = get_all_posts();

    require 'templates/list.php';

现在，控制器的唯一任务就是从模型层获取数据，然后调用模板去渲染数据。这是一个非常简单的模型-视图-控制器模式。

###分离布局

现在，应用被重构成3个独立的部分，这有很多的优点、而且在不同的页面中复用也可行了。

代码中唯一不能复用的是视图层。通过创建一个layout.php文件完善：


    <!-- templates/layout.php -->
    <!DOCTYPE html>
    <html>
        <head>
            <title><?php echo $title ?></title>
        </head>
        <body>
            <?php echo $content ?>
        </body>
    </html>

模板（template/list.php）现在可以通过继承布局得到简化：

    <?php $title = 'List of Posts' ?>

    <?php ob_start() ?>
        <h1>List of Posts</h1>
        <ul>
            <?php foreach ($posts as $post): ?>
            <li>
                <a href="/read?id=<?php echo $post['id'] ?>">
                    <?php echo $post['title'] ?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    <?php $content = ob_get_clean() ?>

    <?php include 'layout.php' ?>

你现在被传授了一种复用模板的方法。但是，为了实现这点，你必须要在模板中使用一些不太优雅的php函数（ob_start()和ob_get_clean（））
Symfony2使用模板组件让你优雅而且简单的完成这个需求。稍后你将会看到这步操作。

##增加博客展示页面

博客列表页面已经被重构的结构更好，而且可以复用。为了验证这点，添加一个博客展示页面，它通过一个id请求参数展示一篇博客。

首先，在model.php文件中新创建一个方法，它通过提供的id检索一个博客。

    // model.php
    function get_post_by_id($id)
    {
        $link = open_database_connection();

        $id = intval($id);
        $query = 'SELECT date, title, body FROM post WHERE id = '.$id;
        $result = mysql_query($query);
        $row = mysql_fetch_assoc($result);

        close_database_connection($link);

        return $row;
    }

接着，创建一个新文件叫show.php -- 这个页面的控制器

    <?php
    require_once 'model.php';

    $post = get_post_by_id($_GET['id']);

    require 'templates/show.php';

最后，创建一个新的模板文件 - templates/show.php，用来渲染这个单独的博客文章：

    <?php $title = $post['title'] ?>

    <?php ob_start() ?>
        <h1><?php echo $post['title'] ?></h1>

        <div class="date"><?php echo $post['date'] ?></div>
        <div class="body">
            <?php echo $post['body'] ?>
        </div>
    <?php $content = ob_get_clean() ?>

    <?php include 'layout.php' ?>

创建第二个页面非常简单，也没有重复使用代码。但是，这个页面还是引入了一些框架可以解决的问题。
比如，没有或者非法的id会导致页面崩溃。如果出现404页面会更好，但是这个不太容易实现。更糟的是，
如果你忘记了使用intval()函数过滤id参数，你的数据库就有被sql注入攻击的危险。

另一个主要问题是每个独立的控制器都必须要引入model.php文件。如果每个控制器突然都要包含另一个文件或者
执行一些全局的任务（安全监测）怎么办？当前这个情况，代码必须加到每个控制器文件中。如果你忘记了其中的一个，
祈祷不会产生安全问题吧

##前端控制器来营救

解决方法是使用一个[前端控制器](http://symfony.com/doc/current/glossary.html#term-front-controller),
这个一个文件，所有的请求都被它处理。通过前端控制器，应用程序的uri会稍微发生些变化，但是更灵活了。

    Without a front controller
    /index.php          => Blog post list page (index.php executed)
    /show.php           => Blog post show page (show.php executed)

    With index.php as the front controller
    /index.php          => Blog post list page (index.php executed)
    /index.php/show     => Blog post show page (index.php executed)


*uri中index.php部分可以使用apache rewrite模块移除掉。在这个例子中，博客展示页面的uri就会变成"/show"*

当使用了前端控制器，一个文件（index.php）处理所有请求。对于博客展示页面，/index.php/show会执行index.php文件，
它现在负责根据uri路由所有请求。稍后你会看到，前端控制器是一个非常有用的工具。

###创建前端控制器

你将对应用做一次大改进。通过使用一个文件处理所有请求，你可以把一些事情，比如安全控制、载入配置和路由等统一处理。
在这个应用中，index.php必须足够聪明，能够根据请求的uri渲染博客列表或者博客展示页面：

    <?php
    // index.php

    // load and initialize any global libraries
    require_once 'model.php';
    require_once 'controllers.php';

    // route the request internally
    $uri = $_SERVER['REQUEST_URI'];
    if ('/index.php' == $uri) {
        list_action();
    } elseif ('/index.php/show' == $uri && isset($_GET['id'])) {
        show_action($_GET['id']);
    } else {
        header('Status: 404 Not Found');
        echo '<html><body><h1>Page Not Found</h1></body></html>';
    }

为了结构更好，两个控制器（index.php和show.php）现在是php方法了，而且被移到一个文件中controllers.php：

    function list_action()
    {
        $posts = get_all_posts();
        require 'templates/list.php';
    }

    function show_action($id)
    {
        $post = get_post_by_id($id);
        require 'templates/show.php';
    }

作为前端控制器，index.php担当了一个新的角色，包括载入核心类库和路由，以使两个控制器被调用。
事实上，这个前端控制器开始有点类似Symfony2处理和路由请求的机制了。


前端控制器的另一个有点是灵活的url。注意，博客展示页面的url可以从"/show"改为"/read"，而这只需要修改一个地方的代码。
在之前，需要修改一个文件名。在Symfony2中，url比这更灵活。

到目前为止，这个项目已经从单个php文件改进到结构有组织性而且代码可以复用的结果。你应该高兴一点，但远没到满意的地步。
比如路由系统是经常变化的，不能通过"/"识别列表为列表页面。花了大量时间在代码的结构组织上，而不是开发博客系统上。
更多的时间应该被用在处理表单提交、输入验证、日志和安全上。你为什么要在这些日常问题上重复造轮子呢？

###接触Symfony2

让Symfony2解救你。在开始使用Symfony2之前，你要先下载它。这可以通过composer完成，它会下载正确的版本、解决依赖关系并提供自动载入工具。
自动载入是可以不需要提前引入一个类就直接使用它的工具。

在你的根目录，用下面的内容创建一个composer.json文件：

    {
        "require": {
            "symfony/symfony": "2.4.*"
        },
        "autoload": {
            "files": ["model.php","controllers.php"]
        }
    }

下一步，[下载composer](http://getcomposer.org/download/)，然后运行下面的命令，它会把Symfony下载到vendor目录：

    $ php composer.phar install

在开始下载依赖之前，composer会创建一个vendor/auotload.php文件，它负责自动载入symfony框架的所有文件以及在composer.json中指定的文件。

Symfony的核心概念是：应用程序的主要工作是接收请求并返回响应。为了这个目的，Symfony2提供了Request和Responce类。
这些类是处理原生HTTP请求和响应的面向对象展现。使用他们来改善博客系统：

    <?php
    // index.php
    require_once 'vendor/autoload.php';

    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;

    $request = Request::createFromGlobals();

    $uri = $request->getPathInfo();
    if ('/' == $uri) {
        $response = list_action();
    } elseif ('/show' == $uri && $request->query->has('id')) {
        $response = show_action($request->query->get('id'));
    } else {
        $html = '<html><body><h1>Page Not Found</h1></body></html>';
        $response = new Response($html, Response::HTTP_NOT_FOUND);
    }

    // echo the headers and send the response
    $response->send();

*V2.4新特性：在Symfony2.4中加入了HTTP状态码的支持*

控制器现在负责返回responce对象。为了看起来更简单，你可以加入一个新的render_template()函数，作用就像Symfony2的模板引擎：

    // controllers.php
    use Symfony\Component\HttpFoundation\Response;

    function list_action()
    {
        $posts = get_all_posts();
        $html = render_template('templates/list.php', array('posts' => $posts));

        return new Response($html);
    }

    function show_action($id)
    {
        $post = get_post_by_id($id);
        $html = render_template('templates/show.php', array('post' => $post));

        return new Response($html);
    }

    // helper function to render templates
    function render_template($path, array $args)
    {
        extract($args);
        ob_start();
        require $path;
        $html = ob_get_clean();

        return $html;
    }

通过引入Symfony2的小部分，应用更灵活和可靠了。Request对象提供了可靠的方法去访问HTTP请求。尤其是，getPathInfo()
方法返回清除过的uri（总是返回/show而不是/index.php/show）。因此，即使用户访问"/index.php/show"，
应用还是可以智能的把请求路由到show_action()函数。

Response对象提供灵活的方式构建HTTP响应，允许使用面向对象的方式添加HTTP头和内容。
虽然这个应用中的响应很简单，但是随着应用的增长你会不断得到灵活性带来的好处。

###Symfony2的简单应用

改善博客系统已经很长时间了，但是这个小应用包含了很多的代码。在这个过程中，你开发了一个小型的路由系统和一个
使用ob_start()和ob_get_clean()的模板渲染的方法。如果，出于某些原因，你需要继续开发这个框架，你至少可以使用
Symfony中独立的[Routing](https://github.com/symfony/Routing)和[Template](https://github.com/symfony/Templating)组件，他们可以解决这些问题。

不用再去处理这些问题了，你可以让Symfony帮你做。这是一个用Symfony2构建的类似项目：

    // src/Acme/BlogBundle/Controller/BlogController.php
    namespace Acme\BlogBundle\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;

    class BlogController extends Controller
    {
        public function listAction()
        {
            $posts = $this->get('doctrine')->getManager()
                ->createQuery('SELECT p FROM AcmeBlogBundle:Post p')
                ->execute();

            return $this->render(
                'AcmeBlogBundle:Blog:list.html.php',
                array('posts' => $posts)
            );
        }

        public function showAction($id)
        {
            $post = $this->get('doctrine')
                ->getManager()
                ->getRepository('AcmeBlogBundle:Post')
                ->find($id)
            ;

            if (!$post) {
                // cause the 404 page not found to be displayed
                throw $this->createNotFoundException();
            }

            return $this->render(
                'AcmeBlogBundle:Blog:show.html.php',
                array('post' => $post)
            );
        }
    }


这两个控制器仍然是轻量级的。他们都使用[Doctrine ORM library](http://symfony.com/doc/current/book/doctrine.html)
从数据库检索数据，然后模板组件渲染模板，返回Response对象。列表的模板现在更简单了：

    <!-- src/Acme/BlogBundle/Resources/views/Blog/list.html.php -->
    <?php $view->extend('::layout.html.php') ?>

    <?php $view['slots']->set('title', 'List of Posts') ?>

    <h1>List of Posts</h1>
    <ul>
        <?php foreach ($posts as $post): ?>
        <li>
            <a href="<?php echo $view['router']->generate(
                'blog_show',
                array('id' => $post->getId())
            ) ?>">
                <?php echo $post->getTitle() ?>
            </a>
        </li>
        <?php endforeach; ?>
    </ul>

布局还是差不多的样子：

    <!-- app/Resources/views/layout.html.php -->
    <!DOCTYPE html>
    <html>
        <head>
            <title><?php echo $view['slots']->output(
                'title',
                'Default title'
            ) ?></title>
        </head>
        <body>
            <?php echo $view['slots']->output('_content') ?>
        </body>
    </html>

博客展示页的模板当做练习了，因为它对列表模板来说不是必须的。

当Symfony2的引擎（Kernel）启动，它需要一个映射用来判断根据请求的信息哪个控制器该被执行。
路由系统提供了一个具有可读性格式的映射信息：

    # app/config/routing.yml
    blog_list:
        path:     /blog
        defaults: { _controller: AcmeBlogBundle:Blog:list }

    blog_show:
        path:     /blog/show/{id}
        defaults: { _controller: AcmeBlogBundle:Blog:show }

现在，Symfony2处理所有的任务，前端控制器非常之简单。由于它做的事很少，一旦它建好了，你基本不需要改动它。
（如果你使用Symfony2发行版，你都不需要去创建它）


    // web/app.php
    require_once __DIR__.'/../app/bootstrap.php';
    require_once __DIR__.'/../app/AppKernel.php';

    use Symfony\Component\HttpFoundation\Request;

    $kernel = new AppKernel('prod', false);
    $kernel->handle(Request::createFromGlobals())->send();


前端控制器的唯一工具就是实例化Symfony2的引擎，然后传给它Request对象去处理。Symfony2的核心使用路由映射觉得哪个控制器被调用。
和之前一样，控制器方法还是负责返回Response对象。这个没什么不同。

想要直观的了解Symfony是如何处理请求的，请查阅[request flow diagram](http://symfony.com/doc/current/book/http_fundamentals.html#request-flow-figure)。

###Symfony2带来的好处

在下面的章节中，你将会学习到Symfony的每个模块是如何工作的，以及推荐的项目组织结构。
而现在，让我们看看把博客系统从原生的php移植到Symfony2的优点：

* 你的应用现在更清晰，代码结构更统一（尽管Symfony没有强制要求你这么做）。代码复用性提高了，新手也能很快的上手项目。
* 你的代码都是为了你的应用逻辑而写。你无需开发或维护一些底层的公共工具，比如自动载入类、路由、控制器。
* Symfony2提供给你开源的工具，像Doctrine、模板引擎、安全、表单、验证期、多语言化组件。
* 由于路由组件，应用拥有非常灵活的url。
* Symfony2以HTTP为中心的架构提供我们一些强大的工具，比如基于Symfony2内部HTTP缓存的HTTP缓存功能或Varnish。这些将在稍后的缓存一章讲到。

可能，通过使用Symfony2，最大的好处就是你接触到了Symfony2社区提供的大量高质量的开源工具。
Symfony2社区的好工具可以在[knpbundles.com](http://knpbundles.com/)找到。

##更好的模板

Symfony2自带了一个使模板写起来更快、更易读的模板引擎 - [twig](http://twig.sensiolabs.org/)。
这意味着同一个应用使用更少的代码。比如，列表模板使用twig这样写：

    {# src/Acme/BlogBundle/Resources/views/Blog/list.html.twig #}
    {% extends "::layout.html.twig" %}

    {% block title %}List of Posts{% endblock %}

    {% block body %}
        <h1>List of Posts</h1>
        <ul>
            {% for post in posts %}
            <li>
                <a href="{{ path('blog_show', {'id': post.id}) }}">
                    {{ post.title }}
                </a>
            </li>
            {% endfor %}
        </ul>
    {% endblock %}

相应的layout.html.twig同样很简单：

    {# app/Resources/views/layout.html.twig #}
    <!DOCTYPE html>
    <html>
        <head>
            <title>{% block title %}Default title{% endblock %}</title>
        </head>
        <body>
            {% block body %}{% endblock %}
        </body>
    </html>

Symfony2很好的支持Twig。PHP模板也一直被Symfony2支持，Twig的更多优点在接下来会继续讨论。
想要获取更多信息，请查看[模板](http://symfonycn.com/the_book/v2.4.0/creating-and-using-templates)。


##从CookBook了解更多

* [如何使用php代替twig](http://symfony.com/doc/current/cookbook/templating/PHP.html)
* [如何将控制器定义为服务](http://symfony.com/doc/current/cookbook/controller/service.html)


###文档下载

Doc: [Word版下载](http://pan.baidu.com/s/1i3uUp97)

Pdf: [Pdf版下载](http://pan.baidu.com/s/1c0h7oDa)