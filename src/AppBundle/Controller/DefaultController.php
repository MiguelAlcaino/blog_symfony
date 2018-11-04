<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Post;
use AppBundle\Form\PostType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager(); // el manager permite interactuar con la base de datos y sus entidades

        $posts = $em->getRepository(Post::class)->getAllSourtedByCreated();//se le pasa el nombre el nombre largo de la clase

        $name = $request->query->get('name', 'mete tu nombre');

        return $this->render('@App/Post/index.html.twig', [
            'posts' => $posts,
            'name' => $name

        ]);// para renderiar la vista
    }

    /**
     * @Route("/post/new/", name="post_new")
     */
    public function newPostAction()
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post, [
            'action' => $this->generateUrl('post_create'),
            'method' => 'POST'
        ]);

        return $this->render('@App/Post/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/post/create/", name="post_create")
     */
    public function createPostAction(Request $request)
    {
        $post = new Post();


        $form = $this->createForm(PostType::class, $post, [
            'action' => $this->generateUrl('post_create'),
            'method' => 'POST'
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Post $post */
            $post = $form->getData();
            $post->setCreatedAt(new \DateTime());//todo lo que tenga un backslash esta dentro del namespace global

            $em = $this->getDoctrine()->getManager();
            $em->persist($post);
            $em->flush();


            return $this->redirectToRoute('post_show', ['id' => $post->getId()]);
        }

        return $this->render('@App/Post/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/post/show/{id}", name="post_show")
     */
    public function showPostAction(Request $request, $id){
        $em = $this->getDoctrine()->getManager();

        $post = $em->getRepository(Post::class)->find($id);

        return $this->render('@App/Post/show.html.twig', [
           'post'=>$post
        ]);

    }
}
