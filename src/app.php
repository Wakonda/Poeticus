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
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
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
		'users' => $app->share(function () use ($app) {
			return new Poeticus\Controller\UserProvider($app['db']);
		})
    )
);

$app->register(new Silex\Provider\SecurityServiceProvider(), array(
    'security.firewalls' => $app['security.firewalls'],
	'security.role_hierarchy' => $app['security.role_hierarchy'],
	'security.access_rules' => $app['security.access_rules']
));

$app->register(new Silex\Provider\RememberMeServiceProvider());

$app->register(new Silex\Provider\TranslationServiceProvider(), array(
    'locale' => 'fr'
));

$app['translator'] = $app->share($app->extend('translator', function($translator, $app) {
    $translator->addLoader('yaml', new YamlFileLoader());

    $translator->addResource('yaml', __DIR__.'/Poeticus/Resources/translations/fr.yml', 'fr');
    $translator->addResource('yaml', __DIR__.'/Poeticus/Resources/translations/pt.yml', 'pt');
    $translator->addResource('yaml', __DIR__.'/Poeticus/Resources/translations/it.yml', 'it');

    return $translator;
}));

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
    if ($locale = $app['request']->get('lang') or $locale  = $app['request']->getSession()->get('_locale')) {
		$app['locale'] = $locale;
		$app['request']->setLocale($locale);
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

$app['twig']->addGlobal("dev", 1);

$app["twig"] = $app->share($app->extend("twig", function (\Twig_Environment $twig, Silex\Application $app) {
    $twig->addExtension(new Poeticus\Service\PoeticusExtension($app));
    return $twig;
}));

// Register repositories.
$app['repository.poem'] = $app->share(function ($app) {
    return new Poeticus\Repository\PoemRepository($app['db']);
});

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

$app->before(function () use ($app) {
    $app['twig']->addGlobal('generic_layout', $app['twig']->loadTemplate('generic_layout.html.twig'));
});

// Register repositories
$app['repository.poeticform'] = $app->share(function ($app) {
    return new Poeticus\Repository\PoeticFormRepository($app['db']);
});
$app['repository.country'] = $app->share(function ($app) {
    return new Poeticus\Repository\CountryRepository($app['db']);
});
$app['repository.biography'] = $app->share(function ($app) {
    return new Poeticus\Repository\BiographyRepository($app['db']);
});
$app['repository.collection'] = $app->share(function ($app) {
    return new Poeticus\Repository\CollectionRepository($app['db']);
});
$app['repository.version'] = $app->share(function ($app) {
    return new Poeticus\Repository\VersionRepository($app['db']);
});
$app['repository.poem'] = $app->share(function ($app) {
    return new Poeticus\Repository\PoemRepository($app['db']);
});
$app['repository.user'] = $app->share(function ($app) {
    return new Poeticus\Repository\UserRepository($app['db']);
});
$app['repository.contact'] = $app->share(function ($app) {
    return new Poeticus\Repository\ContactRepository($app['db']);
});
$app['repository.poemvote'] = $app->share(function ($app) {
    return new Poeticus\Repository\PoemVoteRepository($app['db']);
});
$app['repository.comment'] = $app->share(function ($app) {
	return new Poeticus\Repository\CommentRepository($app['db']);
});
$app['repository.page'] = $app->share(function ($app) {
	return new Poeticus\Repository\PageRepository($app['db']);
});

// Register controllers
$app["controllers.index"] = $app -> share(function($app) {
    return new Poeticus\Controller\IndexController();
});

$app["controllers.poeticformadmin"] = $app -> share(function($app) {
    return new Poeticus\Controller\PoeticFormAdminController();
});

$app["controllers.countryadmin"] = $app -> share(function($app) {
    return new Poeticus\Controller\CountryAdminController();
});

$app["controllers.biographyadmin"] = $app -> share(function($app) {
    return new Poeticus\Controller\BiographyAdminController();
});

$app["controllers.collectionadmin"] = $app -> share(function($app) {
    return new Poeticus\Controller\CollectionAdminController();
});

$app["controllers.poemadmin"] = $app -> share(function($app) {
    return new Poeticus\Controller\PoemAdminController();
});

$app["controllers.useradmin"] = $app -> share(function($app) {
    return new Poeticus\Controller\UserAdminController();
});

$app["controllers.admin"] = $app -> share(function($app) {
    return new Poeticus\Controller\AdminController();
});

$app["controllers.contact"] = $app -> share(function($app) {
    return new Poeticus\Controller\ContactController();
});

$app["controllers.contactadmin"] = $app -> share(function($app) {
    return new Poeticus\Controller\ContactAdminController();
});

$app["controllers.versionadmin"] = $app -> share(function($app) {
    return new Poeticus\Controller\VersionAdminController();
});

$app["controllers.user"] = $app -> share(function($app) {
    return new Poeticus\Controller\UserController();
});

$app["controllers.poemvote"] = $app -> share(function($app) {
    return new Poeticus\Controller\PoemVoteController();
});

$app["controllers.comment"] = $app -> share(function($app) {
    return new Poeticus\Controller\CommentController();
});

$app["controllers.sitemap"] = $app -> share(function($app) {
	return new Poeticus\Controller\SitemapController();
});

$app["controllers.sendpoem"] = $app -> share(function($app) {
	return new Poeticus\Controller\SendPoemController();
});

$app["controllers.pageadmin"] = $app -> share(function($app) {
	return new Poeticus\Controller\PageAdminController();
});

// Form extension
$app['form.type.extensions'] = $app->share($app->extend('form.type.extensions', function ($extensions) use ($app) {
    $extensions[] = new Poeticus\Form\Extension\ButtonTypeIconExtension();
    return $extensions;
}));

// SwiftMailer
// See http://silex.sensiolabs.org/doc/providers/swiftmailer.html
$app['swiftmailer.options'] = array(
	'host' => 'smtp.gmail.com',
	'port' => 465,
    'username' => 'test@gmail.com',
    'password' => 'test',
    'encryption' => 'ssl'
);

// Global
$app['web_directory'] = realpath(__DIR__."/../web");

return $app;