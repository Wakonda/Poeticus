<?php

namespace Poeticus\Controller;

use Poeticus\Entity\Poem;
use Poeticus\Form\Type\PoemType;
use Silex\Application;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

class AdminController
{
    public function indexAction(Request $request, Application $app)
    {
        return $app['twig']->render('Admin/index.html.twig');
    }
}
