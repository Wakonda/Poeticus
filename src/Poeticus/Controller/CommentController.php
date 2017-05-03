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
        $form = $app['form.factory']->create(CommentType::class, $entity);
		$app['generic_function']->setLocaleTwigRenderController();

        return $app['twig']->render('Comment/index.html.twig', array('poemId' => $poemId, 'form' => $form->createView()));
    }
	
	public function createAction(Request $request, Application $app, $poemId)
	{
		$entity = new Comment();
        $form = $app['form.factory']->create(CommentType::class, $entity);
		$form->handleRequest($request);

		$user = $app['security.token_storage']->getToken()->getUser();
		
		if(!empty($user) and is_object($user))
			$user = $app['repository.user']->findByUsernameOrEmail($user->getUsername());
		else
		{
			$form->get("text")->addError(new FormError($app["translator"]->trans("comment.field.YouMustBeLoggedInToWriteAComment")));
		}

		if($form->isValid())
		{
			$entity->setUser($user);
			$entity->setPoem($poemId);
			
			$app['repository.comment']->save($entity);
			
			$entities = $app['repository.comment']->findAll();
			$form = $app['form.factory']->create(CommentType::class, new Comment());
		}

		$params = $this->getParametersComment($request, $app, $poemId);

		return $app['twig']->render('Comment/form.html.twig', array("form" => $form->createView()));
	}
	
	public function loadCommentAction(Request $request, Application $app, $poemId)
	{
		return $app['twig']->render('Comment/list.html.twig', $this->getParametersComment($request, $app, $poemId));
	}
	
	private function getParametersComment($request, $app, $poemId)
	{
		$max_comment_by_page = 7;
		$page = $request->query->get("page");
		$totalComments = $app['repository.comment']->countAllComments($poemId);
		$number_pages = ceil($totalComments / $max_comment_by_page);
		$first_message_to_display = ($page - 1) * $max_comment_by_page;
		
		$entities = $app['repository.comment']->displayComments($poemId, $max_comment_by_page, $first_message_to_display);
		
		return array("entities" => $entities, "page" => $page, "number_pages" => $number_pages);
	}
}
