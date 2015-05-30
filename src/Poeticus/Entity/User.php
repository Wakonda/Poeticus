<?php

namespace Poeticus\Entity;

use Symfony\Component\Security\Core\User\AdvancedUserInterface;

class User implements AdvancedUserInterface
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
    protected $username;

    /**
     *
     * @var string
     */
    protected $password;

    /**
     *
     * @var string
     */
    protected $roles;

    /**
     *
     * @var boolean
     */
    protected $enabled;

    /**
     *
     * @var string
     */
    protected $confirmation_token;

    /**
     *
     * @var string
     */
    protected $email;

    /**
     *
     * @var string
     */
    protected $avatar;

    /**
     *
     * @var string
     */
    protected $gravatar;

    /**
     *
     * @var text
     */
    protected $presentation;

    /**
     *
     * @var datetime
     */
    protected $expiration_token;

    /**
     *
     * @var datetime
     */
    protected $registrationDate;
	
    /**
     *
     * @var \Poetic\Entity\Country
     */
    protected $country;

    /**
     *
     * @var string
     */
    protected $salt;

    /**
     *
     * @var string
     */
    protected $token;

    /**
     *
     * @var datetime
     */
    protected $expired_at;

    public function __construct($username = "", $password = "", $salt = "", array $roles = array("ROLE_USER"), $enabled = true, $userNonExpired = true, $credentialsNonExpired = true, $userNonLocked = true)
    {
        $this->username = $username;
        $this->password = $password;
		$this->salt = $salt;
        $this->enabled = $enabled;
        $this->accountNonExpired = $userNonExpired;
        $this->credentialsNonExpired = $credentialsNonExpired;
        $this->accountNonLocked = $userNonLocked;
        $this->roles = $roles;
    }
	
    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($username)
    {
        $this->username = $username;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function setRoles($roles)
    {
        $this->roles = $roles;
    }

    public function getEnabled()
    {
        return $this->enabled;
    }

    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    public function getConfirmation_token()
    {
        return $this->confirmation_token;
    }

    public function setConfirmation_token($confirmation_token)
    {
        $this->confirmation_token = $confirmation_token;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getAvatar()
    {
        return $this->avatar;
    }

    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;
    }
	
	public function getGravatar()
    {
        return $this->gravatar;
    }

    public function setGravatar($gravatar)
    {
        $this->gravatar = $gravatar;
    }

    public function getPresentation()
    {
        return $this->presentation;
    }

    public function setPresentation($presentation)
    {
        $this->presentation = $presentation;
    }

    public function getExpiration_token()
    {
        return $this->expiration_token;
    }

    public function setExpiration_token($expiration_token)
    {
        $this->expiration_token = $expiration_token;
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function setCountry($country)
    {
        $this->country = $country;
    }

	public function getRegistrationDate()
	{
		return $this->registrationDate;
	}
	
	public function setRegistrationDate($registrationDate)
	{
		$this->registrationDate = $registrationDate;
	}
	
	public function setSalt($salt)
	{
		$this->salt = $salt;
	}

	public function getToken()
	{
		return $this->token;
	}
	
	public function setToken($token)
	{
		$this->token = $token;
	}

	public function getExpiredAt()
	{
		return $this->expired_at;
	}
	
	public function setExpiredAt($expired_at)
	{
		$this->expired_at = $expired_at;
	}

    /**
     * {@inheritdoc}
     */
    public function getSalt()
    {
        return $this->salt;
    }
	
    /**
     * {@inheritdoc}
     */
    public function isAccountNonExpired()
    {
        return $this->accountNonExpired;
    }

    /**
     * {@inheritdoc}
     */
    public function isAccountNonLocked()
    {
        return $this->accountNonLocked;
    }

    /**
     * {@inheritdoc}
     */
    public function isCredentialsNonExpired()
    {
        return $this->credentialsNonExpired;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
    }
}
