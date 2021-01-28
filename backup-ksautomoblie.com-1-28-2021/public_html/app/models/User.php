<?php

    class User{
        
        private $db;
        
        public function __construct(){
            $this->db = new DB();
        }
        
        public function login($data){
            $this->db->query('SELECT * FROM users WHERE email=:email AND password=:password');
            $this->db->bind(":email", $data["email"]);
            $this->db->bind(":password", $data["password"]);
            $row_detail = $this->db->single();
            if($this->db->rowCount() > 0){
                return $row_detail;
            }else{
                return false;
            }
        }
        
        public function insertBill($data){
            if(empty($data->menu)){
                return false;
            }else{
                $this->db->query('SELECT * FROM bills WHERE billno=:billno');
                $this->db->bind(":billno", $data->billno);
                if($this->db->rowCount()>0){
                    return false;
                }else{
                    $this->db->query('SELECT id from users WHERE workshopname=:workshopname');
                    $this->db->bind(":workshopname", $_SESSION["workshopname"]);
                    $fid = $this->db->single();
                    $this->db->query('INSERT INTO `bills`(`billno`, `name`, `vehicleno`, `vehiclename`, `date`, `km`, `email`, `mobileno`, `fid`, `total`) VALUES (:billno, :name, :vehicleno, :vehiclename, :date, :km, :email, :mobileno, :fid, :total)');
                    $this->db->bind(":billno", $data->billno);
                    $this->db->bind(":name", $data->name);
                    $this->db->bind(":vehicleno", $data->vehicleno);
                    $this->db->bind(":vehiclename", $data->vehiclename);
                    $this->db->bind(":date", $data->date);
                    $this->db->bind(":km", $data->km);
                    $this->db->bind(":email", $data->email);
                    $this->db->bind(":mobileno", trim($data->mobileno));
                    $this->db->bind(":fid", $fid["id"]);
                    $this->db->bind(":total", $data->total);
                    if($this->db->execute()){
                        foreach($data->menu as $menu){
                            $this->db->query('INSERT INTO `particulars`(`bills_fid`, `particular`, `rate`, `amount`, `gst`, `quantity`) VALUES (:bills_fid, :particulars, :rate, :amount, :gst, :quantity)');
                            $this->db->bind(":bills_fid", $data->billno);
                            $this->db->bind(":particulars", $menu->particulars);
                            $this->db->bind(":rate", $menu->rate);
                            $this->db->bind(":amount", $menu->amount);
                            $this->db->bind(":gst", $menu->gst);
                            $this->db->bind(":quantity", $menu->quantity);
                            $this->db->execute();
                        }
                        return true;
                    }else{
                        false;
                    }
                
                }
            }
        }
        
        public function fetchBill(){
            if(trim($_SESSION["name"])=="K S Automobiles"){
                $this->db->query("SELECT b.billno, b.name, b.date, b.vehicleno, b.vehiclename, b.email, b.mobileno, b.km, b.total FROM bills b INNER JOIN users u ON b.fid=u.id ORDER BY b.date DESC");
            }else{
                $this->db->query("SELECT b.billno, b.name, b.date, b.vehicleno, b.vehiclename, b.email, b.mobileno, b.km, b.total FROM bills b INNER JOIN users u ON b.fid=u.id WHERE u.workshopname=:workshopname ORDER BY b.date DESC");
                $this->db->bind(":workshopname", $_SESSION["workshopname"]);
            }

            $data = $this->db->resultSet();
            return $data;
        }
        
        public function fetchBillParticulars($billno){
            $this->db->query("SELECT p.particular, p.rate, p.amount, p.gst, p.quantity FROM particulars p INNER JOIN bills b ON p.bills_fid=b.billno WHERE b.billno=:billno");
            $this->db->bind(":billno", $billno);
            $data = $this->db->resultSet();
            return $data;
        }
        
        public function deleteBill($billno){
            $this->db->query("DELETE bills FROM bills INNER JOIN users ON bills.fid=users.id WHERE bills.billno=:billno AND users.workshopname=:workshopname");
            $this->db->bind(":billno", $billno);
            $this->db->bind(":workshopname", $_SESSION["workshopname"]);
            if($this->db->execute()){
                return true;
            }else{
                return false;
            }
            
        }
        
        public function searchVehicle($vehicleno){
            $this->db->query("SELECT b.billno, b.name, b.date, b.vehicleno, b.vehiclename, b.email, b.mobileno, b.km, b.total FROM bills b WHERE vehicleno=:vehicleno");
            $this->db->bind(":vehicleno", trim($vehicleno));
            $count = $this->db->resultSet();
            if($count){
                return $count;
            }else{
                return false;
            }
        }
        
        public function fetchWorkshopAddress($billno){
            $this->db->query("SELECT workshopaddress from users INNER JOIN bills ON users.id=bills.fid WHERE bills.billno=:billno");
            $this->db->bind(":billno", $billno);
            return $this->db->single();
        }
        
        public function contactForm($data){
            $this->db->query("INSERT INTO `response` (`name`, `email`, `subject`, `message`, `status`) VALUES (:name, :email, :subject, :message, :status)");
            $this->db->bind(":name", $data['name']);
            $this->db->bind(":email", $data['email']);
            $this->db->bind(":subject", $data['subject']);
            $this->db->bind(":message", $data['message']);
            $this->db->bind(":status", true);
            if($this->db->execute()){
                return true;
            }else{
                return false;
            }
        }
        
        public function fetchRecievecMsg(){
            $this->db->query("SELECT * FROM `response`");
            $data = $this->db->resultSet();
            return $data;
        }
        
        public function getAnawithDate($date){
            $this->db->query("SELECT SUM(total) FROM bills WHERE bills.date=:date");
            $this->db->bind(":date", $date);
            $data["total"] = $this->db->single();
            $this->db->query("SELECT COUNT(billno) FROM bills WHERE bills.date=:date");
            $this->db->bind(":date", $date);
            $data["visit"] = $this->db->single();
            $this->db->query("SELECT SUM(particulars.amount) FROM particulars INNER JOIN bills ON particulars.bills_fid=bills.billno WHERE particulars.particular=:wheelalignment AND bills.date=:date");
            $this->db->bind(":wheelalignment", "Wheel Alignment");
            $this->db->bind(":date", $date);
            $data["wheelalignment"] = $this->db->single();
            $this->db->query("SELECT SUM(particulars.amount) FROM particulars INNER JOIN bills ON particulars.bills_fid=bills.billno WHERE particulars.particular=:wheelalignment AND bills.date=:date");
            $this->db->bind(":wheelalignment", "Washing");
            $this->db->bind(":date", $date);
            $data["washing"] = $this->db->single();
            $this->db->query("SELECT SUM(particulars.amount) FROM particulars INNER JOIN bills ON particulars.bills_fid=bills.billno WHERE particulars.particular=:wheelalignment AND bills.date=:date");
            $this->db->bind(":wheelalignment", "A.C. Gas");
            $this->db->bind(":date", $date);
            $data["acgas"] = $this->db->single();
            $this->db->query("SELECT SUM(particulars.amount) FROM particulars INNER JOIN bills ON particulars.bills_fid=bills.billno WHERE particulars.particular=:wheelalignment AND bills.date=:date");
            $this->db->bind(":wheelalignment", "Labour");
            $this->db->bind(":date", $date);
            $data["labour"] = $this->db->single();
            $this->db->query("SELECT SUM(particulars.amount) FROM particulars INNER JOIN bills ON particulars.bills_fid=bills.billno WHERE particulars.particular=:wheelalignment AND bills.date=:date");
            $this->db->bind(":wheelalignment", "Danting & Painting");
            $this->db->bind(":date", $date);
            $data["dantingpainting"] = $this->db->single();
            $this->db->query("SELECT SUM(particulars.amount) FROM particulars INNER JOIN bills ON particulars.bills_fid=bills.billno WHERE particulars.particular=:wheelalignment AND bills.date=:date");
            $this->db->bind(":wheelalignment", "Dry Cleaners");
            $this->db->bind(":date", $date);
            $data["drycleaners"] = $this->db->single();
            $this->db->query("SELECT SUM(particulars.amount) FROM particulars INNER JOIN bills ON particulars.bills_fid=bills.billno WHERE particulars.particular=:wheelalignment AND bills.date=:date");
            $this->db->bind(":wheelalignment", "Tyre Changes");
            $this->db->bind(":date", $date);
            $data["typrechanging"] = $this->db->single();
            
            return $data;
        }
        
        
        public function getAnauptillNow(){
            $this->db->query("SELECT SUM(total) FROM bills");
            $data["total"] = $this->db->single();
            $this->db->query("SELECT COUNT(billno) FROM bills");
            $data["visit"] = $this->db->single();
            $this->db->query("SELECT SUM(particulars.amount) FROM particulars INNER JOIN bills ON particulars.bills_fid=bills.billno WHERE particulars.particular=:wheelalignment");
            $this->db->bind(":wheelalignment", "Wheel Alignment");
            $data["wheelalignment"] = $this->db->single();
            $this->db->query("SELECT SUM(particulars.amount) FROM particulars INNER JOIN bills ON particulars.bills_fid=bills.billno WHERE particulars.particular=:wheelalignment");
            $this->db->bind(":wheelalignment", "Washing");
            $data["washing"] = $this->db->single();
            $this->db->query("SELECT SUM(particulars.amount) FROM particulars INNER JOIN bills ON particulars.bills_fid=bills.billno WHERE particulars.particular=:wheelalignment");
            $this->db->bind(":wheelalignment", "A.C. Gas");
            $data["acgas"] = $this->db->single();
            $this->db->query("SELECT SUM(particulars.amount) FROM particulars INNER JOIN bills ON particulars.bills_fid=bills.billno WHERE particulars.particular=:wheelalignment");
            $this->db->bind(":wheelalignment", "Labour");
            $data["labour"] = $this->db->single();
            $this->db->query("SELECT SUM(particulars.amount) FROM particulars INNER JOIN bills ON particulars.bills_fid=bills.billno WHERE particulars.particular=:wheelalignment");
            $this->db->bind(":wheelalignment", "Danting & Painting");
            $data["dantingpainting"] = $this->db->single();
            $this->db->query("SELECT SUM(particulars.amount) FROM particulars INNER JOIN bills ON particulars.bills_fid=bills.billno WHERE particulars.particular=:wheelalignment");
            $this->db->bind(":wheelalignment", "Dry Cleaners");
            $data["drycleaners"] = $this->db->single();
            $this->db->query("SELECT SUM(particulars.amount) FROM particulars INNER JOIN bills ON particulars.bills_fid=bills.billno WHERE particulars.particular=:wheelalignment");
            $this->db->bind(":wheelalignment", "Tyre Changes");
            $data["typrechanging"] = $this->db->single();
            
            return $data;
        }
        
        public function forgotPassword($email){
            $this->db->query("SELECT id FROM users WHERE users.email=:email");
            $this->db->bind(":email", $email);
            if($this->db->execute()){
                return $this->db->rowCount();
            }else{
                return false;
            }
        }
        
        public function forgot_password($email, $password){
            $this->db->query("UPDATE users SET password=:password WHERE users.email=:email");
            $this->db->bind(":password", $password);
            $this->db->bind(":email", $email);
            if($this->db->execute()){
                return true;
            }else{
                return false;
            }
        }
    }

?>