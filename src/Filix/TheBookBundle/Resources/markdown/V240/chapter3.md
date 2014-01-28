#安装和配置Symfony

本章的目标是让你兴奋一下，我们会运行一个用Symfony构建的应用程序。
幸运的是，Symfony提供了一个发行版，这是一个你下载后可以直接开始开发的功能齐全的Symfony上手项目。

*如果你想知道最好的创建项目和使之加入源代码管理的方法，请浏览文章最后的部分。*

##安装Symfony2发行版

*首先，检查一下你已经安装和配置好了一个支持php的web服务器，比如apache。想了解更多Symfony2运行要求的内容，请查阅[requirements reference](http://symfony.com/doc/current/reference/requirements.html)*

Symfony2发行版是一个功能齐全的应用，它包括了Symfony2的核心类库、一些有用的bundle、一个清晰的目录结构和一些默认配置。
你下载的发行版，就是一个可以立即开始开发的功能性应用骨架。

先浏览Symfony2的下载页面：[http://symfony.com/download](http://symfony.com/download)。
在这个页面，你可以看到Symfony标准版，这是Symfony2最重要的版本。有两种方式开始着手项目：

###一、Composer

[Composer](http://getcomposer.org/)是php的一个依赖管理库，你可以使用它下载Symfony2的标准版。

首先，你要在自己的电脑上[下载composer](http://getcomposer.org/download/)。如果你安装了curl会非常简单：

    $ curl -s https://getcomposer.org/installer | php

*如果你的电脑不满足使用composer的条件，运行命令的时候你会看到一些建议。按照建议做以使composer可以很好的工作*


Composer是一个可执行的PHAR文件，你可以使用它下载标准发行版：

    $ php composer.phar create-project symfony/framework-standard-edition /path/to/webroot/Symfony 2.4.*

*可以在任何Composer命令后面增加--prefer-dist选项，以使vendor文件可以下载的更快*

这个命令会花费几分钟去下载标准发行版和它所需的vendor库。当命令结束时，你应该有一个像下面这样的目录结构：

    path/to/webroot/ <- your web server directory (sometimes named htdocs or public)
    Symfony/ <- the new directory
        app/
            cache/
            config/
            logs/
        src/
            ...
        vendor/
            ...
        web/
            app.php
            ...

###二、下载文档包

你也可以下载标准发行版的文档包。这种方式有两个选择：

* 下载.tgz或.zip文档，两个文档一样，想用哪个就下那个
* 下载有或无vendor的版本。如果你打算使用更多的第三方库或者包，而且想使用Composer管理他们，就下载无vendor版。

下载一个文档到你web服务器的跟目录，然后解压。在unix命令行，可以按照下面的命令做（用真实的名字替换###）：

    # for .tgz file
    $ tar zxvf Symfony_Standard_Vendors_2.4.###.tgz

    # for a .zip file
    $ unzip Symfony_Standard_Vendors_2.4.###.zip

如果你下载的是“without vendor”版，你需要阅读下面的章节。

*你可以简单的推翻默认的结构。阅读[如何覆盖symfony默认的目录结构](http://symfony.com/doc/current/cookbook/configuration/override_dir_structure.html)*

所有公共资源和处理进来的请求的前端控制器文件都在Symfony/web目录。
所以，建设你把文档解压到你的web服务器的根目录下或者虚拟主机的文档目录，你的应用的url应该以http://localhost/Symfony/web/开头。

*下面的例子假设你没有修改文档根目录，所以所有的url都以http://localhost/Symfony/web/开头*

###更新vendors

现在你已经下载了一个功能齐全的Symfony项目，用它你可以开始开发你的应用了。
Symfony项目依赖很多的外部库。他们通过一个叫[Composer](http://getcomposer.org/)的库被下载到vendor目录。

根据你如何下载的Symfony，你可能需要立即更新你的vendor目录。但是，更新你的vendor是非常安全的，它能保证你拥有你所需的所有库。

**第一步，获取Composer**

    $ curl -s http://getcomposer.org/installer | php

保证你下载的composer.phar文件和composer.json文件在同一个目录（默认是Symfony项目的跟目录）。

**第二步，安装vendors**

    $ php composer.phar install

这个命令会下载所有需要的vendor库（包括Symfony自身）到vendor目录。

如果你没有安装curl，你可以在[http://getcomposer.org/installer](http://getcomposer.org/installer)手动下载installer文件。
把这个文件放到你的项目中，然后运行：

    $ php installer
    $ php composer.phar install


当运行php composer.phar install或者php composer.phar update，Composer在执行install/update命令之后会清除缓存和安装资源。
默认情况下这些资源会被复制到web目录

如果你的操作系统支持，你可以创建符号链接而不是复制。
为了创建符号链接，在你的composer.json文件中增加extra节点，使用symfony-assects-install作为key，symlink为值：

    "extra": {
    "symfony-app-dir": "app",
    "symfony-web-dir": "web",
    "symfony-assets-install": "symlink"
    }

当给symfony-assets-install赋relation而不是symlink的值时，这个命令会创建相对的符号链接。


###配置和安装

现在，所有所需的第三方库被安装到vendor目录。你也会有一个默认的安装的程序在app/目录中，还有一些demo代码在src/目录。

Symfony2自带了一个服务的测试工具，帮助你确认你的web服务器和php符合Symfony的要求。使用这个地址检查配置：

    http://localhost/config.php

如果出现任何问题，在继续之前先解决他们。


**配置权限**

一个常见问题是app/cache和app/logs目录必须对web服务器和命令行用户都是可写的。
在UNIX系统中，如果web服务器的用户和命令行用户不是同一个用户，你可以运行一次下面的命令保证权限被正确的配置。

1. 在支持chmod +a的系统上使用acl

很多系统支持你使用chmod +a命令。先尝试这个命令，如果出错了，尝试下面的方法。
这个方法让找到你的web服务器用户，然后把它设置为APACHEUSER:

    rm -rf app/cache/*
    $ rm -rf app/logs/*

    $ APACHEUSER=`ps aux | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data' | grep -v root | head -1 | cut -d\  -f1`
    $ sudo chmod +a "$APACHEUSER allow delete,write,append,file_inherit,directory_inherit" app/cache app/logs
    $ sudo chmod +a "`whoami` allow delete,write,append,file_inherit,directory_inherit" app/cache app/logs

2. 在不支持chmod +a的系统上使用acl

一些系统不支持chmod +a，但是支持其他的工具叫做setfacl。
你需要[支持ACL](https://help.ubuntu.com/community/FilePermissionsACLs)，以及在使用前安装它。
这个方法用命令获取你的web服务器用户，然后设置为APACHEUSER:

    $ APACHEUSER=`ps aux | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data' | grep -v root | head -1 | cut -d\  -f1`
    $ sudo setfacl -R -m u:"$APACHEUSER":rwX -m u:`whoami`:rwX app/cache app/logs
    $ sudo setfacl -dR -m u:"$APACHEUSER":rwX -m u:`whoami`:rwX app/cache app/logs

3. 不使用ACL

如果你没有权限改变目录的ACL，你需要改变umask以使缓存和日志目录是组可写的。
为了实现这点，把下面的命令放在app/console，web/app.php和web/app_dev.php前面：

    umask(0002); // This will let the permissions be 0775

    // or

    umask(0000); // This will let the permissions be 0777



当所有事情都好了，点击“Go to the Welcome page”去请求你第一个真的Symfony2页面：

http://localhost/app_dev.php/

Symfony2会欢迎和祝贺你到目前为止的努力。
<img src="https://github.com/Filix/sf_zh/blob/master/src/Filix/TheBookBundle/Resources/public/images/the_book/v240/c3_1.jpg?raw=true" />

想要获取精简的url，你要更改web服务器或虚拟主机的根目录到symfony/web目录。
尽管在开发环境这不是必须的，但是推荐在你的应用部署到生成环境时这么做。
想获取配置web服务器根目录的信息，阅读[配置web服务器](http://symfony.com/doc/current/cookbook/configuration/web_server_configuration.html)或查阅你的web服务器的官方文档：[Apache](http://httpd.apache.org/docs/current/mod/core.html#documentroot)，[Nginx](http://wiki.nginx.org/Symfony)。


##开始开发

现在，你已经有了一个功能丰富的Symfony2应用，你可以开始开发了。
你的版本可能包含一些相同的代码 -- 检查README.md文件，了解你的版本中包含了哪些相同的代码。

如果你是刚接触Symfony，看看[创建页面](http://symfonycn.com/the_book/v2.4.0/creating-pages-in-symfony2)，你会学到如何创建页面、
改变配置和在你的新应用中的所有事情。

也要保证查看[CookBook](http://symfony.com/doc/current/cookbook/index.html)，他介绍了Symfony中一些特定问题的解决方法。

如果你想移除demo代码，看看cookbook中的文章[如何移除AcmeDemoBundle](http://symfony.com/doc/current/cookbook/bundles/remove.html)

##使用代码控制工具

如果你在使用像git或svn这样的版本控制工具，你可以像往常一样配置你的版本控制、提交你的项目。
Symfony标准发行版是你的新项目的第一个版本。

###忽略vendor目录

如果你下载的是“without vendor”版，你可以安全的忽略vendor目录，不把它提交到版本库。
如果使用git，创建一个.gitignore文件，然后添加：

    /vendor/

现在vendor目录不会被提交到版本库。这很好（事实上很非常好）。
因为当其他人clone或者检出这个项目，他们只要运行php composer.phar install脚本就能安装所有依赖的库。


###文档下载

Doc: [Word版下载](http://pan.baidu.com/s/1pJ2jAcz)

Pdf: [Pdf版下载](http://pan.baidu.com/s/1jGjwVXW)

<a style="float:left; display: block; width:49%; padding: 20px 0px;; text-decoration: none; font-size: 18px; border: 1px solid #ccc; text-align:center; margin: 50px 0px;" href="http://symfonycn.com/the_book/v2.4.0/symfony2-versus-flat-php">上一篇： Symfony2 VS 原生的PHP</a><a style="float:left; display: block; width:50%; padding: 20px 0px;; text-decoration: none; font-size: 18px; border: 1px solid #ccc; text-align:center; margin: 50px 0px;" href="http://symfonycn.com/the_book/v2.4.0/creating-pages-in-symfony2">下一篇： 在Symfony2中创建页面</a>