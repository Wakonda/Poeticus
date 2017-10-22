<?php

namespace Poeticus\Controller;

use Poeticus\Entity\Poem;
use Poeticus\Entity\PoeticForm;
use Poeticus\Form\Type\PoemType;
use Poeticus\Form\Type\PoemFastType;
use Poeticus\Form\Type\PoemFastMultipleType;
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
		
		$translator = $app['translator'];
		
		foreach($entities as $entity)
		{
			$row = array();
			$row[] = $entity->getId();
			$row[] = $entity->getTitle();
			
			$language = $entity->getLanguage();
			$row[] = $language['title'];
			
			$show = $app['url_generator']->generate('poemadmin_show', array('id' => $entity->getId()));
			$edit = $app['url_generator']->generate('poemadmin_edit', array('id' => $entity->getId()));
			
			$row[] = '<a href="'.$show.'" alt="Show">'.$translator->trans('admin.index.Read').'</a> - <a href="'.$edit.'" alt="Edit">'.$translator->trans('admin.index.Update').'</a>';

			$output['aaData'][] = $row;
		}

		$response = new Response(json_encode($output));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}

    public function newAction(Request $request, Application $app, $biographyId, $collectionId)
    {
		$entity = new Poem();

		if(!empty($biographyId))
			$entity->setBiography($biographyId);

		if(!empty($collectionId))
			$entity->setCollection($collectionId);

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
		$translator = $app['translator'];
		
		if(!empty($poeticForm) and $poeticForm->getTypeContentPoem() == PoeticForm::IMAGETYPE) {
			if($entity->getPhoto() == null)
				$form->get("photo")->addError(new FormError($translator->trans("This value should not be blank.", array(), "validators")));
		}
		else {
			if($entity->getText() == null)
				$form->get("text")->addError(new FormError($translator->trans("This value should not be blank.", array(), "validators")));
		}
		
		$userForms = $app['repository.user']->findAllForChoice($app['generic_function']->getLocaleTwigRenderController());

		if(($entity->isBiography() and $entity->getBiography() == null) or ($entity->isUser() and $entity->getUser() == null))
			$form->get($entity->getAuthorType())->addError(new FormError($translator->trans("This value should not be blank.", array(), "validators")));

		if($form->isValid())
		{
			if(!empty($poeticForm) and $poeticForm->getTypeContentPoem() == PoeticForm::IMAGETYPE) {
				$image = $app['generic_function']->getUniqCleanNameForFile($entity->getPhoto());
				$entity->getPhoto()->move("photo/poem/", $image);
				$entity->setPhoto($image);
			}

			$entity->setCountry($app['repository.biography']->find($entity->getBiography())->getCountry());
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
		$form = $this->createForm($app, $app['repository.poem']->find($id));
		$entity = $app['repository.poem']->find($id, true);

		return $app['twig']->render('Poem/edit.html.twig', array('form' => $form->createView(), 'entity' => $entity));
	}

	public function updateAction(Request $request, Application $app, $id)
	{
		$entity = $app['repository.poem']->find($id);
		$form = $this->createForm($app, $entity);
		$form->handleRequest($request);
		
		$this->checkForDoubloon($entity, $form, $app);
		
		if(($entity->isBiography() and $entity->getBiography() == null) or ($entity->isUser() and $entity->getUser() == null))
			$form->get($entity->getAuthorType())->addError(new FormError($translator->trans("This value should not be blank.", array(), "validators")));
		
		if($form->isValid())
		{
			if(!empty($poeticForm) and $poeticForm->getTypeContentPoem() == PoeticForm::IMAGETYPE and !is_null($entity->getPhoto())) {
				$image = $app['generic_function']->getUniqCleanNameForFile($entity->getPhoto());
				$entity->getPhoto()->move("photo/poem/", $image);
				$entity->setPhoto($image);
			}

			$entity->setCountry($app['repository.biography']->find($entity->getBiography())->getCountry());
			$id = $app['repository.poem']->save($entity, $id);

			$redirect = $app['url_generator']->generate('poemadmin_show', array('id' => $id));

			return $app->redirect($redirect);
		}
	
		return $app['twig']->render('Poem/edit.html.twig', array('form' => $form->createView(), 'entity' => $entity));
	}
	
	public function newFastAction(Request $request, Application $app, $biographyId, $collectionId)
	{
		$entity = new Poem();
		$poeticForms = $app['repository.poeticform']->findAllForChoice($app['generic_function']->getLocaleTwigRenderController());
		$collectionForms = $app['repository.collection']->findAllForChoice($app['generic_function']->getLocaleTwigRenderController());
		$languageForms = $app['repository.language']->findAllForChoice();
		$language = $app['repository.language']->findOneByAbbreviation($app['generic_function']->getLocaleTwigRenderController());
		$localeForms = $language->getId();
		
		if(!empty($biographyId))
			$entity->setBiography($biographyId);

		if(!empty($collectionId))
			$entity->setCollection($collectionId);
		
		$form = $app['form.factory']->create(PoemFastType::class, $entity, array('collections' => $collectionForms, 'languages' => $languageForms, "locale" => $localeForms, 'poeticForms' => $poeticForms));
	
		return $app['twig']->render('Poem/fast.html.twig', array('form' => $form->createView(), 'entity' => $entity));
	}

	public function addFastAction(Request $request, Application $app)
	{
		$entity = new Poem();
		$poeticForms = $app['repository.poeticform']->findAllForChoice($app['generic_function']->getLocaleTwigRenderController());
		$collectionForms = $app['repository.collection']->findAllForChoice($app['generic_function']->getLocaleTwigRenderController());
		$languageForms = $app['repository.language']->findAllForChoice();
		$language = $app['repository.language']->findOneByAbbreviation($app['generic_function']->getLocaleTwigRenderController());
		$localeForms = $language->getId();
		
		$form = $app['form.factory']->create(PoemFastType::class, $entity, array('collections' => $collectionForms, 'languages' => $languageForms, "locale" => $localeForms, 'poeticForms' => $poeticForms));
	
		$form->handleRequest($request);
		
		$req = $request->request->get($form->getName());

		if(!empty($req["url"]) and filter_var($req["url"], FILTER_VALIDATE_URL))
		{
			$url = $req["url"];
			$url_array = parse_url($url);

			if(!empty($ipProxy = $form->get('ipProxy')->getData()))
				$content = str_get_html($app['generic_function']->file_get_contents_proxy($url, $ipProxy));
			else
				$content = file_get_html($url, false, null, 0);

			$entity->setAuthorType("biography");
			$entity->setCountry($app['repository.biography']->find($entity->getBiography())->getCountry());
			$poemArray = array();

			switch(base64_encode($url_array['host']))
			{
				case 'cG9lc2llLndlYm5ldC5mcg==':
					$title = $content->find('h1'); 
					$text = $content->find('p[class=last]'); 

					$title = html_entity_decode($title[0]->plaintext);
					$title = (preg_match('!!u', $title)) ? $title : utf8_encode($title);

					$subPoemArray = array();
					$subPoemArray['title'] = $title;
					$subPoemArray['text'] = str_replace(' class="last"', '', $text[0]->outertext);
					$poemArray[] = $subPoemArray;
					break;
				case 'd3d3LnBvZXNpZS1mcmFuY2Fpc2UuZnI=':
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
					
					$subPoemArray = array();
					$subPoemArray['title'] = $title;
					$subPoemArray['text'] = $text;
					$poemArray[] = $subPoemArray;
					break;
				case 'd3d3LnBvZXRpY2EuZnI=':
					$title = current($content->find("h1.entry-title"))->innertext;
					
					$text = $content->find("main article div.entry-content");
					$text = $text[1]->innertext;
					
					$text = str_replace("<p>", "", $text);
					$text = str_replace("<br />", "<br>", $text);
					$text = trim($text);

					$text = explode("</p>", $text);
					array_pop($text);
					array_pop($text);
					$text = implode("<br><br>", $text);
					
					$subPoemArray = array();
					$subPoemArray['title'] = $title;
					$subPoemArray['text'] = $text;
					$poemArray[] = $subPoemArray;
					break;
				case 'd3d3LnRvdXRlbGFwb2VzaWUuY29t':
					$html = file_get_html($url);
					$title = trim(current(explode("<br>", current($html->find('h1.ipsType_pagetitle'))->innertext)));					
					$text = current($html->find('div.poemeanthologie p.last'))->innertext;
					$text =preg_replace('#</?span[^>]*>#is', '', $text);

					$subPoemArray = array();
					$subPoemArray['title'] = utf8_encode($title);
					$subPoemArray['text'] = utf8_encode($text);
					$poemArray[] = $subPoemArray;
					break;
				case 'd3d3LnVuaGFpa3UuY29t':
					foreach($content->find('ul#chunkLast > li') as $li)
					{
						$text = current($li->find("div#texte"));
						
						if(!empty($text))
						{
							$titleArray = preg_split(":(<br ?/?>):", $text->innertext);
							
							$subPoemArray = array();
							$subPoemArray['title'] = $titleArray[0];
							$subPoemArray['text'] = $text->innertext;
							$poemArray[] = $subPoemArray;
						}
					}
					break;
				case 'd3d3LmNpdGFkb3IucHQ=':
					$dom = new \DOMDocument();
					libxml_use_internal_errors(true); 
					$dom->loadHTML(file_get_contents($url));
					libxml_clear_errors();

					$xpath = new \DOMXpath($dom);

					$div = $xpath->query("//div[@class='panel panel-default']/div[@class='panel-body']/div")->item(0);
					
					$subPoemArray = [];
					$subPoemArray['title'] = $xpath->query("//div[@class='panel panel-default']/div[@class='panel-body']/h2")->item(0)->textContent;

					$html="";
					foreach($div->childNodes as $node) {
						$html .= str_replace("&nbsp;", '', $dom->saveHTML($node));
					}

					$htmlArray = preg_split('/<i[^>]*>([\s\S]*?)<\/i[^>]*>/', $html);

					array_pop($htmlArray);
					$content = $htmlArray[0];

					$content = preg_replace('/<font[^>]*>([\s\S]*?)<\/font[^>]*>/', '', $content);

					// Remove <br> at the end of string
					$content = preg_replace('[^([\n\r\s]*<br( \/)?>[\n\r\s]*)*|([\n\r\s]*<br( \/)?>[\n\r\s]*)*$]', '', $content);

					$content = str_replace(chr(150), "-", utf8_decode($content));// Replace "en dash" by simple "dash"
					$content = str_replace(chr(151), '-', $content);// Replace "em dash" by simple "dash"
					$content = str_replace("\xc2\xa0", '', utf8_encode($content));// Remove nbsp
				
					$subPoemArray['text'] = $content;
				
					/*$html = file_get_html($url);
					
					$divPanelDefault = $html->find("div.panel-default", 0);
					$div = $divPanelDefault->find("div.panel-body", 0);
					
					
					$subPoemArray['title'] = $div->find("h2", 0)->plaintext;
					$content = $div->find("div", 0)->innertext;

					$content = preg_replace('/<font[^>]*>([\s\S]*?)<\/font[^>]*>/', '', $content);
					$content = preg_replace('/<i[^>]*>([\s\S]*?)<\/i[^>]*>/', '', $content);
					
					// Remove <br> at the end of string
					$content = preg_replace('[^([\n\r\s]*<br( \/)?>[\n\r\s]*)*|([\n\r\s]*<br( \/)?>[\n\r\s]*)*$]', '', $content);

					$content = str_replace(chr(150), "-", $content);// Replace "en dash" by simple "dash"
					$content = str_replace(chr(151), '-', $content);// Replace "em dash" by simple "dash"
					$content = utf8_encode($content); 

					$subPoemArray['text'] = $content;*/
					
					$poemArray[] = $subPoemArray;
					break;
			}
		}
		
		$numberDoubloons = 0;
		$numberAdded = 0;

		if($form->isValid())
		{
			foreach($poemArray as $poem)
			{
				$entityPoem = clone $entity;
				$entityPoem->setTitle($poem['title']);
				$entityPoem->setText($poem['text']);

				if($app['repository.poem']->checkForDoubloon($entityPoem) >= 1)
					$numberDoubloons++;
				else
				{
					$id = $app['repository.poem']->save($entityPoem);
					$numberAdded++;
				}
			}
			if(!empty($id))
				$redirect = $app['url_generator']->generate('poemadmin_show', array('id' => $id));
			else
				$redirect = $app['url_generator']->generate('poemadmin_index');

			return $app->redirect($redirect);
		}
	
		return $app['twig']->render('Poem/fast.html.twig', array('form' => $form->createView(), 'entity' => $entity));
	}
	
	public function newFastMultipleAction(Request $request, Application $app)
	{
		$poeticForms = $app['repository.poeticform']->findAllForChoice($app['generic_function']->getLocaleTwigRenderController());
		$collectionForms = $app['repository.collection']->findAllForChoice($app['generic_function']->getLocaleTwigRenderController());
		$language = $app['repository.language']->findOneByAbbreviation($app['generic_function']->getLocaleTwigRenderController());
		$localeForms = $language->getId();
		$languageForms = $app['repository.language']->findAllForChoice();
		
		$form = $app['form.factory']->create(PoemFastMultipleType::class, null, array('collections' => $collectionForms, 'poeticForms' => $poeticForms, "locale" => $localeForms, 'languages' => $languageForms));


		return $app['twig']->render('Poem/fastMultiple.html.twig', array('form' => $form->createView(), 'language' => $language));
	}
	
	public function addFastMultipleAction(Request $request, Application $app)
	{
		$entity = new Poem();
		$poeticForms = $app['repository.poeticform']->findAllForChoice($app['generic_function']->getLocaleTwigRenderController());
		$collectionForms = $app['repository.collection']->findAllForChoice($app['generic_function']->getLocaleTwigRenderController());
		$languageForms = $app['repository.language']->findAllForChoice();
		$language = $app['repository.language']->findOneByAbbreviation($app['generic_function']->getLocaleTwigRenderController());
		$localeForms = $language->getId();
		
		$form = $app['form.factory']->create(PoemFastMultipleType::class, $entity, array('collections' => $collectionForms, 'poeticForms' => $poeticForms, "locale" => $localeForms, 'languages' => $languageForms));
		
		$form->handleRequest($request);
		$req = $request->request->get($form->getName());
			
		if(!empty($req["url"]) and filter_var($req["url"], FILTER_VALIDATE_URL))
		{
			$url = $req["url"];
			$url_array = parse_url($url);
			
			$authorizedURLs = ['d3d3LnBvZXNpZS1mcmFuY2Fpc2UuZnI=', 'd3d3LnBlbnNpZXJpcGFyb2xlLml0'];
			
			if(!in_array(base64_encode($url_array['host']), $authorizedURLs))
				$form->get("url")->addError(new FormError('URL inconnue'));
		}

		if($form->isValid())
		{
			$entity->setAuthorType("biography");
			$entity->setCountry($app['repository.biography']->find($entity->getBiography())->getCountry());
			$number = $req['number'];
			$i = 0;
			if(!empty($ipProxy = $form->get('ipProxy')->getData()))
				$html = str_get_html($app['generic_function']->file_get_contents_proxy($url, $ipProxy));
			else
				$html = file_get_html($url, false, null, 0);
			
			switch(base64_encode($url_array['host']))
			{
				case 'd3d3LnBvZXNpZS1mcmFuY2Fpc2UuZnI=':
					foreach($html->find('div.poemes-auteurs') as $div)
					{					
						$entityPoem = clone $entity;
						$a = current($div->find("a"));
						$content = file_get_html($a->href);
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
						
						$entityPoem->setTitle($title);
						$entityPoem->setText($text);
						$entityPoem->setLanguage($app['repository.language']->findOneByAbbreviation('fr')->getId());
						
						if($app['repository.poem']->checkForDoubloon($entityPoem) >= 1)
							continue;
						
						if($number == $i)
							break;
	
						$i++;

						$id = $app['repository.poem']->save($entityPoem);
					}
					break;
				case 'd3d3LnBlbnNpZXJpcGFyb2xlLml0':
					foreach($html->find('article') as $article)
					{
						$title = $article->find("h2", 0)->plaintext;
						$blockquote = $article->find('blockquote', 0);
						$a = $blockquote->find('a', 0);
						
						$content = $a->plaintext;
						$content = utf8_encode(str_replace(chr(150), '-', $content)); // Replace "en dash" by simple "dash"
						$content = str_replace("\n", "<br>", $content);
						$entityPoem = clone $entity;
						$entityPoem->setTitle($title);
						$entityPoem->setText($content);
						
						$entityPoem->setLanguage($app['repository.language']->findOneByAbbreviation('it')->getId());
						
						if($app['repository.poem']->checkForDoubloon($entityPoem) >= 1)
							continue;
						
						if($number == $i)
							break;
	
						$i++;

						$id = $app['repository.poem']->save($entityPoem);
					}
				break;
			}

			
			
			if(isset($id))
				$redirect = $app['url_generator']->generate('poemadmin_show', array('id' => $id));
			else
				$redirect = $app['url_generator']->generate('poemadmin_index');

			return $app->redirect($redirect);
		}
		
		return $app['twig']->render('Poem/fastMultiple.html.twig', array('form' => $form->createView(), 'language' => $language));
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

			$countryText = (empty($country)) ? null : array('title' => $country->getTitle(), 'flag' => $country->getFlag());
				
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
			$finalArray = array("releasedDate" => null);
			
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
		$locale = $request->query->get("locale");
		
		if($request->query->has("pkey_val")) {
			$pkeyVal = $request->query->has("pkey_val");
			
			if(empty($pkeyVal))
				return json_encode(array());

			$parameters = array("pkey_val" => $request->query->get("pkey_val"));
			$response = $app['repository.biography']->getDatasCombobox($parameters, $locale);
			
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

		$response = $app['repository.biography']->getDatasCombobox($parameters, $locale);
		$count = $app['repository.biography']->getDatasCombobox($parameters, $locale, true);

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
		$poeticForms = $app['repository.poeticform']->findAllForChoice($app['generic_function']->getLocaleTwigRenderController());
		$userForms = $app['repository.user']->findAllForChoice();
		$collectionForms = $app['repository.collection']->findAllForChoice($app['generic_function']->getLocaleTwigRenderController());
		$languageForms = $app['repository.language']->findAllForChoice();
		$language = $app['repository.language']->findOneByAbbreviation($app['generic_function']->getLocaleTwigRenderController());
		$localeForms = $language->getId();

		return $app['form.factory']->create(PoemType::class, $entity, array('poeticForms' => $poeticForms, 'users' => $userForms, 'collections' => $collectionForms, 'languages' => $languageForms, "locale" => $localeForms));
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