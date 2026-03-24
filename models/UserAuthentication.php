<?php
require_once('Database.php');
require_once('UserData.php');

class UserAuthentication
{
  private $_dbHandle;

  public function __construct()
  {
    $this->_dbHandle =
      Database::getInstance()->getdbConnection();
  }

  public function verifyLogin($username, $password): ?UserData
  {
    $sql = "SELECT * FROM users
                WHERE username = :username
                LIMIT 1";
    $stmt = $this->_dbHandle->prepare($sql);
    $stmt->bindValue(':username', $username);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && password_verify($password, $row['password_hash'])) {
      return new UserData($row);
    }

    return null;
  }

  public function hasUserPassword(
    $ownerPassword, $userPassword
  ): int
  {
    $sql = "SELECT id, role FROM users";
    $stmt = $this->_dbHandle->prepare($sql);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $update = $this->_dbHandle->prepare("
            UPDATE users SET password_hash = :hash WHERE id = :id
        ");

    $count = 0;

    foreach ($users as $user) {
      $hash = password_hash(
        $user['role'] === 'Owner'
          ? $ownerPassword
          : $userPassword,
        PASSWORD_DEFAULT
      );

      $update->execute([
        ':hash' => $hash,
        ':id' => $user['id']
      ]);

      $count += $update->rowCount();
    }

    return $count;
  }
}
