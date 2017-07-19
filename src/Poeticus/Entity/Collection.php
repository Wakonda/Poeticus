<?php

namespace Poeticus\Entity;

use Poeticus\Service\GenericFunction;

class Collection
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
     * @var image
     */
    protected $image;

    /**
     *
     * @var string
     */
    protected $releasedDate;
	
    /**
     *
     * @var \Poetic\Entity\Biography
     */
    protected $biography;
	
	/**
	 *
	 * @var text
	 */
	protected $widgetProduct;
	
	/**
	 *
	 * @var \Poeticus\Entity\Language
	 */
	protected $language;
	
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
		$this->setSlug();
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

    public function setSlug()
    {
		if(empty($this->slug))
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

    public function getReleasedDate()
    {
        return $this->releasedDate;
    }

    public function setReleasedDate($releasedDate)
    {
        $this->releasedDate = $releasedDate;
    }

    public function getBiography()
    {
        return $this->biography;
    }

    public function setBiography($biography)
    {
        $this->biography = $biography;
    }

    public function getWidgetProduct()
    {
        return $this->widgetProduct;
    }

    public function setWidgetProduct($widgetProduct)
    {
        $this->widgetProduct = $widgetProduct;
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
