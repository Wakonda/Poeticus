<?php

namespace Poeticus\Controller;

use Silex\Application;
use Poeticus\Entity\Contact;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Response;
use Poeticus\Form\Type\SendPoemType;
use Poeticus\Service\MailerPoeticus;

class SendPoemController
{
    public function indexAction(Request $request, Application $app, $poemId)
    {
		$form = $app['form.factory']->create(SendPoemType::class, null);
		
		$app['locale'] = $app['request']->getLocale();

        return $app['twig']->render('Index/send_poem.html.twig', array('form' => $form->createView(), 'poemId' => $poemId));
    }
	
	public function sendAction(Request $request, Application $app, $poemId)
	{
		$sendPoemForm = new SendPoemType();
		
		parse_str($request->request->get('form'), $form_array);

        $form = $app['form.factory']->create(SendPoemType::class, $form_array);
		
		$form->handleRequest($request);

		if($form->isSubmitted() && $form->isValid())
		{
			$data = (object)($request->request->get($form->getName()));
			$entity = $app['repository.poem']->find($poemId, true);
			
			$content = $app['twig']->render('Index/send_poem_message_content.html.twig', array(
				"data" => $data,
				"entity" => $entity
			));

			$mailer = new MailerPoeticus($app['swiftmailer.options']);
			
			$mailer->setSubject($data->subject);
			$mailer->setSendTo($data->recipientMail);
			$mailer->setBody($content);
			
			$mailer->send();
			
			$response = new Response(json_encode(array("result" => "ok")));
			$response->headers->set('Content-Type', 'application/json');

			return $response;
		}

		$res = array("result" => "error");
		
		$res["content"] = $app['twig']->render('Index/send_poem_form.html.twig', array('form' => $form->createView(), 'poemId' => $poemId));
		
		$response = new Response(json_encode($res));
		$response->headers->set('Content-Type', 'application/json');
		
		return $response;
	}
}