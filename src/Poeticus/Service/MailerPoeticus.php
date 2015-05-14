<?php
namespace Poeticus\Service;

class MailerPoeticus
{
	private $swiftMailerOptions;
	private $subject;
	private $sendTo;
	private $body;
	private $host;
	private $port;
	private $encryption;
	
	public function __construct($swiftMailerOptions)
	{
		$this->swiftMailerOptions = $swiftMailerOptions;
	}

	private function createTransport()
	{
		$transport = \Swift_SmtpTransport::newInstance($this->swiftMailerOptions["host"], $this->swiftMailerOptions["port"], $this->swiftMailerOptions["encryption"])
					->setUsername($this->swiftMailerOptions["username"])
					->setPassword($this->swiftMailerOptions["password"]);
					
		return $transport;
	}
	
	public function send()
	{
		// die(var_dump($this->transport));
		$mailer = \Swift_Mailer::newInstance($this->createTransport());

		$message = \Swift_Message::newInstance()
			->setSubject($this->subject)
			->setTo(array($this->sendTo))
			->setFrom(array($this->swiftMailerOptions["username"] => "Poéticus"))
			->setBody($this->body, 'text/html');
			
		return $mailer->send($message);
	}
	
	public function getSwiftMailerOptions()
	{
		return $this->swiftMailerOptions;
	}

	public function setSwiftMailerOptions($swiftMailerOptions)
	{
		$this->swiftMailerOptions = $swiftMailerOptions;
	}

	public function getSubject()
	{
		return $this->subject;
	}

	public function setSubject($subject)
	{
		$this->subject = $subject;
	}

	public function getBody()
	{
		return $this->body;
	}

	public function setBody($body)
	{
		$this->body = $body;
	}
	public function getSendTo()
	{
		return $this->sendTo;
	}

	public function setSendTo($sendTo)
	{
		$this->sendTo = $sendTo;
	}
}