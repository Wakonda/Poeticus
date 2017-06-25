<?php

namespace Poeticus\Controller;

use Silex\Application;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Response;

use Poeticus\Service\SitemapGenerator;

class SitemapController
{
    public function generateAction(Request $request, Application $app)
    {
		$url_base = $request->getUriForPath("/");

		$sg = new SitemapGenerator($url_base, array("image" => true));
		
		// Generic
		$sg->addItem("", '1.0');
		$sg->addItem("page/copyright", '1.0');
		$sg->addItem("page/about", '1.0');
		$sg->addItem("contact", '1.0');
		$sg->addItem("version", '1.0');
		
		// Authors
		$sg->addItem("byauthors", '0.6');

		$entities = $app['repository.biography']->findAll();

		foreach($entities as $entity)
		{
			$sg->addItem("author/".$entity->getId()."/".$entity->getSlug(), '0.5', array("images" => array(array("loc" => "photo/biography/".$entity->getPhoto(), "caption" => ""))));
		}
		
		// Collection
		$sg->addItem("bycollections");
		
		$entities = $app['repository.collection']->findAll();

		foreach($entities as $entity)
		{
			$sg->addItem("collection/".$entity->getId()."/".$entity->getSlug(), '0.5', array("images" => array(array("loc" => "photo/collection/".$entity->getImage(), "caption" => ""))));
		}

		// Country
		$sg->addItem("bycountries");
		
		$entities = $app['repository.country']->findAll();

		foreach($entities as $entity)
		{
			$sg->addItem("country/".$entity->getId()."/".$entity->getSlug());
		}

		// Poetic Form
		$sg->addItem("bypoeticforms");
		
		$entities = $app['repository.poeticform']->findAll();

		foreach($entities as $entity)
		{
			$sg->addItem("poeticform/".$entity->getId()."/".$entity->getSlug());
		}
		
		// User
		$sg->addItem("bypoemusers");

		// Poem
		$entities = $app['repository.poem']->findAll();

		foreach($entities as $entity)
		{
			$sg->addItem("read/".$entity->getId().'/'.$entity->getSlug());
		}

		$res = $sg->save();
		
		file_put_contents("sitemap/sitemap.xml", $res);

		return $app['twig']->render('Admin/index.html.twig');
    }
	
	public function sitemapAction(Request $request, Application $app)
	{
		$response = new Response(file_get_contents("sitemap/sitemap.xml"));
		$response->headers->set('Content-Type', 'application/xml');
		$response->setCharset('UTF-8');
		
		return $response;
	}
}