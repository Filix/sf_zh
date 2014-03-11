#控制器

控制器就是你创建的一个PHP方法，它从HTTP请求对象中获取信息然后构造并返回一个HTTP响应（在Symfony2中就是Response对象）。
这个响应可以是一个HTML页面、一个XML文档、一个序列化的JSON数组、一个图片、一个重定向、一个404错误或其他你能创造出来的东西。
控制器包含你的应用需要渲染页面内容的任意逻辑。

通过一个Symfony2控制器的动作（action）可以了解到它是多么简单。
下面的控制器会渲染出一个只打印出hello world的页面：

    use Symfony\Component\HttpFoundation\Response;

    public function helloAction()
    {
        return new Response('Hello world!');
    }

控制器的目标总是一样的：创建和返回一个Response对象。
在这个过程中，它可能需要从请求中读取信息，载入数据库资源，发送邮件或者更新用户的session信息。
但是在所有的情况中，控制器最终都会返回Response对象，并发送给客户端。

没有什么神奇的地方，也不用担心有其他的要求。下面是几个相同的例子：

1. 控制器A准备了一个Response对象，它展示了网站首页的内容
2. 控制器B从请求中读取slug参数，然后从数据库中读取博客条目。如果找不到该slug，它会创建并返回一个带有404状态码的response对象
3. 控制器C处理联系表单的提交。它从请求中读取信息，保存联系信息到数据库中，并且通过邮件将联系信息发送给管理员。最后它创建一个Response对象，这个对象重定向客户端到联系表单的感谢页面。

##请求，控制器，响应的生命周期

每个被Symfony2处理的请求都有相同而且简单的生命周期。
框架处理重复的任务并且在最后执行一个控制器，这个控制器容纳了你自定义的应用程序代码：

1. 每个请求都被一个前端控制器（app.php或app_dev.php）处理并引导应用
2. 路由系统重请求中读取信息（比如uri），找到匹配该信息的路由，然后从路由系统中读取_controller参数。
3. 在路由中被匹配的控制器被执行，在控制器中的代码创建和返回一个Response对象
4. HTTP头和Response对象的内容被返回给客户端

创建一个页面只需要创建一个控制器，然后增加一条路由规则将url映射到这个控制器。

*尽管名字有点类似，前端控制器和本章所说的控制器是不同的。
一个前端控制器就是一个简短的php文件，该文件存放在web目录下，所有的请求都被定向到这个文件。
一个典型的应用有一个生产环境控制器（app.php）和一个开发环境控制器（app_dev.php）。
基本上，你不需要编辑、查看或者关心应用中的前端控制器。*

##一个简单的控制器

尽管控制器可以是任何php中可调用的类型（函数、一个对象的方法或者闭包），
在Symfony2中，控制器通常只是控制器对象中的一个方法，控制器通常也叫动作。

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

*注意，控制器是indexAction方法，它存在于一个控制器类中（HelloController）。
不要被名字迷惑了：控制器类只是一个将多个控制器/动作（比如updateAction、deleteAction）组织在一起的方便的方式。*

控制器非常的简单：
    
* 第四行： Symfony2使用php5.3中的命名空间来命名控制器类。use关键字导入Response对象，它会被控制器返回。
* 第六行： 类名是控制器名字（Hello）和单词Controller的组合。这是一个惯例，这样会使控制器变的一致，而且在路由配置中可以只使用名字中的第一部分（Hello）来引用这个控制器。
* 第八行： 控制器类中的每个方法都以Action为后缀，在路由配置中通过动作名（index）即可引用。
* 第十行： 控制器创建并返回一个Response对象。

##URL到控制器的映射

新的控制器返回简单的HTML页面。想要在你的浏览器中看到这个页面，你需要创建一个路由，它会映射特定的url到这个控制器上：

    # app/config/routing.yml
    hello:
        path:      /hello/{name}
        defaults:  { _controller: AcmeHelloBundle:Hello:index }

浏览/hello/ryan页面会执行HelloController::indexAction()控制器，并且把ryan作为$name变量传给控制器。
创建页面通常意味着创建一个控制器和与其关联的路由。

想要了解更多关于引用不同控制器的字符格式，可以浏览[Controller Naming Pattern](http://symfony.com/doc/current/book/routing.html#controller-string-syntax)

*上例中，直接将路由配置放在app/config/目录下。
更好的方式是将路由放在它所处的bundle中。更多信息请查阅[Including External Routing Resources](http://symfony.com/doc/current/book/routing.html#routing-include-external-resources)*

*你可以在[路由](http://symfonycn.com/the_book/v2.4.0/routing)一章中学到更多关于路由的信息*

###将路由参数作为控制器参数

你已经知道了_controller参数AcmeHelloBundle:Hello:index引用在AcmeHelloBundle中的HelloController::indexAction() 。
更有有趣的是传递到这个方法的参数：

    // src/Acme/HelloBundle/Controller/HelloController.php
    namespace Acme\HelloBundle\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;

    class HelloController extends Controller
    {
        public function indexAction($name)
        {
          // ...
        }
    }

这个控制器只有一个参数$name，它对应匹配路由中的{name}参数（本例中是ryan）。
事实上，在执行控制器的时候，Symfony2通过匹配的路由中的参数匹配每个控制器的参数。
看下面这个例子：

    # app/config/routing.yml
    hello:
        path:      /hello/{firstName}/{lastName}
        defaults:  { _controller: AcmeHelloBundle:Hello:index, color: green }

它的控制器可以有多个参数：

    public function indexAction($firstName, $lastName, $color)
    {
        // ...
    }

注意这两个占位符变量（{firstName},{lastName}）以及默认的color变量都可以作为控制器的参数。
当一个路由被匹配时，占位符变量和defaults合并成一个数组，并且对于你的控制器是可调用的。

将路由参数映射到控制器参数是简单而灵活的。在开发的时候，在脑子中想想下面的指导说明：

* 控制器参数的顺序不重要

Symfony可以匹配路由中的参数到控制器方法签名中的变量名。
换句话说，它知道{lastName}参数和$lastName参数对应

* 控制器中的每个参数必须要匹配一个路由参数

下面的例子会抛出一个RuntimeException，因为在路由中没有定义foo参数

    public function indexAction($firstName, $lastName, $color, $foo)
    {
        // ...
    }

如果，将参数设为可选的则可以通过。下面的例子不会抛出异常：

    public function indexAction($firstName, $lastName, $color, $foo = 'bar')
    {
        // ...
    }

* 不是所有的路由参数都需要在控制器中作为参数

比如，如果lastName对你的控制器来说不重要，你完全可以忽略它：

    public function indexAction($firstName, $color)
    {
        // ...
    }

*每个路由都有一个特殊的_route参数，它的值等于匹配的路由的名字（比如hello）。
尽管不经常被使用，它同样可以作为控制器的参数*

###Request对象作为控制器参数

出于方便考虑，你也可以让Symfony将Request对象作为控制器参数传给你。
当你在处理表单的时候这会非常的方便，比如：

    use Symfony\Component\HttpFoundation\Request;

    public function updateAction(Request $request)
    {
        $form = $this->createForm(...);

        $form->handleRequest($request);
        // ...
    }


##创建静态页面

你可以不创建控制器而直接创建一个静态页面（只有路由和模板是必须的）。

使用它，可以查看[How to render a Template without a custom Controller](http://symfony.com/doc/current/cookbook/templating/render_without_controller.html)。

##控制器基类

为了方便，Symfony2自带了一个基本的控制器类，它实现了大多数控制器经常遇到的任务，提供你的控制器访问它所需的资源的能力。
通过继承Controller类，你可以使用几个helper方法。

在控制器类顶部添加use声明，然后修改HelloController类去继承它：

    // src/Acme/HelloBundle/Controller/HelloController.php
    namespace Acme\HelloBundle\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    use Symfony\Component\HttpFoundation\Response;

    class HelloController extends Controller
    {
        public function indexAction($name)
        {
            return new Response('<html><body>Hello '.$name.'!</body></html>');
        }
    }

这不会改变你的控制器的工作方式。
在下面的章节中，你会了解到基类提供的helper方法的使用。
这些方法只是使用Symfony2核心功能的封装，不管是否继承这个基类，都可以使用到这些核心功能。
在动作中查看这些核心功能的好方法是看看这个[Controller](http://api.symfony.com/2.4/Symfony/Bundle/FrameworkBundle/Controller/Controller.html)类的内部。

*继承这个基类在Symfony中是可选的；它提供了一些有用的封装，但是缺不是强制要求的。
你也可以继承[ContainerAware](http://api.symfony.com/2.4/Symfony/Component/DependencyInjection/ContainerAware.html)
或Symfony\Component\DependencyInjection\ContainerAwareTrait这个trait（如果你使用php5.4）。
这个服务包含了可以通过contrainer属性访问的对象。*

*Version2.4新功能：ContainerAwareTrait 是Symfony2.4新加入的功能*

*你也可以[将控制器作为服务](http://symfony.com/doc/current/cookbook/controller/service.html)。
这是可选的，但是可以让你更好的控制你的控制器之间的依赖性*

##常见的控制器功能

尽管控制器可以做几乎任何的事，但是大多数控制器总是一遍又一遍的做一些相同的事。
这些事情包括，重定向、转发、渲染模板和访问核心服务，在Symfony2中他们都很容易使用。

###重定向

如果你想将用户重定向到别的页面，使用redirect()方法：

    public function indexAction()
    {
        return $this->redirect($this->generateUrl('homepage'));
    }

generateUrl()方法是一个创建url的helper方法。更多信息查看[路由](http://symfonycn.com/the_book/v2.4.0/routing)一章。

默认情况下，redirect()方法执行一个302（临时）重定向。
为了执行301（永久）重定向，修改第二个参数：

    public function indexAction()
    {
        return $this->redirect($this->generateUrl('homepage'), 301);
    }

redirect()方法是创建一个专门重定向用户的Response对象的快捷方式。
它等同于：

    use Symfony\Component\HttpFoundation\RedirectResponse;

    return new RedirectResponse($this->generateUrl('homepage'));


###转发

你也可以通过forward()方法将请求转发到另一个控制器内容。
它创建一个内部的子请求，调用指定的控制器，而不是重定向用户的浏览器。
forward()方法返回从另一个控制器中返回的Response对象。

    public function indexAction($name)
    {
        $response = $this->forward('AcmeHelloBundle:Hello:fancy', array(
            'name'  => $name,
            'color' => 'green',
        ));

        // ... further modify the response or return it directly

        return $response;
    }

注意forward()方法使用在路由配置中一样的字符串来代表控制器。
在这个例子中，目标控制器是AcmeHelloBundle中的HelloController。
传给这个方法的数组将是目标控制器的参数。在模板中嵌入控制器时使用同样的接口（查看[Embedding Controllers](http://symfony.com/doc/current/book/templating.html#templating-embedding-controller)）
模板控制器将会像下面这样：

    public function fancyAction($name, $color)
    {
        // ... create and return a Response object
    }

同给一个路由创建控制器一样，fancyAction的参数顺序不重要。
Symfony2根据数组的键名（如name）和方法的参数名（$name）匹配。
如果你改变参数的顺序，Symfony2仍然会将正确的值传给每个变量。

和其他的基本控制器方法一样，forward方法是Symfony2核心方法的一个快捷方式。
转发通过复制当前的请求完成。当这个子请求通过heep_kernel服务执行时，HttpKernel返回一个Response对象：

    use Symfony\Component\HttpKernel\HttpKernelInterface;

    $path = array(
        '_controller' => 'AcmeHelloBundle:Hello:fancy',
        'name'        => $name,
        'color'       => 'green',
    );
    $request = $this->container->get('request');
    $subRequest = $request->duplicate(array(), null, $path);

    $httpKernel = $this->container->get('http_kernel');
    $response = $httpKernel->handle(
        $subRequest,
        HttpKernelInterface::SUB_REQUEST
    );



###渲染模板

尽管不是强制要求的，但是大多数的控制器在最后还是会渲染一个模板来输出HTML（或其他格式）。
renderView()方法渲染一个模板并返回它的内容。返回的这个内容可以用来创建一个Response对象：

    use Symfony\Component\HttpFoundation\Response;

    $content = $this->renderView(
        'AcmeHelloBundle:Hello:index.html.twig',
        array('name' => $name)
    );

    return new Response($content);

也可以使用render()方法一步实现这个功能，render()方法返回一个包含了模板内容的Response对象：

    return $this->render(
        'AcmeHelloBundle:Hello:index.html.twig',
        array('name' => $name)
    );

在两个例子中，在AcmeHelloBundle中的Resources/views/Hello/index.html.twig模板被渲染。

Symfony的模板引擎在[模板](http://symfonycn.com/the_book/v2.4.0/creating-and-using-templates)一章有非常详细的介绍。

*你也可以使用@Template注释而不需要调用render方法。详细内容可以查看[FrameworkExtraBundle documentation](http://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/view.html)*

renderView方法是直接使用templating服务的快捷方式。你也可以直接使用templating服务：

    $templating = $this->get('templating');
    $content = $templating->render(
        'AcmeHelloBundle:Hello:index.html.twig',
        array('name' => $name)
    );


也可以渲染在更深层次的子目录中的模板，但是小心避免因为过度使用子目录而带来的陷阱：


    $templating->render(
        'AcmeHelloBundle:Hello/Greetings:index.html.twig',
        array('name' => $name)
    );
    // index.html.twig found in Resources/views/Hello/Greetings
    // is rendered.


###访问其他服务

当继承了控制器基类，你可以通过get()方法访问Symfony2的服务。
下面是一些你可能会用到的常见的几个服务：

    $templating = $this->get('templating');
    $router = $this->get('router');
    $mailer = $this->get('mailer');

还有很多其他的服务，你也可以定义自己的服务。为了显示所有可用的服务，可以在终端命令行下使用container:debug命令：

    $ php app/console container:debug

##管理错误和404页面

当某些资源没有找到时，你应该展示友好的页面并返回404响应。
为了做到这样，你需要抛出一个特殊的异常。如果你继承了控制器基类，可以像下面这样：

    public function indexAction()
    {
        // retrieve the object from database
        $product = ...;
        if (!$product) {
            throw $this->createNotFoundException('The product does not exist');
        }

        return $this->render(...);
    }

createNotFoundException()方法创建一个特殊的NotFoundHttpException对象，
它在Symfony内容产生一个404响应。

当然，你可以在控制器中任意的抛出任何异常，这样Symfony2会自动的返回一个HTTP500的响应码.

    throw new \Exception('Something went wrong!');

在每种情况下，一个设计过样式的错误页面会展示给最终用户，一个充满调试信息的错误页面被展示给开发者（当在debug模式下查看页面）。
这两种页面都可以自定义。更多信息可以阅读cookbook中的[如何自定义错误页面](http://symfony.com/doc/current/cookbook/controller/error_pages.html)

##管理Session

Symfony2提供了一个很好的session对象，通过它你可以在请求之间存储用户的信息。
默认情况下Symfony2通过使用原生的php session将这些信息存储在cookie中。

在任何控制器中向session中存储和检索信息都可以很简单的完成：

    use Symfony\Component\HttpFoundation\Request;

    public function indexAction(Request $request)
    {
        $session = $request->getSession();

        // store an attribute for reuse during a later user request
        $session->set('foo', 'bar');

        // in another controller for another request
        $foo = $session->get('foo');

        // use a default value if the key doesn't exist
        $filters = $session->get('filters', array());
    }

###Flash消息

你也可以只在某一个请求中，在用户的session中存储一些小的信息。
这在处理表单的时候非常有用：比如你要重定向用户并且在下一个请求中显示某个特殊的信息。
这种信息叫做flash。

比如，你可以想象一下你在处理一个表单：

    use Symfony\Component\HttpFoundation\Request;

    public function updateAction(Request $request)
    {
        $form = $this->createForm(...);

        $form->handleRequest($request);

        if ($form->isValid()) {
            // do some sort of processing

            $this->get('session')->getFlashBag()->add(
                'notice',
                'Your changes were saved!'
            );

            return $this->redirect($this->generateUrl(...));
        }

        return $this->render(...);
    }

在处理完请求之后，控制器设置了一个notice的flash信息，然后重定向。
flash的名字（notice）没有特殊的意义，它就是你用来找到这条信息的标识。

在下一个动作的模板中，下面的代码可以用来渲染notice信息：

    {% for flashMessage in app.session.flashbag.get('notice') %}
        <div class="flash-notice">
            {{ flashMessage }}
        </div>
    {% endfor %}

通过特殊的设计，flash信息只存在于一个请求中。他们被设计成在重定向之间使用，就像这个例子中一样。

##响应对象

控制器的唯一要求就是返回一个Response对象。Response对象是HTTP响应的一个抽象，
HTTP响应就是在HTTP头和内容中存储基于文本的信息，然后发送给客户端。

    use Symfony\Component\HttpFoundation\Response;

    // create a simple Response with a 200 status code (the default)
    $response = new Response('Hello '.$name, Response::HTTP_OK);

    // create a JSON-response with a 200 status code
    $response = new Response(json_encode(array('name' => $name)));
    $response->headers->set('Content-Type', 'application/json');

*Version2.4新特性： Symfony2.4新加入HTTP状态码常量的支持*

*headers属性是一个HeaderBag对象，它有一些读取和改变Response头的有用的方法。
header的名字是标准化的，因此使用Content-Type等同于content-type或content_type。*

还有一些其他的特殊的类，他们可以方便的创建其他形式的响应：

* 对于json，有[JsonResponse](http://api.symfony.com/2.4/Symfony/Component/HttpFoundation/JsonResponse.html)。查看[创建json响应](http://symfony.com/doc/current/components/http_foundation/introduction.html#component-http-foundation-json-response)
* 对于文件，有[BinaryFileResponse](http://api.symfony.com/2.4/Symfony/Component/HttpFoundation/BinaryFileResponse.html)。查看[Serving Files](http://symfony.com/doc/current/components/http_foundation/introduction.html#component-http-foundation-serving-files)。

##请求对象

除了路由占位符的值，控制器也可以访问Request对象。
如果一个变量被申明成SymfonyComponentHttpFoundationRequest类型框架会注入一个Request对象给控制器。

    use Symfony\Component\HttpFoundation\Request;

    public function indexAction(Request $request)
    {
        $request->isXmlHttpRequest(); // is it an Ajax request?

        $request->getPreferredLanguage(array('en', 'fr'));

        $request->query->get('page'); // get a $_GET parameter

        $request->request->get('page'); // get a $_POST parameter
    }

和Response对象一样，请求头存储在HeaderBag对象中，访问非常容易。

##总结

无论何时，当你在创建页面时，你最终都需要写一些逻辑的代码。
在Symfony中这叫做控制器，它是一个可以做任何事情的php方法，它最终就为了返回发送给用户的Response对象。

为了方便，你可以选择继承Controller基类，它包含了一些常见任务封装后的方法。
比如你想在控制器中输出HTML，你可以使用redder()方法来渲染并且从目标返回内容。

在其他的章节，你将会看到控制器可以用来持久化到数据库和从数据库检索数据，处理表单提交，处理缓存和其他任务。

##通过CookBook了解更多

* [如何自定义错误页面](http://symfony.com/doc/current/cookbook/controller/error_pages.html)
* [如何将控制器定义为服务](http://symfony.com/doc/current/cookbook/controller/service.html)