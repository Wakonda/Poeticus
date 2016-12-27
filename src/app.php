<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\Loader\YamlFileLoader;

// Register service providers.
$app->register(new Silex\Provider\ServiceControllerServiceProvider());

$app->register(new Silex\Provider\DoctrineServiceProvider());
$app->register(new Silex\Provider\FormServiceProvider());
$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\ValidatorServiceProvider());
$app->register(new Silex\Provider\RoutingServiceProvider());
$app->register(new Silex\Provider\TranslationServiceProvider());
$app->register(new Silex\Provider\SwiftmailerServiceProvider());
$app->register(new Silex\Provider\HttpFragmentServiceProvider());
// 

$app['security.role_hierarchy'] = array(
    'ROLE_ADMIN' => array('ROLE_USER'),
);

$app['security.access_rules'] = array(
    array('^/admin', 'ROLE_ADMIN'),
);

$app['security.firewalls'] = array(
    'main' => array(
        'pattern' => '^/',
		'anonymous' => true,
		'remember_me' => array('key' => '}Gp#qsZ^9HBR8^V%2vJz'),
		'form' => array('login_path' => '/user/login', 'check_path' => '/admin/login_check','default_target_path'=> '/','always_use_default_target_path'=>true),
		'logout' => array('logout_path' => '/admin/logout'),
		'users' => function ($app) {
			return new Poeticus\Controller\UserProvider($app['db']);
		}
    )
);

$app->register(new Silex\Provider\SecurityServiceProvider(), array(
    'security.firewalls' => $app['security.firewalls'],
	'security.role_hierarchy' => $app['security.role_hierarchy'],
	'security.access_rules' => $app['security.access_rules']
));

$app->register(new Silex\Provider\RememberMeServiceProvider());

$app['security.default_encoder'] = function ($app) {
    return $app['security.encoder.digest'];
};

$app->register(new Silex\Provider\TranslationServiceProvider(), array(
    'locale' => 'fr',
	'translator.domains' => array()
));

$app['translator'] = $app->extend('translator', function($translator, $app) {
    $translator->addLoader('yaml', new YamlFileLoader());

    $translator->addResource('yaml', __DIR__.'/Poeticus/Resources/translations/fr.yml', 'fr');
    $translator->addResource('yaml', __DIR__.'/Poeticus/Resources/translations/pt.yml', 'pt');
    $translator->addResource('yaml', __DIR__.'/Poeticus/Resources/translations/it.yml', 'it');

    return $translator;
});

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.orm.proxies_namespace'     => 'DoctrineProxy',
    'db.orm.auto_generate_proxies' => true,
    'db.orm.entities'              => array(array(
        'type'      => 'annotation',       // как определяем поля в Entity
        'path'      => __DIR__,   // Путь, где храним классы
        'namespace' => 'Poeticus\Entity', // Пространство имен
    )),
));

$app->before(function () use ($app) {
	$request = $app['request_stack']->getCurrentRequest();

    if ($locale = $app['locale'] or $locale = $request->get('lang') or $locale = $request->getSession()->get('_locale')) {
		$app['locale'] = $locale;
		$app['translator']->setLocale($locale);
		$request->getSession()->set('_locale', $locale);

		if(!empty($request))
			$request->setLocale($locale);
    }
});

$app->boot();

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.options' => array(
        'cache' => isset($app['twig.options.cache']) ? $app['twig.options.cache'] : false,
        'strict_variables' => true,
    ),
    'twig.path' => array(__DIR__ . '/Poeticus/Resources/views')
));

$app["twig"] = $app->extend("twig", function (\Twig_Environment $twig, Silex\Application $app) {
    $twig->addExtension(new Poeticus\Service\PoeticusExtension($app));
    return $twig;
});

$app['twig']->addGlobal("dev", 1);

// Register repositories.
$app['repository.poem'] = function ($app) {
    return new Poeticus\Repository\PoemRepository($app['db']);
};

$app->before(function () use ($app) {
    $app['twig']->addGlobal('generic_layout', $app['twig']->loadTemplate('generic_layout.html.twig'));
}, \Silex\Application::EARLY_EVENT);

// Register the error handler.
$app->error(function (\Exception $e, $code) use ($app) {
    if ($app['debug']) {
        return;
    }

    switch ($code) {
        case 404:
            $message = 'The requested page could not be found.';
            break;
        default:
            $message = 'We are sorry, but something went terribly wrong.';
    }
	
	return $app['twig']->render('Index/error.html.twig', array('code' => $code, 'message' => $e->getMessage()));
});


// Register repositories
$app['repository.poeticform'] = function ($app) {
    return new Poeticus\Repository\PoeticFormRepository($app['db']);
};
$app['repository.country'] = function ($app) {
    return new Poeticus\Repository\CountryRepository($app['db']);
};
$app['repository.biography'] = function ($app) {
    return new Poeticus\Repository\BiographyRepository($app['db']);
};
$app['repository.collection'] = function ($app) {
    return new Poeticus\Repository\CollectionRepository($app['db']);
};
$app['repository.version'] = function ($app) {
    return new Poeticus\Repository\VersionRepository($app['db']);
};
$app['repository.poem'] = function ($app) {
    return new Poeticus\Repository\PoemRepository($app['db']);
};
$app['repository.user'] = function ($app) {
    return new Poeticus\Repository\UserRepository($app['db']);
};
$app['repository.contact'] = function ($app) {
    return new Poeticus\Repository\ContactRepository($app['db']);
};
$app['repository.poemvote'] = function ($app) {
    return new Poeticus\Repository\PoemVoteRepository($app['db']);
};
$app['repository.comment'] = function ($app) {
	return new Poeticus\Repository\CommentRepository($app['db']);
};
$app['repository.page'] = function ($app) {
	return new Poeticus\Repository\PageRepository($app['db']);
};
$app['repository.language'] = function ($app) {
	return new Poeticus\Repository\LanguageRepository($app['db']);
};

// Register controllers
$app["controllers.index"] = function($app) {
    return new Poeticus\Controller\IndexController();
};

$app["controllers.poeticformadmin"] = function($app) {
    return new Poeticus\Controller\PoeticFormAdminController();
};

$app["controllers.countryadmin"] = function($app) {
    return new Poeticus\Controller\CountryAdminController();
};

$app["controllers.biographyadmin"] = function($app) {
    return new Poeticus\Controller\BiographyAdminController();
};

$app["controllers.collectionadmin"] = function($app) {
    return new Poeticus\Controller\CollectionAdminController();
};

$app["controllers.poemadmin"] = function($app) {
    return new Poeticus\Controller\PoemAdminController();
};

$app["controllers.useradmin"] = function($app) {
    return new Poeticus\Controller\UserAdminController();
};

$app["controllers.admin"] = function($app) {
    return new Poeticus\Controller\AdminController();
};

$app["controllers.contact"] = function($app) {
    return new Poeticus\Controller\ContactController();
};

$app["controllers.contactadmin"] = function($app) {
    return new Poeticus\Controller\ContactAdminController();
};

$app["controllers.versionadmin"] = function($app) {
    return new Poeticus\Controller\VersionAdminController();
};

$app["controllers.user"] = function($app) {
    return new Poeticus\Controller\UserController();
};

$app["controllers.poemvote"] = function($app) {
    return new Poeticus\Controller\PoemVoteController();
};

$app["controllers.comment"] = function($app) {
    return new Poeticus\Controller\CommentController();
};

$app["controllers.sitemap"] = function($app) {
	return new Poeticus\Controller\SitemapController();
};

$app["controllers.sendpoem"] = function($app) {
	return new Poeticus\Controller\SendPoemController();
};

$app["controllers.pageadmin"] = function($app) {
	return new Poeticus\Controller\PageAdminController();
};

// Register Services
$app['generic_function'] = function ($app) {
    return new Poeticus\Service\GenericFunction($app);
};

// Form extension
$app['form.type.extensions'] = $app->extend('form.type.extensions', function ($extensions) use ($app) {
    $extensions[] = new Poeticus\Form\Extension\ButtonTypeIconExtension();
    return $extensions;
});

// SwiftMailer
// See http://silex.sensiolabs.org/doc/providers/swiftmailer.html
$app['swiftmailer.options'] = array(
	'host' => 'smtp.gmail.com',
	'port' => 465,
    'username' => 'amatukami66@gmail.com',
    'password' => 'rclens66', // k+W13uz5
    'encryption' => 'ssl'
);

// Global
$app['languages'] = array('fr' => 'fr_FR', 'it' => 'it_IT', 'pt' => 'pt_PT');
$app['web_directory'] = realpath(__DIR__."/../web");

return $app;