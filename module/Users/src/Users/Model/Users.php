<?php
namespace Users\Model;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Users implements InputFilterAwareInterface
{
    public $id;
    public $username;
    public $email;
    public $full_name;
    public $password;
    public $created_at;
    protected $updated_at;
    protected $creator_id;
    protected $confirm_password;
    protected $remember_me;
    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->id = (!empty($data['id'])) ? $data['id'] : null;
        $this->username = (!empty($data['username'])) ? $data['username'] : null;
        $this->email = (!empty($data['email'])) ? $data['email'] : null;
        $this->full_name = (!empty($data['full_name'])) ? $data['full_name'] : null;
        $this->password = (!empty($data['password'])) ? $data['password'] : null;
        $this->confirm_password = (!empty($data['confirm_password'])) ? $data['confirm_password'] : null;
        $this->created_at = (!empty($data['created_at'])) ? $data['created_at'] : null;
        $this->updated_at = (!empty($data['updated_at'])) ? $data['updated_at'] : null;
        $this->creator_id = (!empty($data['creator_id'])) ? $data['creator_id'] : null;
        $this->remember_me = (!empty($data['remember_me'])) ? $data['remember_me'] : null;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }

    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();

            // User ID
            $inputFilter->add(array(
                'name' => 'id',
                'required' => true,
                'filters' => array(
                    array('name' => 'int'),
                ),
            ));

            // Username
            $inputFilter->add(array(
                'name' => 'username',
                'required' => true,
                'filters' => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name' => 'NotEmpty',
                    ),
                    array(
                        'name' => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min' => 5,
                            'max' => 100,
                        ),
                    ),
                ),
            ));

            // Full name
            $inputFilter->add(array(
                'name' => 'full_name',
                'required' => false,
                'filters' => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name' => 'Alpha',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'allowWhiteSpace' => true,
                        ),
                    ),
                    array(
                        'name' => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min' => 1,
                            'max' => 100,
                        ),
                    ),
                ),
            ));

            // Email address
            $inputFilter->add(array(
                'name' => 'email',
                'required' => true,
                'filters' => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name' => 'NotEmpty',
                    ),
                    array(
                        'name' => 'EmailAddress',
                        'options' => array(
                            'message' => array(
                                \Zend\Validator\EmailAddress::INVALID_FORMAT => 'Email address format is invalid',
                            ),
                        ),
                    ),
                ),
            ));

            //Password
            $inputFilter->add(array(
                'name' => 'password',
                'required' => true,
                'validators' => array(
                    array(
                        'name' => 'NotEmpty',
                    ),
                    array(
                        'name' => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min' => 5,
                            'max' => 20,
                        ),
                    ),
                ),
            ));

            // Confirm Password
            $inputFilter->add(array(
                'name' => 'confirm_password',
                'required' => true,
                'validators' => array(
                    array(
                        'name' => 'Identical',
                        'options' => array(
                            'token' => 'password', // name of `password` field
                        ),
                    ),),
            ));

            // Remember me
            $inputFilter->add(array(
                'name' => 'remember_me',
                'required' => false,
            ));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

    public function getEditInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();

            // User ID
            $inputFilter->add(array(
                'name' => 'id',
                'required' => true,
                'filters' => array(
                    array('name' => 'int'),
                ),
            ));

            // Username
            $inputFilter->add(array(
                'name' => 'username',
                'required' => true,
                'filters' => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name' => 'NotEmpty',
                    ),
                    array(
                        'name' => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min' => 5,
                            'max' => 100,
                        ),
                    ),
                ),
            ));

            // Full name
            $inputFilter->add(array(
                'name' => 'full_name',
                'required' => false,
                'filters' => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name' => 'Alpha',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'allowWhiteSpace' => true,
                        ),
                    ),
                    array(
                        'name' => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min' => 1,
                            'max' => 100,
                        ),
                    ),
                ),
            ));

            // Email address
            $inputFilter->add(array(
                'name' => 'email',
                'required' => true,
                'filters' => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name' => 'NotEmpty',
                    ),
                    array(
                        'name' => 'EmailAddress',
                        'options' => array(
                            'message' => array(
                                \Zend\Validator\EmailAddress::INVALID_FORMAT => 'Email address format is invalid',
                            ),
                        ),
                    ),
                ),
            ));

            //Password
            $inputFilter->add(array(
                'name' => 'password',
                'required' => false,
                'validators' => array(
                    array(
                        'name' => 'NotEmpty',
                    ),
                    array(
                        'name' => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min' => 5,
                            'max' => 20,
                        ),
                    ),
                ),
            ));

            // Remember me
            $inputFilter->add(array(
                'name' => 'remember_me',
                'required' => false,
            ));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

    public function getLoginInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();

            // User ID
//            $inputFilter->add(array(
//                'name' => 'id',
//                'required' => true,
//                'filters' => array(
//                    array('name' => 'int'),
//                ),
//            ));

            // Username
            $inputFilter->add(array(
                'name' => 'username',
                'required' => true,
                'filters' => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name' => 'NotEmpty',
                    ),
                    array(
                        'name' => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min' => 5,
                            'max' => 100,
                        ),
                    ),
                ),
            ));

            //Password
            $inputFilter->add(array(
                'name' => 'password',
                'required' => true,
                'validators' => array(
                    array(
                        'name' => 'NotEmpty',
                    ),
                    array(
                        'name' => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min' => 5,
                            'max' => 20,
                        ),
                    ),
                ),
            ));

            // Remember me
            $inputFilter->add(array(
                'name' => 'remember_me',
                'required' => false,
            ));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}