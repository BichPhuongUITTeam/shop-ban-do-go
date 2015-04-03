<?php
namespace User\Model;

use Zend\Db\TableGateway\TableGateway;

class UserTable
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

	public function getUser($id)
	{
		$id = (int) $id;
		$rowset = $this->tableGateway->select(array('id' => $id));
		$row = $rowset->current();
		if (!$row) {
			throw new \Exception("Could not find user has ID = $id");
		}
		return $row;
	}

	public function saveUser(User $user)
	{
		$data = array(
				'username' => $user->username,
				'email' => $user->email,
				'full_name' => $user->full_name,
				'password' => password_hash($user->password, PASSWORD_DEFAULT),
			);

		$id = (int) $user->id;

		if ($id === 0) {
			$data['created_at'] = $data['updated_at'] = date("Y-m-d H:i:s");
			$this->tableGateway->insert($data);
			$id = $this->tableGateway->lastInsertValue;
			$update_data['creator_id'] = $id;
			$this->tableGateway->update($update_data, array('id' => $id));
		} else {
            if ($this->getUser($id)) {
            	$data['updated_at'] = date("Y-m-d H:i:s");
                $this->tableGateway->update($data, array('id' => $id));
            } else {
                throw new \Exception("User $id does not exist");
            }
        }
	}

    public function deleteUser($id)
    {
        $this->tableGateway->delete(array('id' => (int) $id));
    }
}

