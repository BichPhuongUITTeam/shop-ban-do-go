<?php
namespace Users\Model;

use Zend\Crypt\Password\Bcrypt;
use Zend\Db\TableGateway\TableGateway;

class UsersTable
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function fetchAll()
    {
        $resultSet = $this->tableGateway->select();
        return $resultSet;
    }

    public function loginUser(Users $user)
    {
        $password_hash = $this->getUserPasswordHash($user->username);

        if ($password_hash) {
            return password_verify($user->password, $password_hash);
        } else {
            return FALSE;
        }
    }

    public function getUserFullName($username)
    {
        $username = (string)$username;
        $rowset = $this->tableGateway->select(array('username' => $username));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find user has username $username");
        }
        return $row->full_name;
    }
    public function getUserPasswordHash($username)
    {
        $username = (string)$username;
        $rowset = $this->tableGateway->select(array('username' => $username));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find user has username $username");
        }
        return $row->password;
    }

    public function verifyUserPassword($password, $hash)
    {
        $bcrypt = new Bcrypt();
        $verify = $bcrypt->verify($password, $hash);
        $result = ($verify) ? $password : $this->hashPassword($password);
        return $result;
    }

    public function hashPassword($password)
    {
        $bcrypt = new Bcrypt();
        return $bcrypt->create($password);
    }

    public function saveUser(Users $user)
    {
        $password = $user->password;
        $bcrypt = new Bcrypt();
        $hashed_password = $bcrypt->create($password);
        $data = array(
            'username' => $user->username,
            'email' => $user->email,
            'full_name' => $user->full_name,
            'password' => $hashed_password,
        );

        $id = (int)$user->id;

        if ($id === 0) {
            $data['created_at'] = $data['updated_at'] = date("Y-m-d H:i:s");
            $this->tableGateway->insert($data);
            $id = $this->tableGateway->lastInsertValue;
            $update_data['creator_id'] = $id;
            $this->tableGateway->update($update_data, array('id' => $id));
        } else {
            if ($this->getUserById($id)) {
                $data['updated_at'] = date("Y-m-d H:i:s");
                $this->tableGateway->update($data, array('id' => $id));
            } else {
                throw new \Exception("User $id does not exist");
            }
        }
    }

    public function getUserById($id)
    {
        $id = (int)$id;
        $rowset = $this->tableGateway->select(array('id' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find user has ID = $id");
        }
        return $row;
    }

    public function getUserByUsername($username)
    {
        $rowset = $this->tableGateway->select(array('username' => $username));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find user has username $username");
        }
        return $row;
    }

    public function deleteUser($id)
    {
        $this->tableGateway->delete(array('id' => (int)$id));
    }
}

