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
		
		$entity->setText(strip_tags($entity->getText()));
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

		if($form->isValid())
		{
			$url = $req["url"];
			$url_array = parse_url($url);
			
			$content = file_get_html($url);

			if(base64_encode($url_array['host']) == 'd3d3LnBvZXNpZS53ZWJuZXQuZnI=')
			{
				$title = $content->find('h1'); 
				$text = $content->find('p[class=last]'); 

				$entity->setTitle((html_entity_decode($title[0]->plaintext)));
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
	
	public function getBiographiesByAjaxAction(Request $request, Application $app)
	{//die("llll");
		$bdd = new \PDO('mysql:host=localhost;dbname=poeticus;charset=utf8', 'root', '');
		// die(var_dump($_GET["pkey_val"]));
		if(array_key_exists("pkey_val", $_GET)) {
			 if(empty($_GET["pkey_val"]) or $_GET["pkey_val"] == 0)
				 return json_encode(array());
	$response = $bdd->query('SELECT id, title FROM biography WHERE id = '.$_GET["pkey_val"]);
	$res = $response->fetch();
	
	$resObj = new \stdClass();
	$resObj->id = $res["id"];
	$resObj->name = $res["title"];

	return json_encode($resObj);
}

$p = array(
  'db_table'     => $_GET['db_table'],
  'page_num'     => $_GET['page_num'],
  'per_page'     => $_GET['per_page'],
  'and_or'       => $_GET['and_or'],
  'order_by'     => $_GET['order_by'],
  'search_field' => $_GET['search_field'],
  'q_word'       => $_GET['q_word']
);

$p['offset']  = ($p['page_num'] - 1) * $p['per_page'];



$response = $bdd->query('SELECT id, title FROM biography WHERE title LIKE "%'.current($p['q_word']).'%" LIMIT '.$p['per_page'].' OFFSET '.$p['offset']);

$count = $bdd->query('SELECT COUNT(*) FROM biography WHERE title LIKE "%'.current($p['q_word']).'%"');

$r = array();
$genericObject = new \stdClass();

foreach($response->fetchAll() as $res) {
	$obj = new \stdClass();
	$obj->id = $res['id'];
	$obj->name = $res['title'];
	
	$r[] = $obj;
}

$resObj = new \stdClass();
$resObj->result = $r;
$resObj->cnt_whole = $count->fetchColumn();

return json_encode($resObj);
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