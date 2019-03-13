<?php
namespace Phonebook;
/*
	Phonebook model class
*/
class Phonebook {
	// ���������� ������ ���������
	public function getContactList() {
		$dbh = new DBHelper();
		$pdo = $dbh->connectToDB();
	
		// �������� �� ���������
		$contacts = [];
		$stmt = $pdo->query('SELECT id, name FROM contact');
		
		while ($row = $stmt->fetch()) {
			$contacts[$row['id']] = $row;
		}
		//return $contacts;
		
		// ��������� � ��� ��������
		$stmt = $pdo->query('SELECT id, contact_id, phone FROM phone');
		while ($row = $stmt->fetch()) {
			// ���� �������� � ����� id ���, ������ ���� ������� �� �������� �� � ������ ��������
			// �������������, ���������� ��� �� �������
			if (!isset($contacts[$row['contact_id']]))
				break;
				
			// ���� ���� phones ����������� ��� ��������������������� � �������� �������
			if (!isset($contacts[$row['contact_id']]['phones']) || 
				!is_array($contacts[$row['contact_id']]['phones'])
			) {
				$contacts[$row['contact_id']]['phones'] = [];
			}
			
			//$contacts[$row['contact_id']]['phones'][] = $row;
			//$contacts[$row['contact_id']]['phones'][$row['id']] = $row;
			$contacts[$row['contact_id']]['phones'][$row['id']] = ['id' => $row['id'], 'phone' => $row['phone']];
		}		

		return $contacts;
	}	
	
	
	// ��������� ������� � ��������� ������ � ���������
	public function addContact($name, $phone) {
		
		$dbh = new DBHelper();
		$pdo = $dbh->connectToDB();
		
		try {
			$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

				// ��������� �������
				$stmt = $pdo->prepare("INSERT INTO contact (name) VALUES (:name)");
				$stmt->bindParam(':name', $name);
				$res = $stmt->execute();
				$contact_id = $pdo->lastInsertId();
				
				// ���� ������� ������� ��������, ��������� �������
				if ($res) {
					$stmt = $pdo->prepare("INSERT INTO phone (contact_id, phone) VALUES (".$contact_id.", :phone)");
					$stmt->bindParam(':phone', $phone);
					$res = $stmt->execute();
					$phone_id = $pdo->lastInsertId();
					
					//return $contact_id;
					if ($res) {
						return ['contact_id' => $contact_id, 'phone_id' => $phone_id];
					}
				}

		} catch (Exception $e) {
			$pdo->rollBack();
			throw new Exception($e->getMessage());
		}
		
		return false;
	}
	
	
	// �������� ��� ��������
	public function updateContact($id, $name) {
		$dbh = new DBHelper();
		$pdo = $dbh->connectToDB();
	
		// ��������� ������������� ��������
		$stmt = $pdo->prepare('SELECT name FROM contact WHERE id = :id');
		$stmt->bindParam(':id', $id);
		$stmt->execute();
		
		if ($row = $stmt->fetch()) {
			// ���� ��� �������� �� ����� ��, �� ������ �� ���������
			if ($row['name'] == $name) {
				return true;
			} else {
				// ��������� update
				$stmt = $pdo->prepare('UPDATE contact SET name = :name WHERE id = :id');
				$stmt->bindParam(':name', $name);
				$stmt->bindParam(':id', $id);
				$stmt->execute();
				
				return true;
			}
		} else {
			return false;
		}
	}
	
	
	// ������� �������
	public function deleteContact($id) {
		$dbh = new DBHelper();
		$pdo = $dbh->connectToDB();
		
		$stmt = $pdo->prepare('DELETE FROM contact WHERE id = :id');
		$stmt->bindParam(':id', $id);
		$stmt->execute();
		
		$res = $stmt->rowCount();

		return $res;
	}
	
	
	// �������� ������� � ��������
	public function addPhone($contact_id, $phone) {
		$dbh = new DBHelper();
		$pdo = $dbh->connectToDB();
		
		// ��������� ������������� ��������
		$stmt = $pdo->prepare('SELECT count(*) as "count" FROM contact WHERE id = :id');
		$stmt->bindParam(':id', $contact_id);
		$stmt->execute();
		$row = $stmt->fetch();
		if ($row['count'] > 0) {
			// ��� ������� �������� - ��������� �������
			$stmt = $pdo->prepare("INSERT INTO phone (contact_id, phone) VALUES (:contact_id, :phone)");
			$stmt->bindParam(':contact_id', $contact_id);
			$stmt->bindParam(':phone', $phone);
			$res = $stmt->execute();
			$phone_id = $pdo->lastInsertId();			
			
			return $phone_id;
		} else {
			return false;
		}
	}
	
	
	// �������� ��� ��������
	public function updatePhone($id, $phone) {
		$dbh = new DBHelper();
		$pdo = $dbh->connectToDB();
	
		// ��������� ������������� ��������
		$stmt = $pdo->prepare('SELECT phone FROM phone WHERE id = :id');
		$stmt->bindParam(':id', $id);
		$stmt->execute();
		
		if ($row = $stmt->fetch()) {
			// ���� ����� �������� �� ����� ��, �� ������ �� ���������
			if ($row['phone'] == $phone) {
				return true;
			} else {
				// ��������� update
				$stmt = $pdo->prepare('UPDATE phone SET phone = :phone WHERE id = :id');
				$stmt->bindParam(':phone', $phone);
				$stmt->bindParam(':id', $id);
				$stmt->execute();
				
				return true;
			}
		} else {
			return false;
		}
	}	
	

	// ������� �������
	public function deletePhone($id) {
		$dbh = new DBHelper();
		$pdo = $dbh->connectToDB();
		
		$stmt = $pdo->prepare('DELETE FROM phone WHERE id = :id');
		$stmt->bindParam(':id', $id);
		$stmt->execute();
		
		$res = $stmt->rowCount();

		return $res;
	}
}