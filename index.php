<?php 


if($_POST && isset($_FILES['my_file']))
{

    $from_email         = 'mail@mail.com'; //from mail, it is mandatory with some hosts
    $recipient_email    = 'mail@mail.com'; //recipient email (most cases it is your personal email)
    
    //Capture POST data from HTML form and Sanitize them, 
    $sender_name    = filter_var($_POST["sender_name"], FILTER_SANITIZE_STRING); //sender name
    $reply_to_email = filter_var($_POST["sender_email"], FILTER_SANITIZE_STRING); //sender email used in "reply-to" header
    $subject        = filter_var($_POST["subject"], FILTER_SANITIZE_STRING); //get subject from HTML form
    $messages        = filter_var($_POST["message"], FILTER_SANITIZE_STRING); //message
    $message = '
                <html>
                <head>
                  <title></title>
                </head>
                <body>
                  <table>
                    <tr>
                      <th>Name</th><th>Email</th><th>Subject</th><th>Message</th>
                    </tr>
                    <tr>
                      <td>'.$sender_name. '</td><td>'.$reply_to_email.'</td><td>'.$subject. '</td><td>'.$messages.'</td>
                    </tr>
                  </table>
                </body>
                </html>
                ';

    
    /* //don't forget to validate empty fields 
    if(strlen($sender_name)<1){
        die('Name is too short or empty!');
    } 
    */
    
    //Get uploaded file data
    $file_tmp_name    = $_FILES['my_file']['tmp_name'];
    $file_name        = $_FILES['my_file']['name'];
    $file_size        = $_FILES['my_file']['size'];
    $file_type        = $_FILES['my_file']['type'];
    $file_error       = $_FILES['my_file']['error'];

    if($file_error > 0)
    {
        die('Upload error or No files uploaded');
    }
    //read from the uploaded file & base64_encode content for the mail
    $handle = fopen($file_tmp_name, "r");
    $content = fread($handle, $file_size);
    fclose($handle);
    $encoded_content = chunk_split(base64_encode($content));

        $boundary = md5("sanwebe");
        //header
        $headers = "MIME-Version: 1.0\r\n"; 
        $headers .= "From:".$from_email."\r\n"; 
        $headers .= "Reply-To: ".$reply_to_email."" . "\r\n";
        $headers .= "Content-Type: multipart/mixed; boundary = $boundary\r\n\r\n"; 
        
        //plain text 
        $body = "--$boundary\r\n";
        $body .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
        $body .= "Content-Transfer-Encoding: base64\r\n\r\n"; 
        $body .= chunk_split(base64_encode($message)); 
        
        //attachment
        $body .= "--$boundary\r\n";
        $body .="Content-Type: $file_type; name=".$file_name."\r\n";
        $body .="Content-Disposition: attachment; filename=".$file_name."\r\n";
        $body .="Content-Transfer-Encoding: base64\r\n";
        $body .="X-Attachment-Id: ".rand(1000,99999)."\r\n\r\n"; 
        $body .= $encoded_content; 
    
    $sentMail = @mail($recipient_email, $subject, $body, $headers);
    if($sentMail) //output success or failure messages
    {       
        die('Thank you for your email');
    }else{
        die('Could not send mail! Please check your PHP mail configuration.');  
    }

}

?>


<!DOCTYPE html>
<html>
<head>
	<title>PHP Contact form - File Attachments</title>
	<style>
		body{
			text-align: center;
			margin-top: 100px;
		}
		input,textarea{
			width: 250px;
			height: 27px;
			margin-bottom: 10px;
		}
		textarea{
			height: 100px;
			resize: vertical;
		}
	</style>
</head>
<body>
	<div>
	<h3>PHP Contact form - File Attachments</h3>
        <form enctype="multipart/form-data" method="POST" action="">
        <input type="text" placeholder="Name" name="sender_name" required /><br>
        <input type="email" placeholder="Email" name="sender_email" required /><br>
        <input type="text" placeholder="Subject" name="subject" required /><br>
        <textarea placeholder="Message" name="message" ></textarea><br>
        <input type="file" placeholder="Attachment" name="my_file" required /><br>
        <input type="submit" name="button" value="Submit" /><br>
        </form>
	</div>

</body>
</html>
