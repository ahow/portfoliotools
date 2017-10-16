<?php
  include('../vendor/phpmailer/phpmailer/PHPMailerAutoload.php');
  function getDBPHPMailer($params)
  {  $mail = new PHPMailer;
     $mail->isSMTP();                                // Set mailer to use SMTP
     $mail->Host = $params->smtphost;                // Specify main and backup SMTP servers
     $mail->Port = $params->smtpport;                // TCP port to connect to
     $mail->Username = $params->smtpuser;            // SMTP username
     if ($params->smtppassword!='')
     {  $mail->SMTPAuth = true;                      // Enable SMTP authentication
        $mail->Password = $params->smtppassword;     // SMTP password
        $mail->SMTPSecure = $params->smtpsecure;     // Enable TLS encryption, `ssl` also accepted
     }     
     return $mail;
  }
  /*
    $mail->setFrom($params->smtpsender, $params->smtpsendername);
    //$mail->addAddress('joe@example.net', 'Joe User');     // Add a recipient
    $mail->addAddress($email);               // Name is optional
    //$mail->addReplyTo('info@example.com', 'Information');
    //$mail->addCC('cc@example.com');
    //$mail->addBCC('bcc@example.com');

    //$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
    //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
    $mail->isHTML(true);                                  // Set email format to HTML
   */
?>
