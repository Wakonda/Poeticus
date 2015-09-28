<?php

namespace Poeticus\Controller;

use Silex\Application;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Response;

use Poeticus\Entity\Comment;
use Poeticus\Form\Type\CommentType;

class CommentController
{
    public function indexAction(Request $request, Application $app, $poemId)
    {
		$entity = new Comment();
        $form = $app['form.factory']->create(new CommentType(), $entity);
		
        return $app['twig']->render('Comment/index.html.twig', array('poemId' => $poemId, 'form' => $form->createView()));
    }
	
	public function createAction(Request $request, Application $app, $poemId)
	{
		$entity = new Comment();
        $form = $app['form.factory']->create(new CommentType(), $entity);
		$form->handleRequest($request);

		if($form->isValid())
		{
			$user = $app['security']->getToken()->getUser();
			$user = $app['repository.user']->findByUsernameOrEmail($user->getUsername());
			
			$entity->setUser($user);
			
			$poem = $app['repository.poem']->find($poemId);
			$entity->setPoem($poemId);
			
			$app['repository.comment']->save($entity);
			
			$entities = $app['repository.comment']->findAll();

			$error = "";
		}
		else
			$error = "Ce champ ne doit pas être vide";	
		
		$params = $this->getParametersComment($request, $app);
		
		$response = new Response(json_encode(array("content" => $app['twig']->render('Comment/list.html.twig', $params), "error" => $error)));
		$response->headers->set('Content-Type', 'application/json');

		return $response;
	}
	
	public function loadCommentAction(Request $request, Application $app)
	{
		return $app['twig']->render('Comment/list.html.twig', $this->getParametersComment($request, $app));
	}
	
	private function getParametersComment($request, $app)
	{
		$max_comment_by_page = 3;
		$page = $request->query->get("page");
		$totalComments = $app['repository.comment']->countAllComments();
		$number_pages = ceil($totalComments / $max_comment_by_page);
		$first_message_to_display = ($page - 1) * $max_comment_by_page;
		
		$entities = $app['repository.comment']->displayComments($max_comment_by_page, $first_message_to_display);
		
		return array("entities" => $entities, "page" => $page, "number_pages" => $number_pages);
	}
}
