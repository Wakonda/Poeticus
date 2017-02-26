<?php

namespace Poeticus\Entity;

class Contact
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
    protected $subject;

    /**
     *
     * @var string
     */
    protected $mail;

    /**
     *
     * @var text
     */
    protected $message;

	/**
     *
     * @var string
     */
    protected $readMessage;

	/**
     *
     * @var datetime
     */
    protected $dateSending;

	public function __construct()
	{
		$this->readMessage = 0;
	}

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    public function getMail()
    {
        return $this->mail;
    }

    public function setMail($mail)
    {
        $this->mail = $mail;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function getReadMessage()
    {
        return $this->readMessage;
    }

    public function setReadMessage($readMessage)
    {
        $this->readMessage = $readMessage;
    }

    public function getDateSending()
    {
        return $this->dateSending;
    }

    public function setDateSending($dateSending)
    {
        $this->dateSending = $dateSending;
    }
}