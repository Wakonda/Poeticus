<?php

namespace Poeticus\Controller;

use Poeticus\Entity\Collection;
use Poeticus\Form\Type\CollectionType;
use Silex\Application;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Response;

class CollectionAdminController
{
	public function indexAction(Request $request, Application $app)
	{
		return $app['twig']->render('Collection/index.html.twig');
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
		
		$entities = $app['repository.collection']->getDatatablesForIndex($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch);
		$iTotal = $app['repository.collection']->getDatatablesForIndex($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, true);

		$output = array(
			"sEcho" => $request->query->get('sEcho'),
			"iTotalRecords" => $iTotal,
			"iTotalDisplayRecords" => $iTotal,
			"aaData" => array()
		);
		
		$translator = $app['translator'];
		
		foreach($entities as $entity)
		{
			$row = array();
			$row[] = $entity->getId();
			$row[] = $entity->getTitle();
			
			$show = $app['url_generator']->generate('collectionadmin_show', array('id' => $entity->getId()));
			$edit = $app['url_generator']->generate('collectionadmin_edit', array('id' => $entity->getId()));
			
			$row[] = '<a href="'.$show.'" alt="Show">'.$translator->trans('admin.index.Read').'</a> - <a href="'.$edit.'" alt="Edit">'.$translator->trans('admin.index.Update').'</a>';

			$output['aaData'][] = $row;
		}

		$response = new Response(json_encode($output));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}

    public function newAction(Request $request, Application $app)
    {
		$entity = new Collection();
        $form = $this->createForm($app, $entity);

		return $app['twig']->render('Collection/new.html.twig', array('form' => $form->createView()));
    }
	
	public function createAction(Request $request, Application $app)
	{
		$entity = new Collection();
        $form = $this->createForm($app, $entity);
		$form->handleRequest($request);
		
		$this->checkForDoubloon($entity, $form, $app);
		$translator = $app['translator'];
		
		if($entity->getImage() == null)
			$form->get("image")->addError(new FormError($translator->trans("This value should not be blank.", array(), "validators")));

		if($form->isValid())
		{
			$image = uniqid()."_".$entity->getImage()->getClientOriginalName();
			$entity->getImage()->move("photo/collection/", $image);
			$entity->setImage($image);
			$id = $app['repository.collection']->save($entity);

			$redirect = $app['url_generator']->generate('collectionadmin_show', array('id' => $id));

			return $app->redirect($redirect);
		}
		
		return $app['twig']->render('Collection/new.html.twig', array('form' => $form->createView()));
	}
	
	public function showAction(Request $request, Application $app, $id)
	{
		$entity = $app['repository.collection']->find($id, true);
	
		return $app['twig']->render('Collection/show.html.twig', array('entity' => $entity));
	}
	
	public function editAction(Request $request, Application $app, $id)
	{
		$entity = $app['repository.collection']->find($id);
		$form = $this->createForm($app, $entity);
	
		return $app['twig']->render('Collection/edit.html.twig', array('form' => $form->createView(), 'entity' => $entity));
	}

	public function updateAction(Request $request, Application $app, $id)
	{
		$entity = $app['repository.collection']->find($id);
		$currentImage = $entity->getImage();
		$form = $this->createForm($app, $entity);
		$form->handleRequest($request);
		
		$this->checkForDoubloon($entity, $form, $app);
		
		if($form->isValid())
		{
			if(!is_null($entity->getImage()))
			{
				$image = uniqid()."_".$entity->getImage()->getClientOriginalName();
				$entity->getImage()->move("photo/collection/", $image);
			}
			else
				$image = $currentImage;

			$entity->setImage($image);
			$id = $app['repository.collection']->save($entity, $id);

			$redirect = $app['url_generator']->generate('collectionadmin_show', array('id' => $id));

			return $app->redirect($redirect);
		}
	
		return $app['twig']->render('Collection/edit.html.twig', array('form' => $form->createView(), 'entity' => $entity));
	}
	
	private function createForm($app, $entity)
	{
		$biographyForms = $app['repository.biography']->findAllForChoice();
		$languageForms = $app['repository.language']->findAllForChoice();
		
		$form = $app['form.factory']->create(CollectionType::class, $entity, array('biographies' => $biographyForms, 'languages' => $languageForms));
		
		return $form;
	}

	private function checkForDoubloon($entity, $form, $app)
	{
		$checkForDoubloon = $app['repository.collection']->checkForDoubloon($entity);

		if($checkForDoubloon > 0)
			$form->get("title")->addError(new FormError('Cette entrée existe déjà !'));
	}
}