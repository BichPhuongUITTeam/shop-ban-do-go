<?php
namespace Users\Model;
use Zend\Authentication\Adapter\AdapterInterface;

class AuthAdapter implements AdapterInterface
{
    protected $username;
    protected $password;

    /**
     *
     */
    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    public function authenticate()
    {

    }
}