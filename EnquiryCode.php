<?php

	    $to = "acrosticitsolutions@gmail.com";
        //$to = "deepakpathak089@gmail.com";       
		$subject = "myexamsprep : New Query";
        $txtName = $_POST['txtName'];
        $txtMobile = $_POST['txtMobile'];
        $txtEmail = $_POST['txtEmail'];
        $txtDate  = date("d/m/Y");
        $txtMsg = $_POST['txtMsg'];
        

        $message = "
                        <div style='border:1px solid #7DAD51'>
                            <h1 style='font-family:Arial; font-size:17px; font-weight:normal; padding:15px 25px; margin:0px; background:#7DAD51; color: #fff'>".$subject."</h1>

                            <table style='font-family:Arial; margin: 25px 40px; width: 90%;'>
                                <tr>
                                    <td style='width:200px;'>Name</td><td style='width:10px'>:</td><td>".$txtName."</td>
                                </tr>
                                <tr><td colspan='3' style='height:10px;'><div style='border-top:1px solid silver'></div></td></tr>
                                
                                <tr>
                                    <td style='width:100px;'>Mobile</td><td style='width:10px'>:</td><td>".$txtMobile."</td>
                                </tr>
                                <tr><td colspan='3' style='height:10px;'><div style='border-top:1px solid silver'></div></td></tr>

                                <tr>
                                    <td style='width:100px;'>Email</td><td style='width:10px'>:</td><td><a href='mailto:".$txtEmail."' style='color:#118bf2; text-decoration:none'>".$txtEmail."</a></td>
                                </tr>
                                <tr><td colspan='3' style='height:10px;'><div style='border-top:1px solid silver'></div></td></tr>
                                <tr>
                                    <td style='width:100px;'>Date of contact:</td><td style='width:10px'>:</td><td>".$txtDate."</td>
                                </tr>
                                <tr><td colspan='3' style='height:10px;'><div style='border-top:1px solid silver'></div></td></tr>
                                <tr>
                                    <td style='width:100px;'>Message</td><td style='width:10px'>:</td><td>".$txtMsg."</td>
                                </tr>
                                <tr><td colspan='3' style='height:10px;'><div style='border-top:1px solid silver'></div></td></tr>
                            </table>
                        </div>    
                            <script language='javascript'>
                            function happycode(){
                               alert('helo');
                            }
                            </script>
                        ";
           //echo $message;


					// Always set content-type when sending HTML email
					$headers = "MIME-Version: 1.0" . "\r\n";
					$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

					// More headers
					$headers .= "From: ".$_POST['txtName']."<".$_POST['txtEmail'].">\nReply-To: ".$_POST['txtEmail']."";

					$flgSend = mail($to,$subject,$message,$headers);





	//$flgSend = @mail($to,$subject,"",$strHeader);
	if($flgSend == true)
                    {   
                        echo "<script>window.location.assign('Thankyou.php')</script>";                        
                    }
                    else
                    {
                        echo "error";
                    }
    
        $to = "$txtEmail";
        //$to = "deepakpathak089@gmail.com";       
		$subject = "myexamsprep : Your Query";
        $txtName = $_POST['txtName'];
        $txtMobile = $_POST['txtMobile'];
        $txtEmail = $_POST['txtEmail'];
        $txtDate  = $_POST['txtDate'];
        $txtMsg = $_POST['txtMsg'];
        

        $message = "
                        <div style='border:1px solid #7DAD51'>
                            <h1 style='font-family:Arial; font-size:17px; font-weight:normal; padding:15px 25px; margin:0px; background:#7DAD51; color: #fff'>".$subject."</h1>

                            <table style='font-family:Arial; margin: 25px 40px; width: 90%;'>
                                <tr>
                                    <td><p>Hi <b> ($txtName), </b></p></td>
                                </tr>
                                <tr><td colspan='3' style='height:10px;'><div style='border-top:1px solid silver'></div></td></tr>
                                <tr>
                                    <td><p>Thanks for contacting us!</p></td>
                                </tr>
                                <tr>
                                    <td><p>We'll get back to you as soon as we can.</p></td>
                                </tr>                                
                                <tr>
                                    <td><p><b>Regards,</b></p></td>
                                </tr>                                
                                <tr>
                                    <td><p>Team Sponsor a Student</p></td>
                                </tr>
                                
                            </table>
                        </div>    
                            <script language='javascript'>
                            function happycode(){
                               alert('helo');
                            }
                            </script>
                        ";
            //echo $message;


					// Always set content-type when sending HTML email
					$headers = "MIME-Version: 1.0" . "\r\n";
					$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

					// More headers
					$headers .= "From: ".$_POST['txtName']."<".$_POST['txtEmail'].">\nReply-To: ".$_POST['txtEmail']."";

					$flgSend = mail($to,$subject,$message,$headers);





	//$flgSend = @mail($to,$subject,"",$strHeader);
	if($flgSend == true)
                    {   
                        echo "<script>window.location.assign('../../Thankyou.php')</script>";                        
                    }
                    else
                    {
                        echo "error";
                    }
?>