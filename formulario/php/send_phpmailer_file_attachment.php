<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content="Advanced Contact Form with File Uploader">
	<meta name="author" content="UWS">
	<title>Sendy | Advanced Contact Form</title>

	<!-- Favicon -->
   <link rel="shortcut icon" href="images/favicon-32x32.png">

	<!-- Google Fonts - Poppins, Karla -->
	<link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css?family=Karla:300,400,500,600,700" rel="stylesheet">

	<!-- Font Awesome CSS -->
	<link href="../vendor/fontawesome/css/all.min.css" rel="stylesheet">

	<!-- Custom Font Icons -->
	<link href="../vendor/icomoon/css/iconfont.min.css" rel="stylesheet">

	<!-- Vendor CSS -->
	<link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
	<link href="../vendor/dmenu/css/menu.css" rel="stylesheet">
	<link href="../vendor/hamburgers/css/hamburgers.min.css" rel="stylesheet">
	<link href="../vendor/mmenu/css/mmenu.min.css" rel="stylesheet">
	<link href="../vendor/filepond/css/filepond.css" rel="stylesheet">

	<!-- Main CSS -->
	<link href="../css/style.css" rel="stylesheet">

</head>

<body onLoad="setTimeout('delayedRedirect()', 5000)">

<?php

/* Setup PHPMailer
==================================== */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '/formulario/php/phpmailer/src/Exception.php';
require '/formulario/php/phpmailer/src/PHPMailer.php';

$mail = new PHPMailer(true);

/* Validate User Inputs
==================================== */

// Name 
if ($_POST['username'] != '') {
	
	// Sanitizing
	$_POST['username'] = filter_var($_POST['username'], FILTER_SANITIZE_STRING);

	if ($_POST['username'] == '') {
		$errors .= 'Por favor ingrese un nombre valido.<br/>';
	}
}
else { 
	// Required to fill
	$errors .= 'Por favor, escriba su nombre.<br/>';
}

// Email 
if ($_POST['email'] != '') {

	// Sanitizing 
	$_POST['email'] = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

	// After sanitization validation is performed
	$_POST['email'] = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
	
	if($_POST['email'] == '') {
		$errors .= 'Please enter a valid email address.<br/>';
	}
}
else {
	// Required to fill
	$errors .= 'Por favor, introduzca su dirección de correo electrónico.<br/>';
}

// Phone 
if ($_POST['phone'] != '') {

	// Sanitizing
	$_POST['phone'] = filter_var($_POST['phone'], FILTER_SANITIZE_STRING);

	// After sanitization validation is performed
	$pattern_phone = array('options'=>array('regexp'=>'/^\+{1}[0-9]/'));
	$_POST['phone'] = filter_var($_POST['phone'], FILTER_VALIDATE_REGEXP, $pattern_phone);
	
	if($_POST['phone'] == '') {
		$errors .= 'Please enter a valid phone number like: +363012345<br/>';
	}
}

/* Validate Hidden Inputs
==================================== */

function sanitizePostTitle($postName, $invalidMessage) {
	
	if ($_POST[$postName] != '') {
		
		// Sanitizing
	  	$_POST[$postName] = filter_var($_POST[$postName], FILTER_SANITIZE_STRING);
		  
		if ($_POST[$postName] == '') {
			return $invalidMessage . '<br/>';
	  	}

	}
	return '';
}

$errors .= sanitizePostTitle('subject', 'Please set a valid Subject.');

// Continue if NO errors found after validation
if (!$errors) {	

	/* Mail Sending
	==================================== */

	try {

    	// Recipients
    	$mail->setFrom('a.saldana@clubbodyshop.com', 'Body Shop Talent');                				// Set Sender    	
		$mail->addAddress('aneudysq@gmail.com', 'Ultimate Websolutions'); 	// Set Recipients		
    	$mail->addReplyTo(['email']);          						// Set Reply-to Address
    	$mail->isHTML(true);                                                       
    	$mail->Subject = 'Message';                                     		// Email Subject

		// Add the uploaded file in attachment if exists		
		$tmp_dirs = [];
		$attachment_ids = $_POST['filepond'];
		foreach($attachment_ids as $attachment_id) {

			$dir = 'tmp/'.$attachment_id;
			$tmp_dirs[] = $dir;
			$file = glob('tmp/'.$attachment_id.'/*.*')[0];
			$mail->addAttachment($file);

		}

		// Handle if user provided a file or not
		if (file_exists($file)) {
			$file_attachment = 'Can be found attached';
		} else {
			$file_attachment = 'was NOT provided';
		}

    	// Content
    	$mail->isHTML(true);
		$mail->Body    = '<strong>El mensaje llegó con los siguientes detalles.</strong> ' . '<br /><br />' .
		'<strong>Nombre:</strong> ' . $_POST['username'] . '<br />' .		
		'<strong>Correo:</strong> ' . $_POST['email'] . '<br />' .
		'<strong>Teléfono:</strong> ' . $_POST['phone'] . '<br />' .
		'<strong>Posición:</strong> ' . $_POST['subject'] . '<br /><br />' .
		'<strong>Argumento:</strong> '. '<br />' . $_POST['message'] . '<br /><br />' . 
		'<strong>Archivo:</strong> ' . $file_attachment;
		
		// Send to site owner
		$mail->Send();

		// Send the confirmation to the user who filled the form
		$mail->clearAddresses();
		$mail->clearAttachments();
		$mail->addAddress($_POST['email']); // Email address entered on the form by the visitor
		$mail->isHTML(true);
		$mail->Subject    = 'Confirmación';
		$mail->Body    = '<strong> ' . $_POST['username'] . '</strong>,<br /><br />' . 
		'Recibimos tu mensaje. Gracias por contactarnos.<br /><br />' .
		'Body Shop';

		// Send to who filled the form
		$mail->send();

	} catch (Exception $e) {

		echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";

	} finally {

		foreach($tmp_dirs as $tmp_dir) {

			foreach(scandir($tmp_dir) as $file_name) {

				if($file_name != '.' && $file_name != '..') {
					unlink($tmp_dir.'/'.$file_name);
				}
			}
			// Clean up the tmp folder, delete the uploaded file
			rmdir($tmp_dir);
		}
	}

	// Success Page
	echo '<div id="success">';
	echo '<div class="icon icon-order-success svg">';
	echo '<svg width="72px" height="72px">';
	echo '<g fill="none" stroke="#53c4da" stroke-width="2">';
	echo '<circle cx="36" cy="36" r="35" style="stroke-dasharray:240px, 240px; stroke-dashoffset: 480px;"></circle>';
	echo '<path d="M17.417,37.778l9.93,9.909l25.444-25.393" style="stroke-dasharray:50px, 50px; stroke-dashoffset: 0px;"></path>';
	echo '</g>';
	echo '</svg>';
	echo '</div>';    
	echo '<h4>Gracias por contactar <br> con nosotros.</h4>';
	echo '<small>Revisa tu correo.</small>';
	echo '</div>';
	echo '<script src="/../redirect.js"></script>';

} else {

	// Error Page
	echo '<div style="color: #e9431c">' . $errors . '</div>';
	echo '<div id="success">';    
	echo '<h4>Algo salió mal.</h4>';
	echo '<a class="animated-link" href="./formulario.html">Ir al formulario</small>';
	echo '</div>';	
}

?>
<!-- END PHP -->

</body>
</html>