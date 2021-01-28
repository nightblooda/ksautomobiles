<?php

    class Defaultpage extends MainController{
        
        public function __construct(){
            $this->userModal = $this->modal('User');
            $this->checkUserLogin();
        }
        
        public function index(){
            $data["login"] = "nologin";
            if($this->isUserLoged){
                $data["login"] = "yeslogin";
            }
            $this->view('pages/home', $data);
            
        }
        
        public function login(){
             if($this->isUserLoged){
                 redirect("defaultpage/index");
             }else{
            
                if($_SERVER['REQUEST_METHOD']=='POST'){
                    $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
                    $_POST['email'] = preg_replace("/[' ']/i", "", $_POST['email']);
                    $_POST['password'] = preg_replace("/[' ']/i", "", $_POST['password']);
                    $_POST['email'] = trim($_POST['email']);
                    $_POST['password'] = trim($_POST[password]);
                    
                    if(empty($_POST['email']) || empty($_POST['password'])){
                        echo "ErrorD";
                        exit();
                    }else{
                        $data = [
                            'email' => $_POST['email'],
                            'password' => $_POST["password"]
                            ];
                        if($row_detail = $this->userModal->login($data)){
                            $details = json_decode(json_encode($row_detail), true);
                            
                            $loginid = $this->generateRandomString().$details['id'].$this->generateRandomString().time().$this->generateRandomString();
                            $this->createLoginStore($loginid);
                            setcookie("workshopname", $details["workshopname"], strtotime(' + 30 days'), "/", "", "", true);
                            $_SESSION["workshopname"] = $details["workshopname"];
                            setcookie("name", $details["name"], strtotime(' + 30 days'), "/", "", "", true);
                            $_SESSION["name"] = $details["name"];
                            setcookie("workshopaddress", $details["workshopaddress"], strtotime(' + 30 days'), "/", "", "", true);
                            $_SESSION["workshopaddress"] = $details["workshopaddress"];
                        }else{
                            echo "Invalid user details.";
                            exit();
                        }
                    }
                }else{
                    $this->view('pages/login');
                }
            }
        }
        
    public function generateRandomString($length = 47) {
      $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_-';
      $charactersLength = strlen($characters);
      $randomString = '';
      for ($i = 0; $i < $length; $i++) {
          $randomString .= $characters[rand(0, $charactersLength - 1)];
      }
      return $randomString;
    }
    
    
    
    public function createLoginStore($loginid){
      setcookie("loginid", $loginid, strtotime(' + 30 days'), "/", "", "", true);
      $_SESSION['loginid']= $loginid;
    }
    
      public function logout(){
    if($this->isUserLoged){
        $this->destroyLoginStore();
    }
    redirect("defaultpage/index");

}

  public function destroyLoginStore(){
if(isset($_COOKIE['loginid'])){
  unset($_COOKIE['loginid']);
  setcookie("loginid", "", time() - 42000, "/", "", "", true);
}
if(isset($_SESSION['loginid'])){
  unset($_SESSION['loginid']);
}

if(isset($_COOKIE['workshopname'])){
    unset($_COOKIE['workshopname']);
    setcookie("workshopname", "", time() - 42000, "/", "", "", true);
}

if(isset($_SESSION['workshopname'])){
    unset($_SESSION['workshopname']);
}

if(isset($_COOKIE['name'])){
    unset($_COOKIE['name']);
    setcookie("name", "", time() - 42000, "/", "", "", true);
}

if(isset($_SESSION['workshopname'])){
    unset($_SESSION['workshopname']);
}

if(isset($_COOKIE['workshopaddress'])){
    unset($_COOKIE['workshopaddress']);
    setcookie("workshopaddress", "", time() - 42000, "/", "", "", true);
}

if(isset($_SESSION['workshopaddress'])){
    unset($_SESSION['workshopaddress']);
}

}

    public function billing(){
        if($this->isUserLoged){
            if(true){
                $data = $this->userModal->fetchBill();
                $this->view('pages/billing', $data);
            }
        }
    }
    
    public function fetchBillParticulars(){
        if($_SERVER['REQUEST_METHOD']=='POST'){
            if($this->isUserLoged){
                if($data = $this->userModal->fetchBillParticulars($_POST["billno"])){
    echo json_encode($data);                }else{
                    echo "Erorr occured in fetching details.";
                }
            }
        }
    }
    
    public function engine(){
        $this->view('pages/engine');
    }
    
    public function insertBill(){
        if($_SERVER['REQUEST_METHOD']=='POST'){
            if($this->isUserLoged){
                // Takes raw data from the request
                $json = file_get_contents('php://input');

                // Converts it into a PHP object
                $data = json_decode($json);
                if(empty($data->menu)){
                    echo "empty";
                }else{
                    if($this->userModal->insertBill($data)){
                        echo "Bill_inserted_successfully";
                    }else{
                        echo "Bill_not_inserted";
                    }
                }
            }
        }else{
            redirect("defaultpage/index");
        }
    }
    
    public function deleteBill(){
        if($_SERVER['REQUEST_METHOD']=='POST'){
            if($this->isUserLoged){
                if($this->userModal->deleteBill($_POST["billno"])){
                    echo "Bill is deleted successfully";
                }else{
                    echo "Bill in not deleted";
                }
            }
            
        }
    }
    
    public function searchVehicle(){
        if($_SERVER['REQUEST_METHOD']=='POST'){
            if($this->isUserLoged){
                $vehicleno = trim($_POST["vehicleno"]);
                if(!empty($vehicleno)){
                    if($data = $this->userModal->searchVehicle($vehicleno)){
                        echo json_encode($data);
                    }else{
                        echo "No result found!";
                    }
                }
            }else{
                echo "You are not loged In.";
            }
            
        }
    }
    
    public function fetchWorkshopAddress(){
        if($_SERVER['REQUEST_METHOD']=='POST'){
            if($this->isUserLoged){
                $billno = trim($_POST["billno"]);
                    if(!empty($billno)){
                        echo $this->userModal->fetchWorkshopAddress($billno);
                    }
                
            }
        }

    }
    
    public function contactForm(){
        if($_SERVER['REQUEST_METHOD']=='POST'){
            if($this->userModal->contactForm($_POST)){
                echo "RESPONSE OK";
            }else{
                echo "Some error check your response and try again.";
            }
        }
    }
    
    public function dashboard(){
        if($this->isUserLoged){
            $data["login"] = "yeslogin";
            $data["recievedmsg"] = (array)$this->userModal->fetchRecievecMsg();
            $this->view('pages/dashboard', $data);
        }else{
            redirect("defaultpage/index");
        }
    }
    
    public function analyst(){
        if($this->isUserLoged){
            $data["login"] = "yeslogin";
            $data["onloadana"] = $this->userModal->getAnawithDate(date("Y-m-d"));
            $data["uptillnowana"] = $this->userModal->getAnauptillNow();
            $this->view('pages/analyst', $data);
        }else{
            redirect("defaultpage/index");
        }
    }
    
    public function getAnawithDate(){
        if($_SERVER['REQUEST_METHOD']=='POST'){
            if($this->isUserLoged){
                if(!empty($_POST['date'])){
                    $data = $this->userModal->getAnawithDate($_POST['date']);
                    echo json_encode($data);
                }
            }
        }
    }
    
    public function sendEmail(){
        if($_SERVER['REQUEST_METHOD']=='POST'){
            if($this->isUserLoged){
               
                $to = $_POST["email"];
		$from = "customer@ksautomoblie.com";
		$headers ="From: $from\n";
		$headers .= "MIME-Version: 1.0\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1 \n";
		$subject =$_POST["subject"];
		$msg = $_POST["message"];
		if(mail($to,$subject,$msg,$headers)) {
			echo "RESPONSE OK ";
			exit();
		} else {
			echo "email_send_failed";
			exit();
		}
            }
        }
    }
    
    public function forgotPassword(){
        if($this->isUserLoged){
            redirect("defaultpage/index");
        }else{
            if($_SERVER['REQUEST_METHOD']=='POST'){
                if($this->userModal->forgotPassword($_POST["email"])){
                    
                    $to = $_POST["email"];
                    $tempPass = substr($to, 0, 5);
                    $chars = '0123456789ABCDEFGHabcdef';
                    $charslen = strlen($chars);
                    for($i = 0; $i < 5; $i++){
                        $tempPass .= $chars[rand(0, $charslen-1)];
                    }
                    
            		$from = "customer@ksautomoblie.com";
            		$headers ="From: $from\n";
            		$headers .= "MIME-Version: 1.0\n";
            		$headers .= "Content-type: text/html; charset=iso-8859-1 \n";
            		$subject = "Forgot Password";
            		$msg = '<h2>Hello,</h2><p>This is an automated message from K S Automobiles. If you did not recently initiate the Forgot Password process, please disregard this email.</p><p>You indicated that you forgot your login password. We can generate a temporary password for you to log in with, then once logged in you can change your password to anything you like.</p><p>After you click the link below your password to login will be:<br /><b>'.$tempPass.'</b></p><p>.<a href="http://ksautomoblie.com/defaultpage/forgot_password/'.$to.'='.$tempPass.'">Click here now to apply the temporary password shown below to your account</a></p><p>If you do not click the link in this email, no changes will be made to your account. In order to set your login password to the temporary password you must click the link above.</p>';
            		if(mail($to,$subject,$msg,$headers)) {
            			echo "RESPONSE OK ";
            			exit();
            		} else {
            			echo "email_send_failed";
            			exit();
            		}
                }else{
                    echo "Eamil is not registered.";
                }
            }else{
                $this->view("pages/forgotpassword");
            }
        }
    }
    
    public function forgot_password($param){
        if($this->isUserLoged){
            redirect("../defaultpage/index");
        }else{
            $up= rtrim($param, '=');
            $up= explode('=', $param);
            $email = trim($up[0]);
            $password = trim($up[1]);
            if($this->userModal->forgot_password($email, $password)){
                redirect("../defaultpage/login");
            }else{
                $this->view("pages/forgot_password_expire");
            }

        }
    }
    }
    ?>