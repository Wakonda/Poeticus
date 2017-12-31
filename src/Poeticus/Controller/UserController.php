<?php

namespace Poeticus\Controller;

use Poeticus\Entity\User;
use Poeticus\Form\Type\UserType;
use Poeticus\Form\Type\UpdatePasswordType;
use Poeticus\Form\Type\ForgottenPasswordType;
use Poeticus\Form\Type\LoginType;

use Poeticus\Service\MailerPoeticus;
use Poeticus\Service\PasswordHash;

use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormError;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
use Symfony\Component\HttpFoundation\Response;

class UserController implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
		$request = $app['request_stack']->getCurrentRequest();

		if($request->query->get("t") != null)
		{
			$entity = $app['repository.user']->findByToken($request->query->get("t"), false);
			
			$now = new \Datetime();

			if($entity->getExpiredAt() > $now)
			{
				$app['session']->getFlashBag()->add('confirm_login', 'Félicitation '.$entity->getUsername(). ', votre compte a été activé. Veuillez entrer vos identifiants pour vous connecter.');
				$entity->setEnabled(true);
				$app['repository.user']->save($entity, $entity->getId());
			}
			else
				$app['session']->getFlashBag()->add('expired_login', 'Désolé '.$entity->getUsername(). ', votre compte ne peut pas être activé, puisque le lien est expiré.');
		}
		
		
		return $app['twig']->render('User/login.html.twig', array(
				// 'error'         => "Pseudo ou mot de passe erroné"/*$app['security.last_error']($request)*/,
				'error'         => $app['security.last_error']($request),
				'last_username' => $app['session']->get('_security.last_username'),
		));
    }

	public function listAction(Request $request, Application $app)
	{
		$entities = $app['repository.user']->findAll();

		return $app['twig']->render('User/list.html.twig', array('entities' => $entities));
	}

	public function showAction(Request $request, Application $app, $username)
	{
		if(!empty($username))
			$entity = $app['repository.user']->findByName($username, true);
		else
			$entity = $app['repository.user']->findByName($this->getCurrentUser($app)->getUsername(), true);

		return $app['twig']->render('User/show.html.twig', array('entity' => $entity));
	}

	public function newAction(Request $request, Application $app)
	{
		$entity = new User();
        $form = $this->createForm($app, $entity, false);

		return $app['twig']->render('User/new.html.twig', array('form' => $form->createView()));
	}

	public function createAction(Request $request, Application $app)
	{
		$entity = new User();
        $form = $this->createForm($app, $entity, false);
		$form->handleRequest($request);
		
		$params = $request->request->get("user");

		if($params["captcha"] != "" and $app["session"]->get("captcha_word") != $params["captcha"])
			$form->get("captcha")->addError(new FormError('Le mot doit correspondre à l\'image'));

		$this->checkForDoubloon($entity, $form, $app);

		if($form->isValid())
		{
			if(!is_null($entity->getAvatar()))
			{
				$image = uniqid()."_avatar.png";
				$entity->getAvatar()->move("photo/user/", $image);
				$entity->setAvatar($image);
			}

			$ph = new PasswordHash();
			$salt = $ph->create_hash($entity->getPassword());
			
			$encoder = new MessageDigestPasswordEncoder();
			$entity->setPassword($encoder->encodePassword($entity->getPassword(), $salt));
			
			$expiredAt = new \Datetime();
			$entity->setExpiredAt($expiredAt->modify("+1 day"));
			$entity->setToken(md5(uniqid(mt_rand(), true).$entity->getUsername()));
			$entity->setEnabled(false);
			$entity->setSalt($salt);
			
			$id = $app['repository.user']->save($entity);

			// Send email
			$body = $app['twig']->render('User/confirmationInscription_mail.html.twig', array("entity" => $entity));
		
			$mailer = new MailerPoeticus($app['swiftmailer.options']);
			$mailer->setBody($body);
			$mailer->setSubject("Poéticus - Inscription");
			$mailer->setSendTo($entity->getEmail());
		
			$mailer->send();

			return $app['twig']->render('User/confirmationInscription.html.twig', array('entity' => $entity));
		}

		return $app['twig']->render('User/new.html.twig', array('form' => $form->createView()));
	}

	public function editAction(Request $request, Application $app, $id)
	{
		if(!empty($id))
			$entity = $app['repository.user']->find($id, false);
		else
		{
			if(!$app['security.authorization_checker']->isGranted('IS_AUTHENTICATED_FULLY'))
				$app->abort(500);

			$entity = $app['repository.user']->findByName($this->getCurrentUser($app)->getUsername(), false);
		}
		$form = $this->createForm($app, $entity, true);
	
		return $app['twig']->render('User/edit.html.twig', array('form' => $form->createView(), 'entity' => $entity));
	}

	public function updateAction(Request $request, Application $app, $id)
	{
		if(empty($id))
			$id = $app['repository.user']->findByName($this->getCurrentUser($app)->getUsername(), true)->getId();

		$entity = $app['repository.user']->find($id);
		
		$current_avatar = $entity->getAvatar();

		$form = $this->createForm($app, $entity, true);
		$form->handleRequest($request);
		
		$this->checkForDoubloon($entity, $form, $app);

		if($form->isValid())
		{
			if(!is_null($entity->getAvatar()))
			{
				unlink("photo/user/".$current_avatar);
				$image = uniqid()."_avatar.png";
				$entity->getAvatar()->move("photo/user/", $image);
				$entity->setAvatar($image);
			}
			else
				$entity->setAvatar($current_avatar);
		
			$id = $app['repository.user']->save($entity, $id);

			$redirect = $app['url_generator']->generate('user_show', array('id' => $id));

			return $app->redirect($redirect);
		}
	
		return $app['twig']->render('User/edit.html.twig', array('form' => $form->createView(), 'entity' => $entity));
	}
	
	public function updatePasswordAction(Request $request, Application $app)
	{
		$entity = $app['repository.user']->findByName($this->getCurrentUser($app)->getUsername(), false);
		$form = $app['form.factory']->create(UpdatePasswordType::class, $entity);
		
		return $app['twig']->render('User/updatepassword.html.twig', array('form' => $form->createView(), 'entity' => $entity));
	}
	
	public function updatePasswordSaveAction(Request $request, Application $app)
	{
		$entity = $app['repository.user']->findByName($this->getCurrentUser($app)->getUsername(), false);
        $form = $app['form.factory']->create(UpdatePasswordType::class, $entity);
		$form->handleRequest($request);

		if($form->isValid())
		{
			$ph = new PasswordHash();
			$salt = $ph->create_hash($entity->getPassword());
			
			$encoder = new MessageDigestPasswordEncoder();
			$entity->setSalt($salt);
			$entity->setPassword($encoder->encodePassword($entity->getPassword(), $salt));
			$id = $app['repository.user']->save($entity, $entity->getId());

			$app['session']->getFlashBag()->add('new_password', 'Votre mot de passe a bien été modifié.');

			return $app->redirect($app['url_generator']->generate('user_show', array('id' => $id)));
		}
		
		return $app['twig']->render('User/updatepassword.html.twig', array('form' => $form->createView()));
	}
	
	public function forgottenPasswordAction(Request $request, Application $app)
	{
		$form = $app['form.factory']->create(ForgottenPasswordType::class, null);
	
		return $app['twig']->render('User/forgottenpassword.html.twig', array('form' => $form->createView()));
	}
	
	public function forgottenPasswordSendAction(Request $request, Application $app)
	{
        $form = $app['form.factory']->create(ForgottenPasswordType::class, null);
		$form->handleRequest($request);

		$params = $request->request->get("forgotten_password");

		if($params["captcha"] != "" and $app["session"]->get("captcha_word") != $params["captcha"])
			$form->get("captcha")->addError(new FormError('Le mot doit correspondre à l\'image'));

		$entity = $app['repository.user']->findByUsernameOrEmail($params["emailUsername"]);
		
		if(empty($entity))
			$form->get("emailUsername")->addError(new FormError("Le nom d'utilisateur ou l'adresse email n'existe pas"));

		if(!$form->isValid())
		{
			return $app['twig']->render('User/forgottenpassword.html.twig', array('form' => $form->createView()));
		}
		
		$temporaryPassword = $this->randomPassword();
		$ph = new PasswordHash();
		$salt = $ph->create_hash($temporaryPassword);

		$encoder = new MessageDigestPasswordEncoder();
		$entity->setSalt($salt);
		$entity->setPassword($encoder->encodePassword($temporaryPassword, $salt));
		$id = $app['repository.user']->save($entity, $entity->getId());

		// Send email
		$body = $app['twig']->render('User/forgottenpassword_mail.html.twig', array("entity" => $entity, "temporaryPassword" => $temporaryPassword));
	
		$mailer = new MailerPoeticus($app['swiftmailer.options']);
		$mailer->setBody($body);
		$mailer->setSubject("Poéticus - Mot de passe oublié");
		$mailer->setSendTo($entity->getEmail());
	
		$mailer->send();
		
		return $app['twig']->render('User/forgottenpasswordsend.html.twig');
	}

	private function randomPassword($length = 8)
	{
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789&+-$";
		
		if($length >= strlen($chars))
			$length = 8;
		
		$password = substr(str_shuffle($chars), 0, $length);
		
		return $password;
	}

	private function createForm($app, $entity, $ifEdit)
	{
		$language = $app['repository.language']->findOneByAbbreviation($app['generic_function']->getLocaleTwigRenderController());
		$countryForms = $app['repository.country']->findAllForChoice($language);
		$form = $app['form.factory']->create(UserType::class, $entity, array('countries' => $countryForms, 'edit' => $ifEdit));
		
		return $form;
	}

	private function checkForDoubloon($entity, $form, $app)
	{
		if($entity->getUsername() != null)
		{
			$checkForDoubloon = $app['repository.user']->checkForDoubloon($entity);

			if($checkForDoubloon > 0)
				$form->get("username")->addError(new FormError('Un utilisateur ayant le même nom d\'utilisateur / email existe déjà !'));
		}
	}
	
	private function createTemporaryPassword($email)
	{
		$key = strlen(uniqid());
		
		if(strlen($key) < strlen($email))
			$key = str_pad($key, strlen($email), $key, STR_PAD_RIGHT);
		elseif(strlen($key) > strlen($email))
		{
			$diff = strlen($key) - strlen($email);
			$key = substr($key, 0, -$diff);
		}
		
		return $email ^ $key;
	}
	
	private function getCurrentUser($app)
	{
		$token = $app['security.token_storage']->getToken();

		if(null !== $token)
			return $token->getUser();
			
		return false;
	}
	
	private function testStrongestPassword($form, $password)
	{
		$min_length = 5;
		
		$letter = array();
		$number = array();
		
		for($i = 0; $i < strlen($password); $i++)
		{
			$current = $password[$i];
			
			if(($current >= 'a' and $current <= 'z') or ($current >= 'A' and $current <= 'Z'))
				$letter[] = $current;
			if($current >= '0' and $current <= '9')
				$number[] = $current;
		}
		
		if(strlen($password) > 0)
		{
			if(strlen($password) < $min_length)
				$form->get("password")->addError(new FormError('Votre mot de passe doit contenir au moins '.$min_length.' caractères.'));
			else
			{
				if(count($letter) == 0)
					$form->get('password')->addError(new FormError('Votre mot de passe doit comporter au moins une lettre.'));
				if(count($number) == 0)
					$form->get('password')->addError(new FormError('Votre mot de passe doit comporter au moins un chiffre.'));
			}
		}
	}
	
	// Profil show
	//** Mes Poésies
	public function poemsUserDatatablesAction(Request $request, Application $app, $username)
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

		$entities = $app['repository.poem']->findPoemByUserAndAuhorType($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $username, $this->getCurrentUser($app), 'user');
		$iTotal = $app['repository.poem']->findPoemByUserAndAuhorType($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $username, $this->getCurrentUser($app), 'user', true);

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

			$show = $app['url_generator']->generate('read', array('id' => $entity->getId(), 'slug' => $entity->getSlug()));
			$row[] = '<a href="'.$show.'" alt="Show">'.$entity->getTitle().'</a>';

			if ($app['security.authorization_checker']->isGranted('IS_AUTHENTICATED_REMEMBERED') and $this->getCurrentUser($app)->getUsername() == $username) {
				$row[] = '<div class="state_poem '.$entity->getStateRealName().'">'.$translator->trans($entity->getStateString()).'</div>';
				$row[] = '<a href="'.$app['url_generator']->generate('poemuser_edit', array("id" => $entity->getId())).'" alt=""><span class="glyphicon glyphicon-pencil">'.$translator->trans('user.myProfile.Edit').'</span></a> / <a href="#" alt="" data-id="'.$entity->getId().'" class="delete_poem"><span class="glyphicon glyphicon-remove">'.$translator->trans('user.myProfile.Delete').'</span></a>';
			}
			
			$output['aaData'][] = $row;
		}

		$response = new Response(json_encode($output));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}
	
	//** Mes Votes
	public function votesUserDatatablesAction(Request $request, Application $app, $username)
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

		$entities = $app['repository.poemvote']->findVoteByUser($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $username);
		$iTotal = $app['repository.poemvote']->findVoteByUser($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $username, true);

		$output = array(
			"sEcho" => $request->query->get('sEcho'),
			"iTotalRecords" => $iTotal,
			"iTotalDisplayRecords" => $iTotal,
			"aaData" => array()
		);

		foreach($entities as $entity)
		{
			$row = array();

			$show = $app['url_generator']->generate('read', array('id' => $entity['id'], 'slug' => $entity['slug']));
			$row[] = '<a href="'.$show.'" alt="Show">'.$entity['title'].'</a>';
			
			list($icon, $color) = (($entity['vote'] == -1) ? array("fa-arrow-down", "red") : array("fa-arrow-up", "green"));
			$row[] = "<i class='fa ".$icon."' aria-hidden='true' style='color: ".$color.";'></i>";

			$output['aaData'][] = $row;
		}

		$response = new Response(json_encode($output));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}

	//** Mes Commentaires
	public function commentsUserDatatablesAction(Request $request, Application $app, $username)
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

		$entities = $app['repository.comment']->findCommentByUser($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $username);
		$iTotal = $app['repository.comment']->findCommentByUser($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $username, true);

		$output = array(
			"sEcho" => $request->query->get('sEcho'),
			"iTotalRecords" => $iTotal,
			"iTotalDisplayRecords" => $iTotal,
			"aaData" => array()
		);

		foreach($entities as $entity)
		{
			$row = array();

			$show = $app['url_generator']->generate('read', array('id' => $entity['id'], 'slug' => $entity['slug']));
			$row[] = '<a href="'.$show.'" alt="Show">'.$entity['title'].'</a>';
			$row[] = "le ".date_format(new \Datetime($entity['created_at']), "d/m/Y à H:i:s");

			$output['aaData'][] = $row;
		}

		$response = new Response(json_encode($output));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}
}