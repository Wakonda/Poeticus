<?php

namespace Poeticus\Controller;

use Poeticus\Entity\Poem;
use Poeticus\Entity\PoeticForm;
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
		
		$poeticForm = $app['repository.poeticform']->find($entity->getPoeticForm());
// die(var_dump($poeticForm).PoeticForm::IMAGETYPE);
		if($poeticForm->getTypeContentPoem() == PoeticForm::IMAGETYPE) {
			if($entity->getPhoto() == null)
				$form->get("photo")->addError(new FormError('Ce champ ne peut pas être vide'));
		}
		else {
			die("kkk");
			$form->get("text")->addError(new FormError('Ceggg champ ne peut pas être vide'));
		}
		
		$userForms = $app['repository.user']->findAllForChoice();

		if(($entity->isBiography() and $entity->getBiography() == null) or ($entity->isUser() and $entity->getUser() == null))
			$form->get($entity->getAuthorType())->addError(new FormError('Ce champ ne peut pas être vide'));
		
		if($form->isValid())
		{
			if($poeticForm->getTypeContentPoem() == PoeticForm::IMAGETYPE) {
				$image = uniqid()."_".$entity->getPhoto()->getClientOriginalName();
				$entity->getPhoto()->move("photo/poem/", $image);
				$entity->setPhoto($image);
			}
			
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
		
		$text = str_ireplace(array('<br>','<br/>','<br />'),"\r\n", $entity->getText());
		$entity->setText(strip_tags($text));
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
			$entity->setText('<p>'.nl2br($entity->getText()).'</p>');
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
		else
		{
			$url = $req["url"];
			$url_array = parse_url($url);

			$content = file_get_html($url);

			if(base64_encode($url_array['host']) == 'cG9lc2llLndlYm5ldC5mcg==')
			{
				$title = $content->find('h1'); 
				$text = $content->find('p[class=last]'); 

				$title = html_entity_decode($title[0]->plaintext);
				$title = (preg_match('!!u', $title)) ? $title : utf8_encode($title);
				
				$entity->setTitle($title);
				$entity->setText(str_replace(' class="last"', '', $text[0]->outertext));
			}
			elseif(base64_encode($url_array['host']) == 'd3d3LnBvZXNpZS1mcmFuY2Fpc2UuZnI=')
			{
				$title_node = $content->find('article h1');
				$title_str = $title_node[0]->plaintext;
				$title_array = explode(":", $title_str);
				$title = trim($title_array[1]);
				
				$text_node = $content->find('div.postpoetique p');
				$text_init = strip_tags($text_node[0]->plaintext, "<br><br /><br/>");
				$text_array = explode("\n", $text_init);
				$text = "";
				
				foreach($text_array as $line) {
					$text = $text."<br>".trim($line);
				}
				$text = preg_replace('/^(<br>)+/', '', $text);
				
				$entity->setTitle($title);
				$entity->setText($text);
			}
			
			$entity->setAuthorType("biography");
			
			if($app['repository.poem']->checkForDoubloon($entity) >= 1)
				$form->get("url")->addError(new FormError('Cette entrée existe déjà !'));
		}

		if($form->isValid())
		{
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
			$finalArray = array("releasedDate" => $entity->getReleasedDate());
		}
		else
			$finalArray = array("releasedDate" => "");
			
		$response = new Response(json_encode($finalArray));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}

	public function selectPoeticFormAction(Request $request, Application $app)
	{
		$id = $request->request->get("id");
		
		if($id != "")
		{
			$entity = $app['repository.poeticform']->find($id);
			$finalArray = array("typeContentPoem" => $entity->getTypeContentPoem());
		}
		else
			$finalArray = array("typeContentPoem" => "");
			
		$response = new Response(json_encode($finalArray));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}
	
	public function getBiographiesByAjaxAction(Request $request, Application $app)
	{
		if($request->query->has("pkey_val")) {
			$pkeyVal = $request->query->has("pkey_val");
			
			if(empty($pkeyVal))
				return json_encode(array());

			$parameters = array("pkey_val" => $request->query->get("pkey_val"));
			$response = $app['repository.biography']->getDatasCombobox($parameters);
			
			$resObj = new \stdClass();
			$resObj->id = $response["id"];
			$resObj->name = $response["title"];

			return json_encode($resObj);
		}

		$parameters = array(
		  'db_table'     => $request->query->get('db_table'),
		  'page_num'     => $request->query->get('page_num'),
		  'per_page'     => $request->query->get('per_page'),
		  'and_or'       => $request->query->get('and_or'),
		  'order_by'     => $request->query->get('order_by'),
		  'search_field' => $request->query->get('search_field'),
		  'q_word'       => $request->query->get('q_word')
		);

		$parameters['offset']  = ($parameters['page_num'] - 1) * $parameters['per_page'];

		$response = $app['repository.biography']->getDatasCombobox($parameters);
		$count = $app['repository.biography']->getDatasCombobox($parameters, true);

		$results = array();

		foreach($response as $res) {
			$obj = new \stdClass();
			$obj->id = $res['id'];
			$obj->name = $res['title'];
			
			$results[] = $obj;
		}

		$resObj = new \stdClass();
		$resObj->result = $results;
		$resObj->cnt_whole = $count;

		return json_encode($resObj);
	}
	
	private function createForm($app, $entity)
	{
		$poeticForms = $app['repository.poeticform']->findAllForChoice();
		$userForms = $app['repository.user']->findAllForChoice();
		$biographyForms = $app['repository.biography']->findAllForChoice();
		$countryForms = $app['repository.country']->findAllForChoice();
		$collectionForms = $app['repository.collection']->findAllForChoice();

		$form = $app['form.factory']->create(PoemType::class, $entity, array('poeticForms' => $poeticForms, 'users' => $userForms, 'biographies' => $biographyForms, 'countries' => $countryForms, 'collections' => $collectionForms));
		
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