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
			'number_version' => new \Twig_Function_Method($this, 'getCurrentVersion')
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
	
	public function text_month($monthInt)
	{
		$arrayMonth = array("janvier", "février", "mars", "avril", "mai", "juin", "juillet", "août", "septembre", "octobre", "novembre", "décembre");
		
		return $arrayMonth[intval($monthInt) - 1];
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
		$arrayMonth = array("janvier", "février", "mars", "avril", "mai", "juin", "juillet", "août", "septembre", "octobre", "novembre", "décembre");
		
		$month = $arrayMonth[$date->format("n") - 1];
		
		$day = ($date->format("j") == 1) ? $date->format("j")."<sup>er</sup>" : $date->format("j");
		
		return $day." ".$month." ".$date->format("Y");
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
}