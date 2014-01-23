<?php
namespace Filix\TheBookBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Filix\TheBookBundle\Exception\MdNotFoundException;
/**
 * BaseController
 *
 * @author Filix
 */
class BaseController extends Controller
{
    public function renderMarkDown($markdown)
    {
        $md  = $this->get('kernel')->getRootDir().'/../src/Filix/TheBookBundle/Resources/markdown/' . $markdown;
        if(!file_exists($md)){
            throw new MdNotFoundException(sprintf("%s not found in src/Filix/TheBookBundle/Resources/markdown dir", $markdown));
        }
        $content = $this->container->get('markdown.parser')->transformMarkdown(file_get_contents($md));
        return $this->render("FilixTheBookBundle:Default:index.html.twig", array('md_content' => $content));
    }
    
    public function get($key){
        return $this->container->get($key);
    }
}
