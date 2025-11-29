<?php

class User{
    public $id;
    public $username;
    public $password;
    public $role;

    public function __construct($id,$username,$password, $role){
        $this->id=$id;
        $this->username=$username;
        $this->password=$password;
        $this->role=$role;
    }
    
    public function getUsername(){
        return $this->username;
    }

    public function getPassword(){
        return $this->password;
    }

    public function save($conn){
        $exist=$conn->prepare("select * from users where username=?");
        $exist->execute([$this->username]);
        if($exist->rowCount()>0){
            return false; 
        }
        $data=$conn->prepare("insert into users(username,pass) values(?,?)");
        $data->execute([$this->username,password_hash($this->password, PASSWORD_DEFAULT)]);
        if($data){
            return new User($conn->lastInsertId(),$this->username, $this->password, $this->role);
        }
        return false;
    }

    public function update($conn,$record=[]){
        $data=$conn->prepare("update users set pass=? where username=?");
        $data->execute([password_hash($record['password'], PASSWORD_DEFAULT),$this->username]);
        if($data){
            $this->password=$record['password'];
            return true;
        }
        return false;
    }

    public function delete($conn){
        $data=$conn->prepare("delete from users where username=?");
        $data->execute([$this->username]);
        if($data){
            return true;
        }
        return false;
    }

    public static function find($conn,$username, $password){
        $datas=$conn->prepare("select * from users where username=?");
        $datas->execute([$username]);
        foreach($datas->fetchAll() as $row){
            if(password_verify($password, $row['pass'])){
                return new User($row['id'],$row['username'], $password, $row['STATUS']);
            }
        }
        return false;
    }

    public static function findbyId($conn,$id){
        $datas=$conn->prepare("select * from users where id=?");
        $datas->execute([$id]);
        foreach($datas->fetchAll() as $row){
            return new User($row['id'],$row['username'], $password, $row['STATUS']);
        }
        return false;
    }

    public static function all($conn){
        $datas=$conn->query("select * from users");
        $users=[];
        foreach($datas->fetchAll() as $row){
            $users[]=new User($row['id'],$row['username'], $row['pass'], $row['role']);
        }
        return $users;
    }

}
?>