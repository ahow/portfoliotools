<?php
 /* Fedotov Vitaliy (c) Ulan-Ude 2016 | kursruk@yandex.ru */
 include('../lib/params.php');
 include('../lib/phpmailer.php');
  
 class ajxreg extends wAjax
 {  function ajxSave()
    {   $d = (object)$_POST; 
        $db = $this->cfg->db;
       
        $r = new stdClass();
        $r->orgname = filter_var($d->orgname , FILTER_SANITIZE_STRING);
        $r->phoneorg = filter_var($d->phoneorg, FILTER_SANITIZE_STRING);
        $r->address = filter_var($d->address, FILTER_SANITIZE_STRING);
        $r->postindex = filter_var($d->postindex , FILTER_SANITIZE_STRING);
        $r->fullname= filter_var($d->fullname, FILTER_SANITIZE_STRING);
        $r->classno = filter_var($d->classno , FILTER_SANITIZE_STRING);
        $r->email = filter_var($d->email, FILTER_SANITIZE_EMAIL);
        $r->chief = filter_var($d->chief, FILTER_SANITIZE_STRING);
        $r->phone = filter_var($d->phone, FILTER_SANITIZE_STRING);
                
        try
          { 

            $q = $db->query("select usersubj,userbody,adminsubj,adminbody
            from templates where name='reg'");
            $t = $db->fetchSingle($q);

            $params = getParams($db,'email');
            
            // send email to the user
            $mail = getDBPHPMailer($params);
            $mail->setFrom($params->smtpsender, $params->smtpsendername);
            $mail->addAddress($r->email);               // Name is optional
            $mail->isHTML(true);                     // Set email format to HTML
            
            
            $mail->Subject = $t->usersubj;
            $mail->Body    = $t->userbody;
         
            // $mail->AltBody = '';

            if(!$mail->send()) {               
               $this->error($mail->ErrorInfo, 4043);
               return false;
            } 
            
            // send email to the admin
            $maila = getDBPHPMailer($params);
            $maila->setFrom($params->smtpsender, $params->smtpsendername);
            $maila->addAddress($params->adminemail);    // Name is optional
            $maila->isHTML(false);                      // Set email format to HTML
            
            
            $maila->Subject = $t->adminsubj;
            
            $body = str_replace('$email',$r->email, $t->adminbody);
            $body = str_replace('$firstname',$r->firstname, $body);
            $body = str_replace('$lastname',$r->lastname, $body);
            $body = str_replace('$confirm_email',$confirm_email, $body);
            $guests = '';
            foreach($d->rows as $row)
            { $sr = (object)$row;              
              $firstname = filter_var($sr->firstname, FILTER_SANITIZE_STRING);  
              $lastname = filter_var($sr->lastname, FILTER_SANITIZE_STRING);
              $guests.="First Name: $firstname\nLast Name: $lastname\n\n";
            }
            $body = str_replace('$guests',$guests, $body); 
            $maila->Body = $body;

            if(!$maila->send()) {               
               $this->error($maila->ErrorInfo, 4043);
               return false;
            } else {
                $this->res->info='Message has been sent';                                
                
                // saving data
                
                $db->insertObject('evnamegl',$r);        
                $id=$db->db->lastInsertId();
                
                $this->res->id = $id;
                foreach($d->rows as $row)
                { $sr = (object)$row;
                  $r = new stdClass();
                  $r->firstname = filter_var($sr->firstname, FILTER_SANITIZE_STRING);  
                  $r->lastname = filter_var($sr->lastname, FILTER_SANITIZE_STRING);
                  $r->engl_id = $id;
                  $db->insertObject('evnamegl_guests',$r);
                }
                
            }

          } catch (Exception $e)
          {   if ($e->getCode()!=23000) $this->error($e->getMessage(), $e->getCode());
          }
          echo json_encode($this->res);
    }
 }

?>
