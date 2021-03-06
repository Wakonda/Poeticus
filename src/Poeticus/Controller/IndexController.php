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

		// $app['request']->getSession()->set('_locale', 'pt');

		$form = $this->createForm($app, null);
		$random = $app['repository.poem']->getRandomPoem($this->getCurrentLocale($app));
		
        return $app['twig']->render('Index/index.html.twig', array('form' => $form->createView(), 'random' => $random));
    }
	
	public function changeLanguageAction(Request $request, Application $app, $language)
	{
		$request->getSession()->set('_locale', $language);

		return $app->redirect($app["url_generator"]->generate('index'));
	}
	
	public function indexSearchAction(Request $request, Application $app)
	{
		$search = $request->request->get("index_search");
		$search['country'] = (empty($search['country'])) ? null : $app['repository.country']->find($search['country'])->getTitle();
		
		$translator = $app['translator'];
		
		if($search['type'] == "biography")
			$search['type'] =  $translator->trans('main.field.GreatWriters');
		elseif($search['type'] == "user")
			$search['type'] =  $translator->trans('main.field.YourPoems');

		$criteria = array_filter(array_values($search));
		$criteria = empty($criteria) ? "Aucun" : $criteria;

		return $app['twig']->render('Index/resultIndexSearch.html.twig', array('search' => base64_encode(json_encode($search)), 'criteria' => $criteria));
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
		$sSearch = json_decode(base64_decode($search));
		$entities = $app['repository.poem']->findIndexSearch($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $this->getCurrentLocale($app));
		$iTotal = $app['repository.poem']->findIndexSearch($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $this->getCurrentLocale($app), true);

		$output = array(
			"sEcho" => $request->query->get('sEcho'),
			"iTotalRecords" => $iTotal,
			"iTotalDisplayRecords" => $iTotal,
			"aaData" => array()
		);
		
		foreach($entities as $entity)
		{
			$row = array();
			$show = $app['url_generator']->generate('read', array('id' => $entity->getId(), 'slug' => $entity->getSlug()));
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
		
		if(empty($entity))
			$app->abort('404');
		
		$params = array();
		
		if($entity->isBiography()) {
			$biography = $entity->getBiography();
			$params["author"] = $biography['id'];
			$params["field"] = "biography_id";
		}
		else {
			$params["author"] = $app['repository.user']->findByName($entity->getUser())->getId();
			$params["field"] = "user_id";			
		}

		$browsingPoems = $app['repository.poem']->browsingPoemShow($params, $id);

		return $app['twig']->render('Index/read.html.twig', array('entity' => $entity, 'browsingPoems' => $browsingPoems));
	}

	public function readPDFAction(Request $request, Application $app, $id)
	{
		$entity = $app['repository.poem']->find($id, true);
		
		if(empty($entity))
			$app->abort('404');
		
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
			$show = $app['url_generator']->generate('read', array('id' => $entity->getId(), 'slug' => $entity->getSlug()));
			$row[] = '<a href="'.$show.'" alt="Show">'.$entity->getTitle().'</a>';

			$collection = $entity->getCollection();
			
			if(!empty($collection))
			{
				$show = $app['url_generator']->generate('collection', array('id' => $collection['id'], 'slug' => $collection['slug']));
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
		$entities = $app['repository.poem']->getLastEntries($this->getCurrentLocale($app));
		$app['generic_function']->setLocaleTwigRenderController();

		return $app['twig']->render('Index/lastPoem.html.twig', array('entities' => $entities));
    }

	public function statPoemAction(Request $request, Application $app)
    {
		$statistics = $app['repository.poem']->getStat($this->getCurrentLocale($app));

		$app['generic_function']->setLocaleTwigRenderController();

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
		}//die("kk");

		$entities = $app['repository.poem']->findPoemByAuthor($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $this->getCurrentLocale($app));
		$iTotal = $app['repository.poem']->findPoemByAuthor($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $this->getCurrentLocale($app), true);

		$output = array(
			"sEcho" => $request->query->get('sEcho'),
			"iTotalRecords" => $iTotal,
			"iTotalDisplayRecords" => $iTotal,
			"aaData" => array()
		);

		foreach($entities as $entity)
		{
			if(!empty($entity['id']))
			{
				$row = array();
				$show = $app['url_generator']->generate('author', array('id' => $entity['id'], 'slug' => $entity['slug']));
				$row[] = '<a href="'.$show.'" alt="Show">'.$entity['author'].'</a>';
				$row[] = $entity['number_poems_by_author'];

				$output['aaData'][] = $row;
			}
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
			$show = $app['url_generator']->generate('read', array('id' => $entity["poem_id"], 'slug' => $entity['slug']));
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

		$entities = $app['repository.poem']->findPoemByPoeticForm($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $this->getCurrentLocale($app));
		$iTotal = $app['repository.poem']->findPoemByPoeticForm($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $this->getCurrentLocale($app), true);

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
				$show = $app['url_generator']->generate('poeticform', array('id' => $entity['poeticform_id'], 'slug' => $entity['poeticform_slug']));
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
			$show = $app['url_generator']->generate('read', array('id' => $entity["poem_id"], 'slug' => $entity['slug']));
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

		$entities = $app['repository.poem']->findPoemByCollection($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $this->getCurrentLocale($app));
		$iTotal = $app['repository.poem']->findPoemByCollection($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $this->getCurrentLocale($app), true);

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
				$show = $app['url_generator']->generate('collection', array('id' => $entity['collection_id'], 'slug' => $entity['collection_slug']));
				$row[] = '<a href="'.$show.'" alt="Show">'.$entity['collection'].'</a>';
			}
			else
				$row[] = "-";

			if(!empty($entity['author_id']))
			{
				$show = $app['url_generator']->generate('author', array('id' => $entity['author_id'], 'slug' => $entity['author_slug']));
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
	
	public function readCollectionPDFAction(Request $request, Application $app, $collectionId, $biographyId)
	{
		$biography = $app['repository.biography']->find($biographyId);
		$collection = $app['repository.collection']->find($collectionId, true);
		$entities = $app['repository.collection']->getAllPoemsByCollectionAndAuthorForPdf($collectionId);

		$content = $app['twig']->render('Index/pdf_poem_collection.html.twig', array('biography' => $biography, 'collection' => $collection, 'entities' => $entities));

		$html2pdf = new \HTML2PDF('P','A4','fr');
		$html2pdf->WriteHTML($content);
		$html2pdf->createIndex('Sommaire', 25, 12, false, true, null, "times");
		
		$file = $html2pdf->Output('poem.pdf');

		$response = new Response($file);
		$response->headers->set('Content-Type', 'application/pdf');

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
			$show = $app['url_generator']->generate('read', array('id' => $entity["poem_id"], 'slug' => $entity["poem_slug"]));
			$row[] = '<a href="'.$show.'" alt="Show">'.$entity["poem_title"].'</a>';
			
			$show = $app['url_generator']->generate('author', array('id' => $entity["biography_id"], 'slug' => $entity['biography_slug']));
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

		$entities = $app['repository.poem']->findPoemByCountry($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $this->getCurrentLocale($app));
		$iTotal = $app['repository.poem']->findPoemByCountry($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $this->getCurrentLocale($app), true);

		$output = array(
			"sEcho" => $request->query->get('sEcho'),
			"iTotalRecords" => $iTotal,
			"iTotalDisplayRecords" => $iTotal,
			"aaData" => array()
		);

		foreach($entities as $entity)
		{
			$row = array();

			$show = $app['url_generator']->generate('country', array('id' => $entity['country_id'], 'slug' => $entity['country_slug']));
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
			
		for($i=0 ; $i < intval($request->query->get('iSortingCols')); $i++)
		{
			if ($request->query->get('bSortable_'.intval($request->query->get('iSortCol_'.$i))) == "true" )
			{
				$sortByColumn[] = $request->query->get('iSortCol_'.$i);
				$sortDirColumn[] = $request->query->get('sSortDir_'.$i);
			}
		}

		$entities = $app['repository.poem']->findPoemByPoemUser($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $this->getCurrentLocale($app));
		$iTotal = $app['repository.poem']->findPoemByPoemUser($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $this->getCurrentLocale($app), true);

		$output = array(
			"sEcho" => $request->query->get('sEcho'),
			"iTotalRecords" => $iTotal,
			"iTotalDisplayRecords" => $iTotal,
			"aaData" => array()
		);

		foreach($entities as $entity)
		{
			if(!empty($entity['id']))
			{
				$row = array();

				$show = $app['url_generator']->generate('read', array('id' => $entity['poem_id'], 'slug' => $entity['slug']));
				$row[] = '<a href="'.$show.'" alt="Show">'.$entity['poem_title'].'</a>';

				$show = $app['url_generator']->generate('user_show', array('username' => $entity['username']));
				$row[] = '<a href="'.$show.'" alt="Show">'.$entity['username'].'</a>';

				$output['aaData'][] = $row;
			}
		}

		$response = new Response(json_encode($output));
		$response->headers->set('Content-Type', 'application/json');

		return $response;
	}
	
	public function pageAction(Request $request, Application $app, $name)
	{
		$entity = $app['repository.page']->findByName($name, $this->getCurrentLocale($app));
		
		return $app['twig']->render('Index/page.html.twig', array("entity" => $entity));
	}
	
	public function versionAction(Request $request, Application $app)
	{
		$entities = $app['repository.version']->findByLanguage($this->getCurrentLocale($app));
		
		return $app['twig']->render('Index/version.html.twig', array('entities' => $entities));
	}
	
	private function createForm($app, $entity)
	{
		$language = $app['repository.language']->findOneByAbbreviation($this->getCurrentLocale($app));

		$countryForms = $app['repository.country']->findAllForChoice($language->getAbbreviation());
		$form = $app['form.factory']->create(IndexSearchType::class, null, array("countries" => $countryForms));
		
		return $form;
	}
	
	// Create User Poem
	public function poemUserNewAction(Request $request, Application $app)
	{
		$entity = new Poem();
		$form = $app['form.factory']->create(PoemUserType::class, null);

		return $app['twig']->render("Index/poemUserNew.html.twig", array("form" => $form->createView()));
	}
	
	public function poemUserCreateAction(Request $request, Application $app)
	{
		$entity = new Poem();
		$form = $app['form.factory']->create(PoemUserType::class, $entity);
		$form->handleRequest($request);
		
		if(array_key_exists("draft", $request->request->get($form->getName())))
			$entity->setState(1);
		else
			$entity->setState(0);
		
		if($form->isValid())
		{
			$user = $app['security.token_storage']->getToken()->getUser();
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
		$entity->setText(strip_tags($entity->getText()));
		
		$form = $app['form.factory']->create(PoemUserType::class, $entity);

		return $app['twig']->render("Index/poemUserEdit.html.twig", array("form" => $form->createView(), "entity" => $entity));
	}

	public function poemUserUpdateAction(Request $request, Application $app, $id)
	{
		$entity = $app['repository.poem']->find($id, true);
		$form = $app['form.factory']->create(PoemUserType::class, $entity);
		$form->handleRequest($request);

		if(array_key_exists("draft", $request->request->get($form->getName())))
			$entity->setState(1);
		else
			$entity->setState(0);
		
		if($form->isValid())
		{
			$entity->setText(nl2br($entity->getText()));

			$user = $app['security.token_storage']->getToken()->getUser();
			$user = $app['repository.user']->findByUsernameOrEmail($user->getUsername());

			$entity->setUser($user);

			$entity->setCountry($user->getCountry());
			
			$language = $app['repository.language']->findOneByAbbreviation($this->getCurrentLocale($app));

			$entity->setLanguage($language->getId());
			
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
		$user = $app['security.token_storage']->getToken()->getUser();
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
	
	private function getCurrentLocale($app)
	{
		return $app['generic_function']->getLocaleTwigRenderController();
	}
}