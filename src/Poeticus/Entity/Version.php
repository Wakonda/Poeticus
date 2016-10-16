<?php

namespace Poeticus\Entity;

class Version
{
    /**
     *
     * @var integer
     */
    protected $id;

    /**
     *
     * @var file
     */
    protected $file;
	
    /**
     *
     * @var text
     */
    protected $versionNumber;
	
	/**
     *
     * @var date
     */
    protected $releaseDate;

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

    public function getFile()
    {
        return $this->file;
    }

    public function setFile($file)
    {
        $this->file = $file;
    }

    public function getVersionNumber()
    {
        return $this->versionNumber;
    }

    public function setVersionNumber($versionNumber)
    {
        $this->versionNumber = $versionNumber;
    }

    public function getReleaseDate()
    {
        return $this->releaseDate;
    }

    public function setReleaseDate($releaseDate)
    {
        $this->releaseDate = $releaseDate;
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