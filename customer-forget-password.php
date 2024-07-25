<?php include 'header.php'; ?>

<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
?>

<?php
if(isset($_POST['form_submit'])) {
    try {

        if($_POST['email'] == '') {
            throw new Exception("Email can not be empty");
        }
        if(!filter_var($_POST['email'],FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Email is invalid");
        }

        $q = $pdo->prepare("SELECT * FROM customers WHERE email=?");
        $q->execute([$_POST['email']]);
        $total = $q->rowCount();
        if(!$total) {
            throw new Exception("Email is not found");
        } 

        $token = time();
        $statement = $pdo->prepare("UPDATE customers SET token=? WHERE email=?");
        $statement->execute([$token,$_POST['email']]);

        $email_message = "Please click on the following link in order to reset the password: <br>";
        $email_message .= '<a href="'.BASE_URL.'customer-reset-password.php?email='.$_POST['email'].'&token='.$token.'">Reset Password</a>';

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'sandbox.smtp.mailtrap.io';
            $mail->SMTPAuth = true;
            $mail->Username = 'd23965a8af8868';
            $mail->Password = 'c64bec36d3e14e';
            $mail->SMTPSecure = SMTP_ENCRYPTION;
            $mail->Port =2525;
            $mail->setFrom('myidproperties@gmail.com');
            $mail->addAddress($_POST['email']);
            //$mail->addReplyTo('contact@example.com');
            $mail->isHTML(true);
            $mail->Subject = 'Registration Verification Email';
            $mail->Body = $email_message;
            $mail->send();
            $success_message = 'please check your email and follow the steps.';

        }catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } catch(Exception $e) {
        $error_message = $e->getMessage();
    }
}
?>

<div class="page-top" style="background-image: url('<?php echo BASE_URL; ?>uploads/banner.jpg')">
    <div class="bg"></div>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h2>Customer Forget Password</h2>
            </div>
        </div>
    </div>
</div>

<div class="page-content">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-4 col-lg-5 col-md-6 col-sm-12">
                <div class="login-form">
                    <form action="" method="post">
                        <div class="mb-3">
                            <label for="" class="form-label">Email Address *</label>
                            <input type="text" name="email" class="form-control">
                        </div>
                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary bg-website" name="form_submit">
                                Submit
                            </button>
                            <a href="<?php echo BASE_URL; ?>customer-login" class="primary-color">Back to Login Page</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>