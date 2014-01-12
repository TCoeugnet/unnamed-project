<?php

namespace Session;

class User {

	private $username,
			$password;
			
	public function __construct($username, $password = 'azerty') {
	
		$this->username = $username;
		$this->password = $password;
		
	}
	
	public function getUsername() {
		return $this->username;
	}
	
	public function setUsername($username) {
		$this->username = $username;
	}
	
	public function getPassword() {
		return $this->password;
	}
	
	public function setPassword($password) {
		$this->password = $password;
	}

}