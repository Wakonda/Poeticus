<?php

namespace Poeticus\Service;

use Poeticus\Service\Captcha;

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
            "text_month"        => new \Twig_Filter_Method($this, "text_month"),
            "max_size_image"        => new \Twig_Filter_Method($this, "maxSizeImage", array('is_safe' => array('html'))),
        );
    }
	
	public function getFunctions() {
		return array(
			'captcha' => new \Twig_Function_Method($this, 'generateCaptcha')
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
	
	public function maxSizeImage($img, $basePath, array $options = null)
	{
		$imageSize = getimagesize($img);
		$width = $imageSize[0];
		$height = $imageSize[1];
		
		$max_width = 500;
				
		if($width > $max_width)
		{
			$height = ($max_width * $height) / $width;
			$width = $max_width;
		}

		return '<img src="'.$basePath.'/'.$img.'" alt="" style="max-width: '.$width.'px;" />';
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
}