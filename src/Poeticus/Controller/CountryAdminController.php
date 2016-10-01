<?php

namespace Poeticus\Controller;

use Poeticus\Entity\Country;
use Poeticus\Form\Type\CountryType;
use Silex\Application;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Response;

class CountryAdminController
{
	public function indexAction(Request $request, Application $app)
	{
		return $app['twig']->render('Country/index.html.twig');
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
		
		$entities = $app['repository.country']->getDatatablesForIndex($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch);
		$iTotal = $app['repository.country']->getDatatablesForIndex($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, true);

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

			$show = $app['url_generator']->generate('countryadmin_show', array('id' => $entity->getId()));
			$edit = $app['url_generator']->generate('countryadmin_edit', array('id' => $entity->getId()));
			
			$row[] = '<a href="'.$show.'" alt="Show">'.$translator->trans('admin.index.Read').'</a> - <a href="'.$edit.'" alt="Edit">'.$translator->trans('admin.index.Update').'</a>';

			$output['aaData'][] = $row;
		}

		$response = new Response(json_encode($output));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}

    public function newAction(Request $request, Application $app)
    {
		$entity = new Country();
        $form = $app['form.factory']->create(CountryType::class, $entity);

		return $app['twig']->render('Country/new.html.twig', array('form' => $form->createView()));
    }
	
	public function createAction(Request $request, Application $app)
	{
		$entity = new Country();
        $form = $app['form.factory']->create(CountryType::class, $entity);
		$form->handleRequest($request);

		$translator = $app['translator'];
		
		if($entity->getFlag() == null)
			$form->get("flag")->addError(new FormError($translator->trans("This value should not be blank.", array(), "validators")));
		
		if($form->isValid())
		{
			$image = uniqid()."_".$entity->getFlag()->getClientOriginalName();
			$entity->getFlag()->move("photo/country/", $image);
			$entity->setFlag($image);
			$id = $app['repository.country']->save($entity);

			$redirect = $app['url_generator']->generate('countryadmin_show', array('id' => $id));

			return $app->redirect($redirect);
		}
		
		return $app['twig']->render('Country/new.html.twig', array('form' => $form->createView()));
	}
	
	public function showAction(Request $request, Application $app, $id)
	{
		$entity = $app['repository.country']->find($id);
	
		return $app['twig']->render('Country/show.html.twig', array('entity' => $entity));
	}
	
	public function editAction(Request $request, Application $app, $id)
	{
		$entity = $app['repository.country']->find($id);
		$form = $app['form.factory']->create(CountryType::class, $entity);
	
		return $app['twig']->render('Country/edit.html.twig', array('form' => $form->createView(), 'entity' => $entity));
	}

	public function updateAction(Request $request, Application $app, $id)
	{
		$entity = $app['repository.country']->find($id);
		$currentImage = $entity->getFlag();
		$form = $app['form.factory']->create(CountryType::class, $entity);
		$form->handleRequest($request);
		
		if($form->isValid())
		{
			if(!is_null($entity->getFlag()))
			{
				$image = uniqid()."_".$entity->getFlag()->getClientOriginalName();
				$entity->getFlag()->move("photo/country/", $image);
			}
			else
				$image = $currentImage;

			$entity->setFlag($image);
			$id = $app['repository.country']->save($entity, $id);

			$redirect = $app['url_generator']->generate('countryadmin_show', array('id' => $id));

			return $app->redirect($redirect);
		}
	
		return $app['twig']->render('Country/edit.html.twig', array('form' => $form->createView(), 'entity' => $entity));
	}
}
