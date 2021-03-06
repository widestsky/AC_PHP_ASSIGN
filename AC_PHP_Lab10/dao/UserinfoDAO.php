<?php
    require_once('./dao/AbstractDAO.php');
    require_once('./model/Userinfo.php');
    require_once('./model/Admininfo.php');

    class UserinfoDAO extends AbstractDAO{
        protected $adminID;
        protected $lastLogin;
        protected $dbError;
        protected $authenticated = false;

        function __construct() {
            try{
                parent::__construct();
                $dbError = false;
            }catch(mysqli_sql_exception $e){
                $dbError = true;
                throw $e;
            }
        }

        public function getUsers(){
            $result = $this->mysqli->query('SELECT * FROM mailingList');
            $users = Array();

            if($result->num_rows >= 1){
                while($row = $result->fetch_assoc()){
                    $user = new Userinfo($row['customerName'], $row['phoneNumber'], $row['emailAddress'], $row['referrer']);
                    $users[] = $user;
                }
                $result->free();
                return $users;
            }
            $result->free();
            return false;
        }

        public function deleteUser($customerID){
            if(!$this->mysqli->connect_errno){
                $Query = "SELECT * FROM mailingList WHERE _id = ?";
                $stmt = $this->mysqli->prepare($Query);
                $stmt->bind_param('i',$customerID);
                $stmt->execute();
                $result = $stmt->get_result();
                if($result->num_rows == 1){
                    $query = 'DELETE FROM mailingList WHERE _id = ?';
                    $stmt = $this->mysqli->prepare($query);
                    $stmt->bind_param('i', $customerID);
                    $stmt->execute();
                    return true;
                }else{
                    return false;
                }
            } else {
                return false;
            }
        }
        
        public function addUser($user){
            if(!$this->mysqli->connect_errno){
                $query = 'INSERT INTO mailingList (customerName, phoneNumber, emailAddress, referrer) VALUES (?,?,?,?)';
                $stmt = $this->mysqli->prepare($query);
                // $email_hash = password_hash($user->getemailAddress(),PASSWORD_DEFAULT);
                $var1 = $user->getcustomerName();
                $var2 = $user->getphoneNumber();
                $var3 = $user->getemailAddress();
                $var4 =  $user->getreferrer();
                $stmt->bind_param('ssss', $var1,$var2,$var3,$var4);
                $stmt->execute();

                if($stmt->error){
                    echo "error";
                    return $stmt->error;
                } else {
                    return 'added successfully!';
                }
            }else {
                return 'Could not connect to Database.';
            }
        }

        public function countRows($query){
            $result = $this->mysqli->query($query);
            $row=mysqli_num_rows($result);
            return $row; 
        }

        public function authenticate($username, $password){
            $loginQuery = "SELECT * FROM adminusers WHERE Username = ? AND Password = ?";
            $stmt = $this->mysqli->prepare($loginQuery);
            $stmt->bind_param('ss',$username, $password);
            $stmt->execute();
            $result = $stmt->get_result();
            if($result->num_rows == 1){
                // $this->username = $username;
                // $this->password = $password;
                $this->authenticated = true;
                $temp = $result->fetch_assoc();     
                $Admin = new Admininfo($temp['AdminID'], $temp['Username'], $temp['Password'], $temp['Lastlogin']);
                $this->adminID = $Admin->getAdminID();
                $this->lastLogin = $Admin->getLastLogin();
                $result->free();
                return $Admin;
            }
            $result->free();
            return false;
            // $stmt->free_result();
        }

        public function updateDate($username, $password){
            if(!$this->mysqli->connect_errno){
                $date=date("Y-m-d H:i:s");
                $query = "UPDATE adminusers SET Lastlogin='$date' WHERE Username = ? and Password = ?";
                $stmt = $this->mysqli->prepare($query);
                $stmt->bind_param('ss', $username, $password);
                $stmt->execute();
                if($stmt->error){
                    return false;
                }else {
                    return $stmt->affected_rows;
                }
            } else {
                return false;
            }
        }

        public function isAuthenticated(){
            return $this->authenticated;
        }

        public function hasDbError(){
            return $this->dbError;
        }

        public function getAdminID(){
            return $this->adminID;
        }

        public function getLastLogin(){
            return $this->lastLogin;
        }


    }
?>