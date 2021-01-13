<?php
session_start();
class connect {
    public function connection(){

      $user = "root";
      $password = "";
      $pdo = "mysql:host=localhost;dbname=project_2";
      try{
        $conn = new PDO($pdo, $user, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo ' Connected Successfully ';
        return $conn;
      }
      catch (PDOException $e){
        echo 'Connection failed: ' . $e->getMessage(); 
      }
    }
}

class register {

    public $dbase;
    public function __construct() {

        $this->dbase = new Connect();
        $this->dbase = $this->dbase->Connection();
    }

    function signup($name, $email, $password, $code) {
        try {
                $result = $this->dbase->prepare("SELECT SQL_CALC_FOUND_ROWS id, name FROM user WHERE email = :email"); 
                $result->bindParam(":email",$email);
                $result->execute();
                $result = $this->dbase->prepare("SELECT FOUND_ROWS()"); 
                $result->execute();
                $row_count = $result->fetchColumn();
                if($row_count == 1)
                {
                    echo "<br/> User Already Registered";
                }
                else if ($row_count == 0)
                {
                    $insert = $this->dbase->prepare("INSERT INTO user (name,email,password,code) VALUES (:name,:email,:password,:code)");
                    $insert->bindParam(":name", $name);
                    $insert->bindParam(":email",$email);
                    $insert->bindParam(":password",$password);
                    $insert->bindParam(":code",$code);
                    if($insert->execute())
                    {
                        $subject = "Email Verification Code";
                        $message = "Your verification code is $code";
                        $sender = "From: bhumanapatel31@gmail.com";
                        if(mail($email, $subject, $message, $sender))
                        {
                            $info = "We've sent a verification code to your email - $email";
                            header("Location:verifycode.html");
                        }
                        else
                        {
                            echo "verification failed";
                        }
                    }
                    else{
                        echo "registration failed";
                    }
                }
        }catch (PDOException $e) {

            echo 'Something failed :' . $e->getMessage();
            }

    }

    function verifycode($inputcode){
        try{
            $stmt3 = $this->dbase->prepare("SELECT SQL_CALC_FOUND_ROWS id from user WHERE code = :inputcode");
            $stmt3->bindparam(":inputcode",$inputcode);
            $stmt3->execute();
            $stmt3 = $this->dbase->prepare("SELECT FOUND_ROWS()");
            $stmt3->execute();
            $rowcount = $stmt3->fetchColumn();
            if($rowcount == 1)
            {
                $setisemailverify = $this->dbase->prepare("UPDATE user SET isemailverified  = '1' where code = '$inputcode'");
                if($setisemailverify->execute())
                {
                    echo "<br/> Code Successfully Verified";
                    header("Location:welcome.html");
                }
                }else if ($rowcount == 0)
                {
                        echo "Sorry, code verif}ication failed, try again...";
                        header("Location:form.html");
                }
        }catch(PDOException $e) {
            echo ' Something failed :' . $e->getMessage();
            }

    }
    
    function signin($logemail, $logpassword){
        try{
            $signemailcheck = $this->dbase->prepare("SELECT SQL_CALC_FOUND_ROWS id FROM user WHERE email = :logemail"); 
            $signemailcheck->bindParam(":logemail",$logemail);
            $signemailcheck->execute();
            $emailfound = $this->dbase->prepare("SELECT FOUND_ROWS()"); 
            $emailfound->execute();
            $availableuser = $emailfound->fetchColumn();
            if($availableuser == 1)
            {
                $signpwdcheck = $this->dbase->prepare("SELECT * FROM user WHERE email = :logemail");
                $signpwdcheck->bindParam(':logemail',$logemail);
                $signpwdcheck->execute();
                $datareturn = $signpwdcheck->fetch();
                $out = $datareturn['password'];
                $ans = (password_verify($logpassword, $datareturn['password']) ? 1 : 0);
                if($ans == 1)
                {
                    $isemailverifiedcheck = $this->dbase->prepare("SELECT * FROM user WHERE email = :logemail");
                    $isemailverifiedcheck->bindParam(":logemail",$logemail);
                    $isemailverifiedcheck->execute();
                    $output = $isemailverifiedcheck->fetch();
                    $emailcode = $output['code'];
                    $outcomeresult = $output['isemailverified'];

                    if($outcomeresult == 1)
                    {
                        header("Location:welcome.html");
                    }

                    else if (outcomeresult == 0)
                    {
                        echo ("Email Verfication required");
                        $subject = "Email Verification Code";
                        $message = "Your verification code is $emailcode";
                        $sender = "From: bhumanapatel31@gmail.com";
                        if(mail($email, $subject, $message, $sender))
                        {
                            header("Location:verifycode.html");
                        }
                        else
                        {
                            echo "Email Sending failed";
                        }
                    }
                       
                }else if ($ans == 0)
                {
                    echo "Invalid Password";
                }
            }else{
                echo "Invalid email...";
            }
        
        }catch(PDOException $e)
        {
            echo ' Something failed :' . $e->getMessage();
                }
    }

    function forgetpassword($forgetemail)
    {
        try{
            $_SESSION['email'] = $forgetemail;
            $forgetpassword = $this->dbase->prepare("SELECT SQL_CALC_FOUND_ROWS id FROM user WHERE email = :forgetemail");
            $forgetpassword->bindParam(':forgetemail',$forgetemail);
            $forgetpassword->execute();
            $forgetpassword = $this->dbase->prepare("SELECT FOUND_ROWS()"); 
            $forgetpassword->execute();
            $row_count = $forgetpassword->fetchColumn();
            if($row_count == 1)
            {
                $newcode = rand(999999, 111111);
                $codeupdation = $this->dbase->prepare("UPDATE user SET code = '$newcode' WHERE email = '$forgetemail'");
                if($codeupdation->execute())
                {
                    $subject = "Email Verification Code";
                    $message = "Your verification code is $newcode";
                    $sender = "From: bhumanpatel31@gmail.com";
                    if(mail($forgetemail, $subject, $message, $sender))
                    {
                        $info = "We've sent a verification code to your email - $email";
                        header("Location:confirmcode.html");
                    }
                }
            }else if ($row_count == 0)
            {
                echo "Invalid Email";
            }
        }catch(PDOException $e) {
            echo ' Something failed :' . $e->getMessage();
        }
    }

    function confirmcode($inputcode){
        try{
            $passwordupdation = $this->dbase->prepare("SELECT SQL_CALC_FOUND_ROWS id from user WHERE code = :inputcode");
            $passwordupdation->bindparam(":inputcode",$inputcode);
            $passwordupdation->execute();
            $passwordupdation = $this->dbase->prepare("SELECT FOUND_ROWS()");
            $passwordupdation->execute();
            $avaiableresult = $passwordupdation->fetchColumn();
            session_start();
            $_SESSION["inputcode"] = $inputcode;
            if($avaiableresult == 1){
                echo "Code Verified";
                header("Location:newpassword.html");
            }
            else{
                echo "Sorry, code verification failed, try again...";
            }  
        }catch(PDOException $e) {
            echo ' Something failed :' . $e->getMessage();
            }

    }

    function updatepassword($encnewpass){
        try{
            $email = $_SESSION['email'];
            echo $email;
            $passwordupdation = $this->dbase->prepare("UPDATE user SET password = '$encnewpass' WHERE email = '$email'");
            $passwordupdation->bindparam(':encnewpass',$encnewpass);
            $passwordupdation->bindParam(':email',$email);
            if($passwordupdation->execute())
            {
                echo "Password Successfully Updated";
                header("Location:welcome.html");
            }else
            {
                echo "Password Updation failure";
                header("Location:form.html");
            }
        }
        catch(PDOException $e) {
            echo ' Something failed :' . $e->getMessage();
                }
    }
    
    
}
$register = new register();

    if(isset($_POST['register'])) 
    {
        $name = ($_POST['name']);
        $email = ($_POST['email']);
        $password = password_hash($_POST['password'],PASSWORD_DEFAULT);
        $code = rand(999999, 111111);
        $register->signup($name, $email, $password, $code);
    }

    if(isset($_POST["verifycode"]))
    {
        $inputcode = $_POST["inputcode"];
        $register -> verifycode($inputcode);
    }

    if(isset($_POST["login"]))
    {
        $logemail = $_POST["logemail"]; 
        $logpassword = $_POST["logpassword"];
        $register -> signin($logemail,$logpassword);
    }

    if(isset($_POST["forgetpassword"]))
    {
        $forgetemail = $_POST["forgetemail"];
        $register -> forgetpassword ($forgetemail);
    }

    if(isset($_POST["confirmcode"]))
    {
        $inputcode = $_POST["forgetcode"];
        $register -> confirmcode ($inputcode);
    }

    if(isset($_POST["updatepassword"]))
    {
        $encnewpass = password_hash($_POST["newpassword"],PASSWORD_DEFAULT);
        $register -> updatepassword ($encnewpass);
    }


?>

