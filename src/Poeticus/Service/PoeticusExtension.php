<?php

namespace Poeticus\Service;

use Poeticus\Service\Captcha;
use Poeticus\Service\Gravatar;

class PoeticusExtension extends \Twig_Extension
{
	private $app;
	
	public function __construct($app)
	{
		$this->app = $app;
	}
	
    public function getName() {
        return "poetic_extension";
    }

    public function getFilters() {
        return array(
            "var_dump"        => new \Twig_Filter_Method($this, "var_dump"),
            "toString"        => new \Twig_Filter_Method($this, "getStringObject"),
            "text_month"      => new \Twig_Filter_Method($this, "text_month"),
            "max_size_image"  => new \Twig_Filter_Method($this, "maxSizeImage", array('is_safe' => array('html'))),
            "date_letter"  	  => new \Twig_Filter_Method($this, "dateLetter", array('is_safe' => array('html'))),
            "remove_control_characters"  => new \Twig_Filter_Method($this, "removeControlCharacters")
        );
    }
	
	public function getFunctions() {
		return array(
			'captcha' => new \Twig_Function_Method($this, 'generateCaptcha'),
			'gravatar' => new \Twig_Function_Method($this, 'generateGravatar'),
			'number_version' => new \Twig_Function_Method($this, 'getCurrentVersion'),
			'current_url' => new \Twig_Function_Method($this, 'getCurrentURL'),
			'code_by_language' => new \Twig_Function_Method($this, 'getCodeByLanguage')
		);
	}

    public function getStringObject($arraySubEntity, $element) {
		if(!is_null($arraySubEntity) and array_key_exists ($element, $arraySubEntity))
			return $arraySubEntity[$element];

        return "";
    }
	
    public function var_dump($object) {
        return var_dump($object);
    }
	
	public function text_month($month, $year)
	{
		$locale = $this->app['generic_function']->getLocaleTwigRenderController();
		$arrayMonth = $this->formatDateByLocale();
		return $arrayMonth[$locale]["months"][intval($month) - 1].(!empty($year) ? $arrayMonth[$locale]["separator"].$year : "");
	}
	
	public function maxSizeImage($img, $basePath, array $options = null, $isPDF = false)
	{
		$basePath = ($isPDF) ? '' : $basePath.'/';
		
		if(!file_exists($img))
			return '<img src="'.$basePath.'photo/640px-Starry_Night_Over_the_Rhone.jpg" alt="" style="max-width: 400px" />';
		
		$imageSize = getimagesize($img);

		$width = $imageSize[0];
		$height = $imageSize[1];
		
		$max_width = 500;
				
		if($width > $max_width)
		{
			$height = ($max_width * $height) / $width;
			$width = $max_width;
		}

		return '<img src="'.$basePath.$img.'" alt="" style="max-width: '.$width.'px;" />';
	}
	
	public function dateLetter($date)
	{
		if(is_string($date))
			$date = new \DateTime($date);
		
		$locale = $this->app['generic_function']->getLocaleTwigRenderController();
		$arrayMonth = $this->formatDateByLocale();
		
		$month = $arrayMonth[$locale]["months"][$date->format("n") - 1];
		$day = ($date->format("j") == 1) ? $date->format("j").((!empty($arrayMonth[$locale]["sup"])) ? "<sup>".$arrayMonth[$locale]["sup"]."</sup>" : "") : $date->format("j");
		
		return $day.$arrayMonth[$locale]["separator"].$month.$arrayMonth[$locale]["separator"].$date->format("Y");
	}

	public function removeControlCharacters($string)
	{
		return preg_replace("/[^a-zA-Z0-9 .\-_;!:?äÄöÖüÜß<>='\"]/", "", $string);
	}
	
	public function generateCaptcha()
	{
		$captcha = new Captcha($this->app);

		$wordOrNumberRand = rand(1, 2);
		$length = rand(3, 7);

		if($wordOrNumberRand == 1)
			$word = $captcha->wordRandom($length);
		else
			$word = $captcha->numberRandom($length);
		
		return $captcha->generate($word);
	}

	public function generateGravatar()
	{
		$gr = new Gravatar();

		return $gr->getURLGravatar();
	}
	
	public function getCurrentVersion()
	{
		return $this->app['repository.version']->getCurrentVersion();
	}
	
	public function getCurrentURL($server)
	{
		return $server->get("REQUEST_SCHEME").'://'.$server->get("SERVER_NAME").$server->get("REQUEST_URI");
	}
	
	public function getCodeByLanguage()
	{
		$locale = $this->app['generic_function']->getLocaleTwigRenderController();
		
		switch($locale)
		{
			case "it":
				return "it";
			case "pt":
				return "pt_PT";
			default:
				return "fr_FR";
		}
	}
	
	private function formatDateByLocale()
	{
		$arrayMonth = array();
		$arrayMonth['fr'] = array("sup" => "er", "separator" => " ", "months" => array("janvier", "février", "mars", "avril", "mai", "juin", "juillet", "août", "septembre", "octobre", "novembre", "décembre"));
		$arrayMonth['it'] = array("sup" => "°", "separator" => " ", "months" => array("gennaio", "febbraio", "marzo", "aprile", "maggio", "guigno", "luglio", "agosto", "settembre", "ottobre", "novembre", "dicembre"));
		$arrayMonth['pt'] = array("sup" => null, "separator" => " de ", "months" => array("janeiro", "fevereiro", "março", "abril", "maio", "junho", "julho", "agosto", "setembro", "outubro", "novembro", "dezembro"));
	
		return $arrayMonth;
	}
}