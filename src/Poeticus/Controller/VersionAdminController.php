<?php

namespace Poeticus\Controller;

use Poeticus\Entity\Version;
use Poeticus\Form\Type\VersionType;
use Silex\Application;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Response;

class VersionAdminController
{
	public function indexAction(Request $request, Application $app)
	{
		return $app['twig']->render('Version/index.html.twig');
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
		
		$entities = $app['repository.version']->getDatatablesForIndex($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch);
		$iTotal = $app['repository.version']->getDatatablesForIndex($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, true);

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
			$row[] = $entity->getVersionNumber();
			$row[] = $entity->getReleaseDate();
			
			$show = $app['url_generator']->generate('versionadmin_show', array('id' => $entity->getId()));
			$edit = $app['url_generator']->generate('versionadmin_edit', array('id' => $entity->getId()));
			
			$row[] = '<a href="'.$show.'" alt="Show">Lire</a> - <a href="'.$edit.'" alt="Edit">Modifier</a>';

			$output['aaData'][] = $row;
		}

		$response = new Response(json_encode($output));
		$response->headers->set('Content-Type', 'application/json');

		return $response;
	}

    public function newAction(Request $request, Application $app)
    {
		$entity = new Version();
        $form = $this->createForm($app, $entity);

		return $app['twig']->render('Version/new.html.twig', array('form' => $form->createView()));
    }
	
	public function createAction(Request $request, Application $app)
	{
		$entity = new Version();
        $form = $this->createForm($app, $entity);
		$form->handleRequest($request);
		
		$this->checkForDoubloon($entity, $form, $app);
		if($entity->getFile() == null)
			$form->get("file")->addError(new FormError('Ce champ ne peut pas être vide'));

		if($form->isValid())
		{
			$image = uniqid()."_".$entity->getFile()->getClientOriginalName();
			$entity->getFile()->move("photo/version/", $image);
			$entity->setFile($image);
			$id = $app['repository.version']->save($entity);

			$redirect = $app['url_generator']->generate('versionadmin_show', array('id' => $id));

			return $app->redirect($redirect);
		}
		
		return $app['twig']->render('Version/new.html.twig', array('form' => $form->createView()));
	}
	
	public function showAction(Request $request, Application $app, $id)
	{
		$entity = $app['repository.version']->find($id, true);

		return $app['twig']->render('Version/show.html.twig', array('entity' => $entity));
	}
	
	public function editAction(Request $request, Application $app, $id)
	{
		$entity = $app['repository.version']->find($id);
		$form = $this->createForm($app, $entity);
	
		return $app['twig']->render('Version/edit.html.twig', array('form' => $form->createView(), 'entity' => $entity));
	}

	public function updateAction(Request $request, Application $app, $id)
	{
		$entity = $app['repository.version']->find($id);
		$currentImage = $entity->getFile();
		$form = $this->createForm($app, $entity);
		$form->handleRequest($request);
		
		$this->checkForDoubloon($entity, $form, $app);
		
		if($form->isValid())
		{
			if(!is_null($entity->getFile()))
			{
				$image = uniqid()."_".$entity->getFile()->getClientOriginalName();
				$entity->getPhoto()->move("photo/version/", $image);
			}
			else
				$image = $currentImage;

			$entity->setPhoto($image);
			$id = $app['repository.version']->save($entity, $id);

			$redirect = $app['url_generator']->generate('versionadmin_show', array('id' => $id));

			return $app->redirect($redirect);
		}
	
		return $app['twig']->render('Version/edit.html.twig', array('form' => $form->createView(), 'entity' => $entity));
	}
	
	private function createForm($app, $entity)
	{
		$form = $app['form.factory']->create(VersionType::class, $entity);
		
		return $form;
	}
	
	private function checkForDoubloon($entity, $form, $app)
	{
		if($entity->getVersionNumber() != null)
		{
			$checkForDoubloon = $app['repository.version']->checkForDoubloon($entity);

			if($checkForDoubloon > 0)
				$form->get("versionNumber")->addError(new FormError('Cette entrée existe déjà !'));
		}
	}
}