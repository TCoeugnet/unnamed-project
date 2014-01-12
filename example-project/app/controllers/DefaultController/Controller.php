<?php

namespace DefaultController;

class Controller {

	protected $twig 	= null;
	protected $kernel 	= null;
	
	public function __construct($f3) {
		$this->twig 	= $f3->get('twig');
		$this->kernel 	= $f3->get('kernel');
		
		$f3->clear('twig');
		$f3->clear('kernel');
	}

}