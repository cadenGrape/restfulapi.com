<?php
require_once __DIR__.'/ErrorCode.php';
class User
{
    /**
     * 数据库链接句柄
     * @var
     */
    private $_db;

    /**
     * User constructor.
     * @param $_db
     */
    public function __construct($_db)
    {
        $this->_db = $_db;
    }

    /**
     * @param $username
     * @param $password
     * @return mixed
     * @throws Exception
     */
    public function login($username,$password)
    {
        if (empty($username)){
            throw new Exception('用户名不能为空',ErrorCode::USERNAME_CANNOT_EMPTY);
        }
        if (empty($password)){
            throw new Exception('密码不能为空',ErrorCode::PASSWORD_CANNOT_EMPTY);
        }
        $sql = 'SELECT * FROM `user`WHERE `username`=:username AND `password`=:password';
        $password = $this->_md5($password);
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':username',$username);
        $stmt->bindParam(':password',$password);
        if (!$stmt->execute()){
            throw new Exception('服务器内部错误',ErrorCode::SERVER_INTERNAL_ERROR);
        }
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (empty($user)){
            throw new Exception('用户名或密码错误',ErrorCode::USERNAME_OR_PASSWORD_INVALID);
        }
        var_dump($user);exit;
        unset($user['password']);
        return $user;
    }

    /**
     * 用户注册
     * @param $username
     * @param $password
     * @return array
     * @throws Exception
     */
    public function register($username,$password)
    {
        if (empty($username)){
            throw new Exception('用户名不能为空',ErrorCode::USERNAME_CANNOT_EMPTY);
        }
        if (empty($password)){
            throw new Exception('密码不能为空',ErrorCode::PASSWORD_CANNOT_EMPTY);
        }
        if ($this->_isUsernameExists($username)){
            throw new Exception('用户名已存在',ErrorCode::USERNAME_EXISITS);
        }
        //写入数据库
        $sql = 'INSERT INTO `user` (`username`,`password`,`created_at`) VALUES (:username,:password,:created_at)';
        $created_at = time();
        $password = $this->_md5($password);
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':username',$username);
        $stmt->bindParam(':password',$password);
        $stmt->bindParam(':created_at',$created_at);
        if (!$stmt->execute()){
            throw new Exception('注册失败',ErrorCode::REGISTER_FAIL);
        }
        return [
            'userId' => $this->_db->lastInsertId(),
            'username' => $username,
            'created_at' => $created_at
        ];
    }

    /**
     * md5加密
     * @param $string
     * @param string $key
     * @return string
     */
    private function _md5($string, $key = 'jane')
    {
        return md5($string.$key);
    }

    /**
     * 检测用户名是否存在
     * @param $username
     * @return bool
     */
    private function _isUsernameExists($username)
    {
        $sql = 'SELECT * FROM `user` WHERE `username`=:username';
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':username',$username);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return !empty($result);
    }
}