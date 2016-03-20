<?php

namespace Poeticus\Entity;

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
    protected $typeContentPoem;

    /**
     *
     * @var image
     */
    protected $image;
	
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
}
