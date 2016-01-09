<?php

namespace Poeticus\Controller;

use Poeticus\Entity\PoeticForm;
use Poeticus\Form\Type\PoeticFormType;
use Silex\Application;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Response;

class PoeticFormAdminController
{
	public function indexAction(Request $request, Application $app)
	{
		return $app['twig']->render('PoeticForm/index.html.twig');
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
		
		$entities = $app['repository.poeticform']->getDatatablesForIndex($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch);
		$iTotal = $app['repository.poeticform']->getDatatablesForIndex($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, true);

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
			
			$show = $app['url_generator']->generate('poeticformadmin_show', array('id' => $entity->getId()));
			$edit = $app['url_generator']->generate('poeticformadmin_edit', array('id' => $entity->getId()));
			
			$row[] = '<a href="'.$show.'" alt="Show">Lire</a> - <a href="'.$edit.'" alt="Edit">Modifier</a>';

			$output['aaData'][] = $row;
		}

		$response = new Response(json_encode($output));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}

    public function newAction(Request $request, Application $app)
    {
		$entity = new PoeticForm();
        $form = $app['form.factory']->create(new PoeticFormType(), $entity);

		return $app['twig']->render('PoeticForm/new.html.twig', array('form' => $form->createView()));
    }
	
	public function createAction(Request $request, Application $app)
	{
		$entity = new PoeticForm();
        $form = $app['form.factory']->create(new PoeticFormType(), $entity);
		$form->handleRequest($request);

		if($entity->getImage() == null)
			$form->get("image")->addError(new FormError('Ce champ ne peut pas être vide'));
		
		if($form->isValid())
		{
			$image = uniqid()."_".$entity->getImage()->getClientOriginalName();
			$entity->getImage()->move("photo/poeticform/", $image);
			$entity->setImage($image);
			$id = $app['repository.poeticform']->save($entity);

			$redirect = $app['url_generator']->generate('poeticformadmin_show', array('id' => $id));

			return $app->redirect($redirect);
		}
		
		return $app['twig']->render('PoeticForm/new.html.twig', array('form' => $form->createView()));
	}
	
	public function showAction(Request $request, Application $app, $id)
	{
		$entity = $app['repository.poeticform']->find($id);
	
		return $app['twig']->render('PoeticForm/show.html.twig', array('entity' => $entity));
	}
	
	public function editAction(Request $request, Application $app, $id)
	{
		$entity = $app['repository.poeticform']->find($id);
		$form = $app['form.factory']->create(new PoeticFormType(), $entity);
	
		return $app['twig']->render('PoeticForm/edit.html.twig', array('form' => $form->createView(), 'entity' => $entity));
	}

	public function updateAction(Request $request, Application $app, $id)
	{
		$entity = $app['repository.poeticform']->find($id);
		$currentImage = $entity->getImage();
		$form = $app['form.factory']->create(new PoeticFormType(), $entity);
		$form->handleRequest($request);
		
		if($form->isValid())
		{
			if(!is_null($entity->getImage()))
			{
				$image = uniqid()."_".$entity->getImage()->getClientOriginalName();
				$entity->getImage()->move("photo/poeticform/", $image);
			}
			else
				$image = $currentImage;

			$entity->setImage($image);
			$id = $app['repository.poeticform']->save($entity, $id);

			$redirect = $app['url_generator']->generate('poeticformadmin_show', array('id' => $id));

			return $app->redirect($redirect);
		}
	
		return $app['twig']->render('PoeticForm/edit.html.twig', array('form' => $form->createView(), 'entity' => $entity));
	}
}
