<?php
	if(!isset($_SESSION)) {
		session_start();
	}
	//we give the session a unique csrf token so malicious links on other sites cannot take advantage of users
	if(!isset($_SESSION['csrftoken'])) {
		$_SESSION['csrftoken'] = rand();
	}

	if(isset($_SESSION['loggedin'])) {
		$response = [
			"redirect" => "/index.php"
		];
	} else {
		if(isset($_POST['username']) && isset($_POST['password']) && isset($_POST['csrftoken'])) {
			$username = $_POST['username'];
			$password = $_POST['password'];
			$csrftoken = $_POST['csrftoken'];

			if($csrftoken != $_SESSION['csrftoken']) {
				$response = [
					"message" => "Cross Site Request Forgery Detected!"
				];
			} else {
				require_once(realpath(dirname(__DIR__) . "/class/AccountManager.php"));

				if(isset($_POST['redirect'])) {
					$redirect = $_POST['redirect'];
					$response = AccountManager::login($username, $password, $redirect);
				} else {
					$response = AccountManager::login($username, $password);
				}
			}
		} else {
			if(isset($_SESSION['justregistered']) && $_SESSION['justregistered'] == 1) {
				$response = [
					"message" => "Thank you for registering!  Please log in to continue."
				];
				$_SESSION['justregistered'] = 0;
			} else {
				$response = [
					"message" => "Form incomplete."
				];
			}
		}
	}
	return $response;
?>
