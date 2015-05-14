<?php

namespace Poeticus\Controller;

use Poeticus\Entity\Biography;
use Poeticus\Form\Type\BiographyType;
use Silex\Application;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Response;

class BiographyAdminController
{
	public function indexAction(Request $request, Application $app)
	{
		return $app['twig']->render('Biography/index.html.twig');
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
		
		$entities = $app['repository.biography']->getDatatablesForIndex($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch);
		$iTotal = $app['repository.biography']->getDatatablesForIndex($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, true);

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
			
			$show = $app['url_generator']->generate('biographyadmin_show', array('id' => $entity->getId()));
			$edit = $app['url_generator']->generate('biographyadmin_edit', array('id' => $entity->getId()));
			
			$row[] = '<a href="'.$show.'" alt="Show">Lire</a> - <a href="'.$edit.'" alt="Edit">Modifier</a>';

			$output['aaData'][] = $row;
		}

		$response = new Response(json_encode($output));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}

    public function newAction(Request $request, Application $app)
    {
		$entity = new Biography();
        $form = $this->createForm($app, $entity);

		return $app['twig']->render('Biography/new.html.twig', array('form' => $form->createView()));
    }
	
	public function createAction(Request $request, Application $app)
	{
		$entity = new Biography();
        $form = $this->createForm($app, $entity);
		$form->handleRequest($request);
		
		$this->checkForDoubloon($entity, $form, $app);
		if($entity->getPhoto() == null)
			$form->get("photo")->addError(new FormError('Ce champ ne peut pas être vide'));

		if($form->isValid())
		{
			$image = uniqid()."_".$entity->getPhoto()->getClientOriginalName();
			$entity->getPhoto()->move("photo/biography/", $image);
			$entity->setPhoto($image);
			$id = $app['repository.biography']->save($entity);

			$redirect = $app['url_generator']->generate('biographyadmin_show', array('id' => $id));

			return $app->redirect($redirect);
		}
		
		return $app['twig']->render('Biography/new.html.twig', array('form' => $form->createView()));
	}
	
	public function showAction(Request $request, Application $app, $id)
	{
		$entity = $app['repository.biography']->find($id, true);
	
		return $app['twig']->render('Biography/show.html.twig', array('entity' => $entity));
	}
	
	public function editAction(Request $request, Application $app, $id)
	{
		$entity = $app['repository.biography']->find($id);
		$form = $this->createForm($app, $entity);
	
		return $app['twig']->render('Biography/edit.html.twig', array('form' => $form->createView(), 'entity' => $entity));
	}

	public function updateAction(Request $request, Application $app, $id)
	{
		$entity = $app['repository.biography']->find($id);
		$currentImage = $entity->getPhoto();
		$form = $this->createForm($app, $entity);
		$form->handleRequest($request);
		
		$this->checkForDoubloon($entity, $form, $app);
		
		if($form->isValid())
		{
			if(!is_null($entity->getPhoto()))
			{
				$image = uniqid()."_".$entity->getPhoto()->getClientOriginalName();
				$entity->getPhoto()->move("photo/biography/", $image);
			}
			else
				$image = $currentImage;

			$entity->setPhoto($image);
			$id = $app['repository.biography']->save($entity, $id);

			$redirect = $app['url_generator']->generate('biographyadmin_show', array('id' => $id));

			return $app->redirect($redirect);
		}
	
		return $app['twig']->render('Biography/edit.html.twig', array('form' => $form->createView(), 'entity' => $entity));
	}
	
	private function createForm($app, $entity)
	{
		$countryForms = $app['repository.country']->findAllForChoice();
		
		$form = $app['form.factory']->create(new BiographyType($countryForms), $entity);
		
		return $form;
	}
	
	private function checkForDoubloon($entity, $form, $app)
	{
		if($entity->getTitle() != null)
		{
			$checkForDoubloon = $app['repository.biography']->checkForDoubloon($entity);

			if($checkForDoubloon > 0)
				$form->get("title")->addError(new FormError('Cette entrée existe déjà !'));
		}
	}
}