<?php
namespace Poeticus\Service;

class GenericFunction
{
	private $app;
	
	public function __construct($app)
	{
		$this->app = $app;
	}
	
	public function setLocaleTwigRenderController()
	{
		$request = $this->app['request_stack']->getCurrentRequest();
		$locale = (empty($request->getSession()->get("_locale"))) ? $this->app['locale'] : $request->getSession()->get("_locale");
		$this->app['translator']->setLocale($locale);
	}

	public function getLocaleTwigRenderController()
	{
		$request = $this->app['request_stack']->getCurrentRequest();
		return (empty($request->getSession()->get("_locale"))) ? $this->app['locale'] : $request->getSession()->get("_locale");
	}
}