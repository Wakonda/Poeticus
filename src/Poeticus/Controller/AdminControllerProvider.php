<?php

namespace Controller;

use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Validator\Constraints as Assert;


class AdminControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];

        $controllers->get('/', function (Application $app) {
            return 'Index Admin';
        });
		
		$controllers->get('/poem/new', function (Application $app) {
		    $form = $app['form.factory']->createBuilder('form')
				->add('title')
				->add('test', 'file')
				->getForm();
				
			return $app['twig']->render('new.html.twig', array('form' => $form->createView()));
		});

        return $controllers;
    }
}