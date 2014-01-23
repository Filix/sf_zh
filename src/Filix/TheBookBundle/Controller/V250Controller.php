<?php
namespace Filix\TheBookBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route("/v2.5.0")
 */
class V250Controller extends BaseController
{
    /**
     * @Route("/",name="index_v250")
     */
    public function indexAction()
    {
        return $this->render('FilixTheBookBundle:Default:v240.html.twig');
    }
    
    /**
     * @Route("/about",name="about_v250")
     */
    public function aboutAction()
    {
        return $this->renderMarkDown('V240/about.md');
    }
    
    /**
     * @Route("/symfony2-and-http-fundamentals",name="c1_v250")
     */
    public function chapter1Action(){
        return $this->renderMarkDown('V240/chapter1.md');
    }
}
