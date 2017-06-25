<?php

namespace Poeticus\Entity;

use Poeticus\Service\GenericFunction;

class PoeticForm
{
    /**
     *
     * @var integer
     */
    protected $id;

    /**
     *
     * @var string
     */
    protected $title;

    /**
     *
     * @var text
     */
    protected $text;

    /**
     *
     * @var string
     */
    protected $slug;

    /**
     *
     * @var string
     */
    protected $typeContentPoem;

    /**
     *
     * @var image
     */
    protected $image;

	/**
	 *
	 * @var \Poeticus\Entity\Language
	 */
	protected $language;
	
	const IMAGETYPE = "image"; 
	const TEXTTYPE = "text"; 
	
	public function __construct()
	{
		$this->typeContentPoem = self::TEXTTYPE;
	}

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getText()
    {
        return $this->text;
    }

    public function setText($text)
    {
        $this->text = $text;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function setSlug($slug)
    {
		$this->slug = GenericFunction::slugify($this->title);
    }

    public function getImage()
    {
        return $this->image;
    }

    public function setImage($image)
    {
        $this->image = $image;
    }

    public function getTypeContentPoem()
    {
        return $this->typeContentPoem;
    }

    public function setTypeContentPoem($typeContentPoem)
    {
        $this->typeContentPoem = $typeContentPoem;
    }

	public function getLanguage()
	{
		return $this->language;
	}
	
	public function setLanguage($language)
	{
		$this->language = $language;
	}
}