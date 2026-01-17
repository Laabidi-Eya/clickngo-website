<?php
		include '../bootstrap/start.php';

		$recaptcha = new \ReCaptcha\ReCaptcha('6LfNwjAUAAAAADZiFxwWsdTV9sGA4UntOxQa_JdP');
		$resp = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
		if (!$resp->isSuccess()) {

		    $errors = $resp->getErrorCodes();

		    var_dump($errors);
		} else {

			echo "Permitido";
			
		}
 