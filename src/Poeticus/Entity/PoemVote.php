<?php

namespace Poeticus\Entity;

class PoemVote
{
    /**
     *
     * @var integer
     */
    protected $id;

    /**
     *
     * @var integer
     */
    protected $vote;

    /**
     *
     * @var \Poetic\Entity\Poem
     */
    protected $poem;

    /**
     *
     * @var \Poetic\Entity\User
     */
    protected $user;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getVote()
    {
        return $this->vote;
    }

    public function setVote($vote)
    {
        $this->vote = $vote;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function getPoem()
    {
        return $this->poem;
    }

    public function setPoem($poem)
    {
        $this->poem = $poem;
    }
}
