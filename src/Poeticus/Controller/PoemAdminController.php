<?php

namespace Poeticus\Controller;

use Poeticus\Entity\Poem;
use Poeticus\Form\Type\PoemType;
use Poeticus\Form\Type\PoemFastType;
use Silex\Application;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Response;

require_once __DIR__.'/../../simple_html_dom.php';

class PoemAdminController
{
	public function indexAction(Request $request, Application $app)
	{
		return $app['twig']->render('Poem/index.html.twig');
	}

	public function indexDatatablesAction(Request $request, Application $app)
	{
		$iDisplayStart = $request->query->get('iDisplayStart');
		$iDisplayLength = $request->query->get('iDisplayLength');
		$sSearch = $request->query->get('sSearch');

		$sortByColumn = array();
		$sortDirColumn = array();
			
		for($i=0 ; $i<intval($request->query->get('iSortingCols')); $i++)
		{
			if ($request->query->get('bSortable_'.intval($request->query->get('iSortCol_'.$i))) == "true" )
			{
				$sortByColumn[] = $request->query->get('iSortCol_'.$i);
				$sortDirColumn[] = $request->query->get('sSortDir_'.$i);
			}
		}
		
		$entities = $app['repository.poem']->getDatatablesForIndex($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch);
		$iTotal = $app['repository.poem']->getDatatablesForIndex($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, true);

		$output = array(
			"sEcho" => $request->query->get('sEcho'),
			"iTotalRecords" => $iTotal,
			"iTotalDisplayRecords" => $iTotal,
			"aaData" => array()
		);
		
		foreach($entities as $entity)
		{
			$row = array();
			$row[] = $entity->getId();
			$row[] = $entity->getTitle();
			
			$show = $app['url_generator']->generate('poemadmin_show', array('id' => $entity->getId()));
			$edit = $app['url_generator']->generate('poemadmin_edit', array('id' => $entity->getId()));
			
			$row[] = '<a href="'.$show.'" alt="Show">Lire</a> - <a href="'.$edit.'" alt="Edit">Modifier</a>';

			$output['aaData'][] = $row;
		}

		$response = new Response(json_encode($output));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}

    public function newAction(Request $request, Application $app)
    {
		$entity = new Poem();
        $form = $this->createForm($app, $entity);

		return $app['twig']->render('Poem/new.html.twig', array('form' => $form->createView()));
    }
	
	public function createAction(Request $request, Application $app)
	{
		$entity = new Poem();
        $form = $this->createForm($app, $entity);
		$form->handleRequest($request);

		$this->checkForDoubloon($entity, $form, $app);
		
		$userForms = $app['repository.user']->findAllForChoice();

		if(($entity->isBiography() and $entity->getBiography() == null) or ($entity->isUser() and $entity->getUser() == null))
			$form->get($entity->getAuthorType())->addError(new FormError('Ce champ ne peut pas être vide'));
		
		if($form->isValid())
		{
			$entity->setText('<p>'.nl2br($entity->getText()).'</p>');
			$id = $app['repository.poem']->save($entity);

			$redirect = $app['url_generator']->generate('poemadmin_show', array('id' => $id));

			return $app->redirect($redirect);
		}
		
		return $app['twig']->render('Poem/new.html.twig', array('form' => $form->createView()));
	}
	
	public function showAction(Request $request, Application $app, $id)
	{
		$entity = $app['repository.poem']->find($id, true);
	
		return $app['twig']->render('Poem/show.html.twig', array('entity' => $entity));
	}
	
	public function editAction(Request $request, Application $app, $id)
	{
		$entity = $app['repository.poem']->find($id);
		$form = $this->createForm($app, $entity);
	
		return $app['twig']->render('Poem/edit.html.twig', array('form' => $form->createView(), 'entity' => $entity));
	}

	public function updateAction(Request $request, Application $app, $id)
	{
		$entity = $app['repository.poem']->find($id);
		$form = $this->createForm($app, $entity);
		$form->handleRequest($request);
		
		$this->checkForDoubloon($entity, $form, $app);
		
		if(($entity->isBiography() and $entity->getBiography() == null) or ($entity->isUser() and $entity->getUser() == null))
			$form->get($entity->getAuthorType())->addError(new FormError('Ce champ ne peut pas être vide'));
		
		if($form->isValid())
		{
			$id = $app['repository.poem']->save($entity, $id);

			$redirect = $app['url_generator']->generate('poemadmin_show', array('id' => $id));

			return $app->redirect($redirect);
		}
	
		return $app['twig']->render('Poem/edit.html.twig', array('form' => $form->createView(), 'entity' => $entity));
	}
	
	public function newFastAction(Request $request, Application $app)
	{
		$entity = new Poem();
		
		$biographyForms = $app['repository.biography']->findAllForChoice();
		$countryForms = $app['repository.country']->findAllForChoice();
		$collectionForms = $app['repository.collection']->findAllForChoice();
		
		$form = $app['form.factory']->create(new PoemFastType($biographyForms, $countryForms, $collectionForms), $entity);
	
		return $app['twig']->render('Poem/fast.html.twig', array('form' => $form->createView(), 'entity' => $entity));
	}

	public function addFastAction(Request $request, Application $app)
	{
		$entity = new Poem();
		$biographyForms = $app['repository.biography']->findAllForChoice();
		$countryForms = $app['repository.country']->findAllForChoice();
		$collectionForms = $app['repository.collection']->findAllForChoice();
		
		$form = $app['form.factory']->create(new PoemFastType($biographyForms, $countryForms, $collectionForms), $entity);
	
		$form->handleRequest($request);
		
		$req = $request->request->get('poemfast');

		if(!empty($req["url"]) and !filter_var($req["url"], FILTER_VALIDATE_URL))
			$form->get("url")->addError(new FormError('L\'URL ne semble pas être valide !'));

		if($form->isValid())
		{
			$content = file_get_html($req["url"]);
			$title = $content->find('h1'); 
			$text = $content->find('p[class=last]'); 

			$entity->setTitle(utf8_encode(html_entity_decode($title[0]->plaintext)));
			$entity->setText(str_replace(' class="last"', '', $text[0]->outertext));
			$entity->setAuthorType("biography");
			
			$id = $app['repository.poem']->save($entity);

			$redirect = $app['url_generator']->generate('poemadmin_show', array('id' => $id));

			return $app->redirect($redirect);
		}
	
		return $app['twig']->render('Poem/fast.html.twig', array('form' => $form->createView(), 'entity' => $entity));
	}
	
	public function listSelectedBiographyAction(Request $request, Application $app)
	{
		$id = $request->request->get("id");
		
		if($id != "")
		{
			$entity = $app['repository.biography']->find($id);

			$collections = $app['repository.collection']->findAllByAuthor($id);
			$collectionArray = array();
			
			foreach($collections as $collection)
			{
				$collectionArray[] = array("id" => $collection["id"], "title" => $collection["title"], "releaseDate" => $collection["releasedDate"]);
			}

			$country = $app['repository.country']->find($entity->getCountry());
			
			if(empty($country))
				$countryText = null;
			else
				$countryText = $country->getId();
				
			$finalArray = array("collections" => $collectionArray, "country" => $countryText);
		}
		else
			$finalArray = array("collections" => "", "country" => "");
			
		$response = new Response(json_encode($finalArray));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}

	public function listSelectedCollectionAction(Request $request, Application $app)
	{
		$id = $request->request->get("id");
		
		if($id != "")
		{
			$entity = $app['repository.collection']->find($id);
			// die(var_dump($entity));	
			$finalArray = array("releasedDate" => $entity->getReleasedDate());
		}
		else
			$finalArray = array("releasedDate" => "");
			
		$response = new Response(json_encode($finalArray));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}
	
	private function createForm($app, $entity)
	{
		$poeticForms = $app['repository.poeticform']->findAllForChoice();
		$userForms = $app['repository.user']->findAllForChoice();
		$biographyForms = $app['repository.biography']->findAllForChoice();
		$countryForms = $app['repository.country']->findAllForChoice();
		$collectionForms = $app['repository.collection']->findAllForChoice();
		
		$form = $app['form.factory']->create(new PoemType($poeticForms, $userForms, $biographyForms, $countryForms, $collectionForms), $entity);
		
		return $form;
	}


	private function checkForDoubloon($entity, $form, $app)
	{
		if($entity->getTitle() != null)
		{
			$checkForDoubloon = $app['repository.poem']->checkForDoubloon($entity);

			if($checkForDoubloon > 0)
				$form->get("title")->addError(new FormError('Cette entrée existe déjà !'));
		}
	}
}