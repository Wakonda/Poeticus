<?php

namespace Poeticus\Controller;

use Silex\Application;
use Poeticus\Entity\Contact;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Response;
use Poeticus\Form\Type\ContactType;

class ContactController
{
    public function indexAction(Request $request, Application $app)
    {
		$form = $app['form.factory']->create(ContactType::class, null);

        return $app['twig']->render('Index/contact.html.twig', array('form' => $form->createView()));
    }
	
	public function sendAction(Request $request, Application $app)
	{
		$entity = new Contact();
        $form = $app['form.factory']->create(ContactType::class, $entity);
		$form->handleRequest($request);

		if($form->isValid())
		{
			$app['repository.contact']->save($entity);
			$app['session']->getFlashBag()->add('message', 'Votre message a été envoyé avec succès !');

			return $app->redirect($app['url_generator']->generate('index'));
		}
		
		return $app['twig']->render('Index/contact.html.twig', array('form' => $form->createView()));
	}
}
