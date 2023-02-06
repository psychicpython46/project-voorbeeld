<?php   
    class Database{
       private $servername;
        private $username;
        private $password;
        private $myDatabase;
        private $conn;
        private $connectMessage = "";
        public  $connected = false; 

        public function __construct(){
           
            $this->servername = "localhost";
            $this->username = "root";
            $this->password = "";
            $this->myDatabase = "projectfestival";
        }

        public function getConnection() {
            $this->conn = new mysqli($this->servername, $this->username, $this->password,$this->myDatabase);
            if ($this->conn->connect_error) {
                $this->connectMessage = "Connection failed: " . $this->conn->connect_error;
            } else {
                $this->connectMessage = "Connected successfully";
                $this->connected = true;
            }
            return $this->conn;
        }

        public function getFestivals($approved) {
            $success = false;
            $error = "";
            $info = [];
            $result_array = [];

            if($approved == 0) {
                $sql = "SELECT * FROM `festivals` WHERE approved = 1";
            } else {
                $sql = "SELECT * FROM `festivals`";
            }
                
            $queryResult = $this->conn->query($sql);

            if ($queryResult->num_rows > 0) {
                while($row = $queryResult->fetch_assoc()) {
                    array_push($result_array,$row);    
                }
                $success = true;
                $info = [
                    'num-rows' => $queryResult->num_rows
                ];
            } else {
                $success = false;
                $error = json_encode($conn.error_get_last());
            }

            $result = [
                'success' => $success,
                'error' => $error,
                'info' => $info,
                'data' => $result_array
            ];
            return $result;
        }

        public function login($username, $password) {
            $success = false;
            $error = "";
            $info = ["No accounts found"];
            $apikey = "";
            $role = "";

            $sql = "SELECT * FROM `users`";              
            $queryResult = $this->conn->query($sql);

            if ($queryResult->num_rows > 0) {
                while($row = $queryResult->fetch_assoc()) {
                    if($row['username'] == $username && $row['password'] == $password) {
                        $success = true;
                        $role = $row['role'];
                        $apikey = $this->getApiKey($username, $role);
                        $info = [];
                        break;
                    } 
                }
            } else {
                $success = false;
                $error = json_encode($conn.error_get_last());
            }

            $result = [
                'success' => $success,
                'error' => $error,
                'info' => $info,
                'key' => $apikey,
                'role' => $role
            ];
            return $result;
        }

        public function getApiKey($username, $role)
        {
            $sql = "SELECT * FROM `codes`";              
            $queryResult = $this->conn->query($sql);
            $result_array = [];

            if ($queryResult->num_rows > 0) {
                while($row = $queryResult->fetch_assoc()) {
                    if($row['user'] == $username) {
                        $key = $row['code'];
                        if (new DateTime() > new DateTime($row['expiry_date'])) {

                            $sql = "DELETE FROM `codes` WHERE `codes`.`code` = '$key'";              
                            $queryResult = $this->conn->query($sql);

                            $key = $this->createKey();
                            $date = date("Y-m-d", strtotime("+2 day"));
                            $sql = "INSERT INTO `codes`(`code`, `expiry_date`, `role`, `user`) VALUES ('$key','$date','$role', '$username')";              
                            $queryResult = $this->conn->query($sql);

                            return $key;
                        } else {
                            return $key;
                        }
                    }   
                }

                $key = $this->createKey();
                $date = date("Y-m-d", strtotime("+2 day"));
                $sql = "INSERT INTO `codes`(`code`, `expiry_date`, `role`, `user`) VALUES ('$key','$date','$role', '$username')";              
                $queryResult = $this->conn->query($sql);

                return $key;
            } else {
                $key = $this->createKey();
                $date = date("Y-m-d", strtotime("+2 day"));
                $sql = "INSERT INTO `codes`(`code`, `expiry_date`, `role`, `user`) VALUES ('$key','$date','$role', '$username')";              
                $queryResult = $this->conn->query($sql);

                return $key;
            }
        }

        public function checkKey($key)
        {
            $success = false;
            $error = "";
            $info = ["Invalid key"];
            $apikey = $key;
            $role = "";
            
            $sql = "SELECT * FROM `codes`";              
            $queryResult = $this->conn->query($sql);
            $result_array = [];

            if ($queryResult->num_rows > 0) {
                while($row = $queryResult->fetch_assoc()) {
                    if($row['code'] == $key) {
                        if (new DateTime() > new DateTime($row['expiry_date'])) {

                            $sql = "DELETE FROM `codes` WHERE `codes`.`code` = '$key'";              
                            $queryResult = $this->conn->query($sql);

                            $success = false;
                            $info = ['Key has Expired'];
                        } else {
                            $success = true;
                            $info = ["Valid key found"];
                            $role = $row['role'];
                        }
                    }   
                }
            }

            $result = [
                'success' => $success,
                'error' => $error,
                'info' => $info,
                'key' => $apikey,
                'role' => $role
            ];
            return $result;
        }

        public function createKey() {
            $abc = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'];
            $key = "";
            for ($i=0; $i < 15; $i++) { 
                if(random_int(0,100) < 50) {
                    $key .= random_int(0, 9);
                } else {
                    $key .= $abc[random_int(0, count($abc) - 1)];
                }
            }
            return $key;
        }

        public function approveFestival($key, $id) {
            $success = false;
            $error = "";
            $info = [];
            $result_array = [];

            if($this->checkKey($key)['success']) {
                $sql = "UPDATE `festivals` SET `approved` = '1' WHERE `festivals`.`id` = $id";
                $queryResult = $this->conn->query($sql);
                $success = true;
            } else {
                $error = "Invalid api-key / No permission";
            }

            $result = [
                'success' => $success,
                'error' => $error,
                'info' => $info,
                'data' => $result_array
            ];
            return $result;
        }

        public function createFestival($naam, $plaats, $datums, $datume, $mens, $kosten) {
            $success = false;
            $error = "";
            $info = [];

            $sql = "INSERT INTO `festivals`(`naam`, `plaats`, `datum start`, `datum eind`, `tickets`, `max_mensen`, `kosten_tickets`, `approved`, `verwijderd`) VALUES ('$naam','$plaats','$datums','$datume','0','$mens','$kosten','0','0')";
                
            $queryResult = $this->conn->query($sql);

            $success = true;

            $result = [
                'success' => $success,
                'error' => $error,
                'info' => $info
            ];
            return $result;
        }
    }
?>