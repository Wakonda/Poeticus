<?php

namespace Poeticus\Controller;

use Silex\Application;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Response;
use Poeticus\Form\Type\IndexSearchType;
use Poeticus\Service\MailerPoeticus;
use Poeticus\Service\Captcha;
use Poeticus\Service\Gravatar;

use Poeticus\Entity\Poem;
use Poeticus\Form\Type\PoemUserType;

require_once __DIR__.'/../../../src/html2pdf_v4.03/Html2Pdf.php';
require_once __DIR__.'/../../simple_html_dom.php';

class IndexController
{
    public function indexAction(Request $request, Application $app)
    {
		// test
		// $test = new MailerPoeticus($app['swiftmailer.options']);
		// $test->setBody("ok");
		// $test->setSubject("ok");
		// $test->setSendTo("amatukami@hotmail.fr");
		// $test->send();

		$form = $this->createForm($app, null);
		$random = $app['repository.poem']->getRandomPoem();
		
        return $app['twig']->render('Index/index.html.twig', array('form' => $form->createView(), 'random' => $random));
    }
	
	public function indexSearchAction(Request $request, Application $app)
	{
		$search = $request->request->get("index_search");

		return $app['twig']->render('Index/resultIndexSearch.html.twig', array('search' => json_encode($search)));
	}
	
	public function indexSearchDatatablesAction(Request $request, Application $app, $search)
	{
		$iDisplayStart = $request->query->get('iDisplayStart');
		$iDisplayLength = $request->query->get('iDisplayLength');

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
		$sSearch = json_decode($search);
		$entities = $app['repository.poem']->findIndexSearch($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch);
		$iTotal = $app['repository.poem']->findIndexSearch($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, true);

		$output = array(
			"sEcho" => $request->query->get('sEcho'),
			"iTotalRecords" => $iTotal,
			"iTotalDisplayRecords" => $iTotal,
			"aaData" => array()
		);
		
		foreach($entities as $entity)
		{
			$row = array();
			$show = $app['url_generator']->generate('read', array('id' => $entity->getId()));
			$row[] = '<a href="'.$show.'" alt="Show">'.$entity->getTitle().'</a>';
			
			$biography = $entity->getBiography();
			$row[] = $biography['title'];

			$country = $entity->getCountry();
			$row[] = '<img src="'.$request->getBaseUrl().'/photo/country/'.$country['flag'].'" class="flag">';

			$output['aaData'][] = $row;
		}

		$response = new Response(json_encode($output));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}

	public function readAction(Request $request, Application $app, $id)
	{
		$entity = $app['repository.poem']->find($id, true);

		return $app['twig']->render('Index/read.html.twig', array('entity' => $entity));
	}

	public function readPDFAction(Request $request, Application $app, $id)
	{
		$entity = $app['repository.poem']->find($id, true);
		$content = $app['twig']->render('Index/pdf_poem.html.twig', array('entity' => $entity));

		$html2pdf = new \HTML2PDF('P','A4','fr');
		$html2pdf->WriteHTML($content);
		$file = $html2pdf->Output('poem.pdf');

		$response = new Response($file);
		$response->headers->set('Content-Type', 'application/pdf');

		return $response;
	}
	
	
	// AUTHOR
	public function authorAction(Request $request, Application $app, $id)
	{
		$entity = $app['repository.biography']->find($id, true);

		return $app['twig']->render('Index/author.html.twig', array('entity' => $entity));
	}
	
	public function authorDatatablesAction(Request $request, Application $app, $authorId)
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

		$entities = $app['repository.poem']->getPoemByAuthorDatatables($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $authorId);
		$iTotal = $app['repository.poem']->getPoemByAuthorDatatables($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $authorId, true);

		$output = array(
			"sEcho" => $request->query->get('sEcho'),
			"iTotalRecords" => $iTotal,
			"iTotalDisplayRecords" => $iTotal,
			"aaData" => array()
		);
		
		foreach($entities as $entity)
		{
			$row = array();
			$show = $app['url_generator']->generate('read', array('id' => $entity->getId()));
			$row[] = '<a href="'.$show.'" alt="Show">'.$entity->getTitle().'</a>';

			$collection = $entity->getCollection();
			
			if(!empty($collection))
			{
				$show = $app['url_generator']->generate('collection', array('id' => $collection['id']));
				$row[] = '<a class="underline italic" href="'.$show.'" alt="Show">'.$collection['title'].'</a>';
			}
			else
				$row[] = "-";
			$output['aaData'][] = $row;
		}

		$response = new Response(json_encode($output));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}
	
	// ENDAUTHOR

	public function lastPoemAction(Request $request, Application $app)
    {
		$entities = $app['repository.poem']->getLastEntries();

		return $app['twig']->render('Index/lastPoem.html.twig', array('entities' => $entities));
    }

	public function statPoemAction(Request $request, Application $app)
    {
		$statistics = $app['repository.poem']->getStat();

		return $app['twig']->render('Index/statPoem.html.twig', array('statistics' => $statistics));
    }

	// BY AUTHOR
	public function byAuthorsAction(Request $request, Application $app)
    {
        return $app['twig']->render('Index/byauthor.html.twig');
    }

	public function byAuthorsDatatablesAction(Request $request, Application $app)
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

		$entities = $app['repository.poem']->findPoemByAuthor($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch);
		$iTotal = $app['repository.poem']->findPoemByAuthor($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, true);

		$output = array(
			"sEcho" => $request->query->get('sEcho'),
			"iTotalRecords" => $iTotal,
			"iTotalDisplayRecords" => $iTotal,
			"aaData" => array()
		);

		foreach($entities as $entity)
		{
			$row = array();
			$show = $app['url_generator']->generate('author', array('id' => $entity['id']));
			$row[] = '<a href="'.$show.'" alt="Show">'.$entity['author'].'</a>';
			$row[] = $entity['number_poems_by_author'];

			$output['aaData'][] = $row;
		}

		$response = new Response(json_encode($output));
		$response->headers->set('Content-Type', 'application/json');
		
		return $response;
	}
	
	// POETIC FORM
	public function poeticFormAction(Request $request, Application $app, $id)
	{
		$entity = $app['repository.poeticform']->find($id, true);
		
		return $app['twig']->render('Index/poeticForm.html.twig', array('entity' => $entity));
	}
	
	public function poeticFormDatatablesAction(Request $request, Application $app, $poeticformId)
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

		$entities = $app['repository.poem']->getPoemByPoeticFormDatatables($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $poeticformId);
		$iTotal = $app['repository.poem']->getPoemByPoeticFormDatatables($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $poeticformId, true);

		$output = array(
			"sEcho" => $request->query->get('sEcho'),
			"iTotalRecords" => $iTotal,
			"iTotalDisplayRecords" => $iTotal,
			"aaData" => array()
		);
		
		foreach($entities as $entity)
		{
			$row = array();
			$show = $app['url_generator']->generate('read', array('id' => $entity["poem_id"]));
			$row[] = '<a href="'.$show.'" alt="Show">'.$entity["poem_title"].'</a>';

			$output['aaData'][] = $row;
		}

		$response = new Response(json_encode($output));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}
	
	public function byPoeticFormsAction(Request $request, Application $app)
    {
        return $app['twig']->render('Index/bypoeticform.html.twig');
    }
	
	public function byPoeticFormsDatatablesAction(Request $request, Application $app)
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

		$entities = $app['repository.poem']->findPoemByPoeticForm($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch);
		$iTotal = $app['repository.poem']->findPoemByPoeticForm($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, true);

		$output = array(
			"sEcho" => $request->query->get('sEcho'),
			"iTotalRecords" => $iTotal,
			"iTotalDisplayRecords" => $iTotal,
			"aaData" => array()
		);

		foreach($entities as $entity)
		{
			$row = array();

			if(!empty($entity['poeticform_id']))
			{
				$show = $app['url_generator']->generate('poeticform', array('id' => $entity['poeticform_id']));
				$row[] = '<a href="'.$show.'" alt="Show">'.$entity['poeticform'].'</a>';
			}
			else
				$row[] = "-";

			$row[] = $entity['number_poems_by_poeticform'];

			$output['aaData'][] = $row;
		}

		$response = new Response(json_encode($output));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}
	
	// COLLECTION
	public function collectionAction(Request $request, Application $app, $id)
	{
		$entity = $app['repository.collection']->find($id, true);

		return $app['twig']->render('Index/collection.html.twig', array('entity' => $entity));
	}
	
	public function collectionDatatablesAction(Request $request, Application $app, $collectionId)
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

		$entities = $app['repository.poem']->getPoemByCollectionDatatables($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $collectionId);
		$iTotal = $app['repository.poem']->getPoemByCollectionDatatables($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $collectionId, true);

		$output = array(
			"sEcho" => $request->query->get('sEcho'),
			"iTotalRecords" => $iTotal,
			"iTotalDisplayRecords" => $iTotal,
			"aaData" => array()
		);
		
		foreach($entities as $entity)
		{
			$row = array();
			$show = $app['url_generator']->generate('read', array('id' => $entity["poem_id"]));
			$row[] = '<a href="'.$show.'" alt="Show">'.$entity["poem_title"].'</a>';

			$output['aaData'][] = $row;
		}

		$response = new Response(json_encode($output));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}
	
	public function byCollectionsAction(Request $request, Application $app)
    {
        return $app['twig']->render('Index/bycollection.html.twig');
    }
	
	public function byCollectionsDatatablesAction(Request $request, Application $app)
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

		$entities = $app['repository.poem']->findPoemByCollection($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch);
		$iTotal = $app['repository.poem']->findPoemByCollection($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, true);

		$output = array(
			"sEcho" => $request->query->get('sEcho'),
			"iTotalRecords" => $iTotal,
			"iTotalDisplayRecords" => $iTotal,
			"aaData" => array()
		);

		foreach($entities as $entity)
		{
			$row = array();

			if(!empty($entity['collection_id']))
			{
				$show = $app['url_generator']->generate('collection', array('id' => $entity['collection_id']));
				$row[] = '<a href="'.$show.'" alt="Show">'.$entity['collection'].'</a>';
			}
			else
				$row[] = "-";

			if(!empty($entity['author_id']))
			{
				$show = $app['url_generator']->generate('author', array('id' => $entity['author_id']));
				$row[] = '<a href="'.$show.'" alt="Show">'.$entity['author'].'</a>';
			}
			else
				$row[] = "-";

			$row[] = $entity['number_poems_by_collection'];

			$output['aaData'][] = $row;
		}

		$response = new Response(json_encode($output));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}

	// COUNTRY
	public function countryAction(Request $request, Application $app, $id)
	{
		$entity = $app['repository.country']->find($id, true);

		return $app['twig']->render('Index/country.html.twig', array('entity' => $entity));
	}
	
	public function countryDatatablesAction(Request $request, Application $app, $countryId)
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

		$entities = $app['repository.poem']->getPoemByCountryDatatables($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $countryId);
		$iTotal = $app['repository.poem']->getPoemByCountryDatatables($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $countryId, true);

		$output = array(
			"sEcho" => $request->query->get('sEcho'),
			"iTotalRecords" => $iTotal,
			"iTotalDisplayRecords" => $iTotal,
			"aaData" => array()
		);
		
		foreach($entities as $entity)
		{
			$row = array();
			$show = $app['url_generator']->generate('read', array('id' => $entity["poem_id"]));
			$row[] = '<a href="'.$show.'" alt="Show">'.$entity["poem_title"].'</a>';
			
			$show = $app['url_generator']->generate('author', array('id' => $entity["biography_id"]));
			$row[] = '<a href="'.$show.'" alt="Show">'.$entity["biography_title"].'</a>';

			$output['aaData'][] = $row;
		}

		$response = new Response(json_encode($output));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}
	
	public function byCountriesAction(Request $request, Application $app)
    {
        return $app['twig']->render('Index/bycountry.html.twig');
    }
	
	public function byCountriesDatatablesAction(Request $request, Application $app)
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

		$entities = $app['repository.poem']->findPoemByCountry($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch);
		$iTotal = $app['repository.poem']->findPoemByCountry($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, true);

		$output = array(
			"sEcho" => $request->query->get('sEcho'),
			"iTotalRecords" => $iTotal,
			"iTotalDisplayRecords" => $iTotal,
			"aaData" => array()
		);

		foreach($entities as $entity)
		{
			$row = array();

			$show = $app['url_generator']->generate('country', array('id' => $entity['country_id']));
			$row[] = '<a href="'.$show.'" alt="Show"><img src="'.$request->getBaseUrl().'/photo/country/'.$entity['flag'].'" class="flag" /> '.$entity['country_title'].'</a>';

			$row[] = $entity['number_poems_by_country'];

			$output['aaData'][] = $row;
		}

		$response = new Response(json_encode($output));
		$response->headers->set('Content-Type', 'application/json');

		return $response;
	}
	
	public function byPoemUsersAction(Request $request, Application $app)
    {
        return $app['twig']->render('Index/bypoemuser.html.twig');
    }

	public function byPoemUsersDatatablesAction(Request $request, Application $app)
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

		$entities = $app['repository.poem']->findPoemByPoemUser($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch);
		$iTotal = $app['repository.poem']->findPoemByPoemUser($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, true);

		$output = array(
			"sEcho" => $request->query->get('sEcho'),
			"iTotalRecords" => $iTotal,
			"iTotalDisplayRecords" => $iTotal,
			"aaData" => array()
		);

		foreach($entities as $entity)
		{
			$row = array();

			$show = $app['url_generator']->generate('read', array('id' => $entity['poem_id']));
			$row[] = '<a href="'.$show.'" alt="Show">'.$entity['poem_title'].'</a>';

			$show = $app['url_generator']->generate('user_show', array('username' => $entity['username']));
			$row[] = '<a href="'.$show.'" alt="Show">'.$entity['username'].'</a>';

			$output['aaData'][] = $row;
		}

		$response = new Response(json_encode($output));
		$response->headers->set('Content-Type', 'application/json');

		return $response;
	}
	
	public function aboutAction(Request $request, Application $app)
	{
		return $app['twig']->render('Index/about.html.twig');
	}
	
	public function copyrightAction(Request $request, Application $app)
	{
		return $app['twig']->render('Index/copyright.html.twig');
	}
	
	private function createForm($app, $entity)
	{
		$countryForms = $app['repository.country']->findAllForChoice();
		$form = $app['form.factory']->create(new IndexSearchType($countryForms), null);
		
		return $form;
	}
	
	// Create User Poem
	public function poemUserNewAction(Request $request, Application $app)
	{
		$entity = new Poem();
		$form = $app['form.factory']->create(new PoemUserType(), null);

		return $app['twig']->render("Index/poemUserNew.html.twig", array("form" => $form->createView()));
	}
	
	public function poemUserCreateAction(Request $request, Application $app)
	{
		$entity = new Poem();
		$form = $app['form.factory']->create(new PoemUserType(), $entity);
		$form->handleRequest($request);
		
		if(array_key_exists("draft", $request->request->get($form->getName())))
			$entity->setState(1);
		else
			$entity->setState(0);
		
		if($form->isValid())
		{
			$user = $app['security']->getToken()->getUser();
			$user = $app['repository.user']->findByUsernameOrEmail($user->getUsername());

			$entity->setUser($user);
			$entity->setAuthorType("user");
			$entity->setCountry($user->getCountry());
			
			$now = new \DateTime();
			$entity->setReleasedDate($now->format('Y-m-d H:i:s'));
			$entity->setText(nl2br($entity->getText()));
			
			$app['repository.poem']->save($entity);

			return $app->redirect($app['url_generator']->generate('user_show', array('id' => $user->getId())));
		}
		
		return $app['twig']->render('Index/poemUserNew.html.twig', array('form' => $form->createView(), 'entity' => $entity));
	}
	
	public function poemUserEditAction(Request $request, Application $app, $id)
	{
		$entity = $app['repository.poem']->find($id, true);
		$form = $app['form.factory']->create(new PoemUserType(), $entity);

		return $app['twig']->render("Index/poemUserEdit.html.twig", array("form" => $form->createView(), "entity" => $entity));
	}

	public function poemUserUpdateAction(Request $request, Application $app, $id)
	{
		$entity = $app['repository.poem']->find($id, true);
		$form = $app['form.factory']->create(new PoemUserType(), $entity);
		$form->handleRequest($request);

		if(array_key_exists("draft", $request->request->get($form->getName())))
			$entity->setState(1);
		else
			$entity->setState(0);
		
		if($form->isValid())
		{
			$entity->setText($entity->getText());
			$user = $app['security']->getToken()->getUser();
			$user = $app['repository.user']->findByUsernameOrEmail($user->getUsername());

			$entity->setUser($user);
			
			$app['repository.poem']->save($entity, $id);

			return $app->redirect($app['url_generator']->generate('user_show', array('id' => $user->getId())));
		}
		
		return $app['twig']->render('Index/poemUserEdit.html.twig', array('form' => $form->createView(), 'entity' => $entity));
	}
	
	public function poemUserDeleteAction(Request $request, Application $app)
	{
		$id = $request->query->get("id");
		
		$entity = $app['repository.poem']->find($id, false);
		$entity->setState(2);
		
		$entity->setText(nl2br($entity->getText()));
		$user = $app['security']->getToken()->getUser();
		$user = $app['repository.user']->findByUsernameOrEmail($user->getUsername());

		$entity->setUser($user);

		$app['repository.poem']->save($entity, $id);
		
		return new Response();
	}

	public function reloadCaptchaAction(Request $request, Application $app)
	{
		$captcha = new Captcha($app);

		$wordOrNumberRand = rand(1, 2);
		$length = rand(3, 7);

		if($wordOrNumberRand == 1)
			$word = $captcha->wordRandom($length);
		else
			$word = $captcha->numberRandom($length);

		$response = new Response(json_encode(array("new_captcha" => $captcha->generate($word))));
		$response->headers->set('Content-Type', 'application/json');

		return $response;
	}

	public function reloadGravatarAction(Request $request, Application $app)
	{
		$gr = new Gravatar();

		$response = new Response(json_encode(array("new_gravatar" => $gr->getURLGravatar())));
		$response->headers->set('Content-Type', 'application/json');

		return $response;
	}
}