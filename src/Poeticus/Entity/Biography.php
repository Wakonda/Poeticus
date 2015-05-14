<?php

namespace Poeticus\Entity;

class Biography
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
     * @var text
     */
    protected $dayBirth;
	/**
     *
     * @var text
     */
    protected $monthBirth;
	/**
     *
     * @var text
     */
    protected $yearBirth;

	/**
     *
     * @var text
     */
    protected $dayDeath;
	/**
     *
     * @var text
     */
    protected $monthDeath;
	/**
     *
     * @var text
     */
    protected $yearDeath;
	
    /**
     *
     * @var image
     */
    protected $photo;

    /**
     *
     * @var \Poetic\Entity\Country
     */
    protected $country;
	
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

    public function getPhoto()
    {
        return $this->photo;
    }

    public function setPhoto($photo)
    {
        $this->photo = $photo;
    }

    public function getDayBirth()
    {
        return $this->dayBirth;
    }

    public function setDayBirth($dayBirth)
    {
        $this->dayBirth = $dayBirth;
    }

    public function getMonthBirth()
    {
        return $this->monthBirth;
    }

    public function setMonthBirth($monthBirth)
    {
        $this->monthBirth = $monthBirth;
    }

    public function getYearBirth()
    {
        return $this->yearBirth;
    }

    public function setYearBirth($yearBirth)
    {
        $this->yearBirth = $yearBirth;
    }

    public function getDayDeath()
    {
        return $this->dayDeath;
    }

    public function setDayDeath($dayDeath)
    {
        $this->dayDeath = $dayDeath;
    }

    public function getMonthDeath()
    {
        return $this->monthDeath;
    }

    public function setMonthDeath($monthDeath)
    {
        $this->monthDeath = $monthDeath;
    }

    public function getYearDeath()
    {
        return $this->yearDeath;
    }

    public function setYearDeath($yearDeath)
    {
        $this->yearDeath = $yearDeath;
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function setCountry($country)
    {
        $this->country = $country;
    }
}