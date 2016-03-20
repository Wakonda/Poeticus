<?php

namespace Poeticus\Entity;

class Poem
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
    protected $text;

    /**
     *
     * @var string
     */
    protected $releasedDate;

    /**
     *
     * @var string
     */
    protected $authorType;

    /**
     *
     * @var \Poetic\Entity\PoeticForm
     */
    protected $poeticForm;

    /**
     *
     * @var \Poetic\Entity\Biography
     */
    protected $biography;

    /**
     *
     * @var \Poetic\Entity\Country
     */
    protected $country;

    /**
     *
     * @var \Poetic\Entity\Collection
     */
    protected $collection;
	
    /**
     *
     * @var \Poetic\Entity\User
     */
    protected $user;

    /**
     *
     * @var integer
     */
    protected $state;
	
    /**
     *
     * @var string
     */
    protected $photo;

	public function getStateString()
	{
		$res = "";
		
		switch($this->state)
		{
			case 0:
				$res = "Publié";
				break;
			case 1:
				$res = "Brouillon";
				break;
			case 2:
				$res = "Supprimé";
				break;
			default:
				$res = "";
		}
		
		return $res;
	}

	public function getStateRealName()
	{
		$res = "";
		
		switch($this->state)
		{
			case 0:
				$res = "published";
				break;
			case 1:
				$res = "draft";
				break;
			case 2:
				$res = "deleted";
				break;
			default:
				$res = "";
		}
		
		return $res;
	}
	
	public function isBiography()
	{
		return $this->authorType == "biography";
	}

	public function isUser()
	{
		return $this->authorType == "user";
	}
	
	public function getAuthor()
	{
		if($this->isBiography())
			return $this->biography;
		else
			return $this->user;
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

    public function getReleasedDate()
    {
        return $this->releasedDate;
    }

    public function setReleasedDate($releasedDate)
    {
        $this->releasedDate = $releasedDate;
    }

    public function getAuthorType()
    {
        return $this->authorType;
    }

    public function setAuthorType($authorType)
    {
        $this->authorType = $authorType;
    }

    public function getBiography()
    {
        return $this->biography;
    }

    public function setBiography($biography)
    {
        $this->biography = $biography;
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function setCountry($country)
    {
        $this->country = $country;
    }

    public function getCollection()
    {
        return $this->collection;
    }

    public function setCollection($collection)
    {
        $this->collection = $collection;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function getPoeticForm()
    {
        return $this->poeticForm;
    }

    public function setPoeticForm($poeticForm)
    {
        $this->poeticForm = $poeticForm;
    }

    public function getState()
    {
        return $this->state;
    }

    public function setState($state)
    {
        $this->state = $state;
    }

    public function getPhoto()
    {
        return $this->photo;
    }

    public function setPhoto($photo)
    {
        $this->photo = $photo;
    }
}