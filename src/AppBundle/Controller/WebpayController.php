<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Post;
use http\Env\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * Post controller.
 *
 * @Route("webpay")
 */
class WebpayController extends Controller
{
    /**
     * Lists all post entities.
     *
     * @Route("/", name="webpay_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        //$em = $this->getDoctrine()->getManager();

        //$posts = $em->getRepository('AppBundle:Post')->findAll();

        return $this->render('@App/webpay/index.html.twig', array(
            'prueba' => 'prueba',
        ));
    }

    /**
     * @Route("/pagar", name="webpay_pagar")
     * @Method("POST")
     *
     */
    public function pagarAction(Post $post){

        var_dump('gola');
        die();

        return $this->redirectToRoute('post_edit', array('id' => 3));



    }
}
