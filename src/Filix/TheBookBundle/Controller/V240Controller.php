<?php

namespace Filix\TheBookBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route("/v2.4.0")
 */
class V240Controller extends BaseController
{
    /**
     * @Route("/",name="index_v240")
     */
    public function indexAction()
    {
        return $this->render('FilixTheBookBundle:Default:v240.html.twig');
    }
    
    /**
     * @Route("/about",name="about_v240")
     */
    public function aboutAction()
    {
        return $this->renderMarkDown('V240/about.md');
    }
    
    /**
     * @Route("/symfony2-and-http-fundamentals",name="c1_v240")
     */
    public function chapter1Action(){
        return $this->renderMarkDown('V240/chapter1.md');
    }
    
    /**
     * @Route("/symfony2-versus-flat-php",name="c2_v240")
     */
    public function chapter2Action(){
        return $this->renderMarkDown('V240/chapter2.md');
    }
    
    /**
     * @Route("/installing-and-configuring-symfony",name="c3_v240")
     */
    public function chapter3Action(){
        return $this->renderMarkDown('V240/chapter3.md');
    }
    
    /**
     * @Route("/creating-pages-in-symfony2",name="c4_v240")
     */
    public function chapter4Action(){
        return $this->renderMarkDown('V240/chapter4.md');
    }
    
    /**
     * @Route("/controller",name="c5_v240")
     */
    public function chapter5Action(){
        return $this->renderMarkDown('V240/empty.md');
    }
    
    /**
     * @Route("/routing",name="c6_v240")
     */
    public function chapter6Action(){
        return $this->renderMarkDown('V240/empty.md');
    }
    
    /**
     * @Route("/creating-and-using-templates",name="c7_v240")
     */
    public function chapter7Action(){
        return $this->renderMarkDown('V240/empty.md');
    }
    
    /**
     * @Route("/databases-and-doctrine",name="c8_v240")
     */
    public function chapter8Action(){
        return $this->renderMarkDown('V240/empty.md');
    }
    
    /**
     * @Route("/databases-and-propel",name="c9_v240")
     */
    public function chapter9Action(){
        return $this->renderMarkDown('V240/empty.md');
    }
    
    /**
     * @Route("/testing",name="c10_v240")
     */
    public function chapter10Action(){
        return $this->renderMarkDown('V240/empty.md');
    }
    
    /**
     * @Route("/validation",name="c11_v240")
     */
    public function chapter11Action(){
        return $this->renderMarkDown('V240/empty.md');
    }
    
    /**
     * @Route("/forms",name="c12_v240")
     */
    public function chapter12Action(){
        return $this->renderMarkDown('V240/empty.md');
    }
    
    /**
     * @Route("/security",name="c13_v240")
     */
    public function chapter13Action(){
        return $this->renderMarkDown('V240/empty.md');
    }
    
    /**
     * @Route("/http-cache",name="c14_v240")
     */
    public function chapter14Action(){
        return $this->renderMarkDown('V240/empty.md');
    }
    
    /**
     * @Route("/translations",name="c15_v240")
     */
    public function chapter15Action(){
        return $this->renderMarkDown('V240/empty.md');
    }
    
    /**
     * @Route("/service-container",name="c16_v240")
     */
    public function chapter16Action(){
        return $this->renderMarkDown('V240/empty.md');
    }
    
    /**
     * @Route("/performance",name="c17_v240")
     */
    public function chapter17Action(){
        return $this->renderMarkDown('V240/empty.md');
    }
    
    /**
     * @Route("/internals",name="c18_v240")
     */
    public function chapter18Action(){
        return $this->renderMarkDown('V240/empty.md');
    }
    
    /**
     * @Route("/the-symfony-stable-api",name="c19_v240")
     */
    public function chapter19Action(){
        return $this->renderMarkDown('V240/empty.md');
    }
}
