<?php

namespace Poeticus\Entity;

use Poeticus\Service\GenericFunction;

class Country
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
     * @var string
     */
    protected $internationalName;

	/**
	 *
	 * @var \Poeticus\Entity\Language
	 */
	protected $language;

    /**
     *
     * @var string
     */
    protected $slug;

	public function __toString()
	{
		$this->title;
	}
	
    /**
     *
     * @var flag
     */
    protected $flag;

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

    public function getInternationalName()
    {
        return $this->internationalName;
    }

    public function setInternationalName($internationalName)
    {
        $this->internationalName = $internationalName;
    }

    public function getFlag()
    {
        return $this->flag;
    }

    public function setFlag($flag)
    {
        $this->flag = $flag;
    }

	public function getLanguage()
	{
		return $this->language;
	}
	
	public function setLanguage($language)
	{
		$this->language = $language;
	}

    public function getSlug()
    {
        return $this->slug;
    }

    public function setSlug($slug)
    {
		$this->slug = GenericFunction::slugify($this->title);
    }
}