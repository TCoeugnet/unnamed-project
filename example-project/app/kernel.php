<?php

class Kernel {
	
	private $prefix 			= '';
	private $app_dir 			= '';
	private $lib_dir 			= '';
	private $views_dir 			= '';
	private $config_dir 		= '';
	private $global_config_file = '';
	private $global_routes_file = '';
	private $f3 				= null;
	
	public function __construct($prefix = '', $debug = false) {
	
		if(!ob_start('ob_gzhandler')) //Si la compression gzip n'est pas geree
			ob_start();
		
		if ((float)PCRE_VERSION<7.9)
			trigger_error('PCRE version is out of date');
			
		$this->createDirectoriesVars($prefix);
		$this->loadF3();
		
		if(!$debug) {
			$this->f3->set('DEBUG', 0);
		}
		
		$this->loadTwig();
		//...
	}
	
	public function redirect($url) {
		$this->f3->set('twig', $this->twig);
		$this->f3->set('kernel', $this);
		
		/*
			ATTENTION ICI, IL S'AGIT D'UN COPIER/COLLER DEGUEULASSE
			La partie suivante est un copier/coller de la fonction Base->Reroute()
			Elle a subit les modifications suivantes :
				- Changement des $this->hive['...'] en $this->f3->get('...')
				- Changement des autres $this->... en $this->f3->...
				- Changement du self::E_Named en Base::E_Named
				- Suppression de la ligne header('Location: '.$url);
				- Suppression de la ligne $this->status...
				- Suppression de la ligne die;
				- Suppression du parametre $permanent
				- Suppression de $this->hive['BASE']
				- Changement de $this->hive['ALIASES'][$parts[1]] en $this->f3->get('ALIASES.' . $parts[1])
				
			ELLE DOIT ABSOLUMENT ETRE MISE A JOUR EN CAS DE MODIFICATION SUR Base->Reroute() !!!
			
		*/
		
		if (PHP_SAPI!='cli') {
			if (preg_match('/^(?:@(\w+)(?:(\(.+?)\))*|https?:\/\/)/', $url,$parts)) {
				if (isset($parts[1])) {
					
					var_dump($this->f3->get('ALIASES.' . $parts[1]));
					var_dump($parts[1]);
					
					if ($this->f3->get('ALIASES.' . $parts[1]) === null)
						user_error(sprintf(Base::E_Named,$parts[1]));
						
					$url = $this->f3->get('ALIASES.'.$parts[1]);
					
					if (isset($parts[2]))
						$this->f3->parse($parts[2]);
					$url=$this->f3->build($url);
				}
			}
			else
				$url=$url;
		}
		
		ob_clean(); //On vire ce qui a ete affiche precedemment
		$this->f3->mock('GET '.$url);
		/*
			Fin du copier/coller modifie
		*/
		
	}
	
	public function reroute($new_route) {
	
		$this->f3->set('twig', $this->twig);
		$this->f3->set('kernel', $this);
		
		$this->f3->reroute($new_route);
		
	}
	
	public function run() {
	
		$this->f3->set('twig', $this->twig);
		$this->f3->set('kernel', $this);
		
		$this->f3->run();
	}
	
	private function createDirectoriesVars($prefix) {
		$this->prefix 				= $prefix . '/';
		$this->app_dir				= __DIR__ . '/';
		$this->lib_dir 				= $this->app_dir . '/lib';
		$this->config_dir 			= $this->app_dir . '/config';
		$this->global_config_file	= 'config.ini';
		$this->global_routes_file	= 'routes.ini';
	}
	
	private function loadF3() {
		
		if($this->f3 !== null)
			return;
		
		$this->f3 = require_once $this->lib_dir . '/base.php';
		//Chargement des fichiers de configuration
		
		$this->f3->config($this->config_dir . '/' . $this->global_config_file);
		$this->f3->config($this->config_dir . '/' . $this->global_routes_file);
		
		/** 

		Structure des fichiers

		AUTOLOAD	: fichiers php à charger (classes + controllers)
		CACHE		: fichiers de cache (vaut toujours 'folder=' . TEMP . '/cache' quelle que soit la valeur dans config... ou false si la clé n'est pas dans config.ini)
		LOCALES		: fichiers de traduction
		LOGS		: fichiers de logging
		TEMP		: fichiers temporaires
		UI			: fichiers de vues

		*/
		$directories = array('AUTOLOAD', 'LOCALES', 'LOGS', 'TEMP', 'UI');

		array_map( function($key_global) {
			$val = $this->f3->get($key_global);
			
			if($val) {
				$locations = explode(';', $val);
				
				foreach($locations as &$location) {
					if(trim($location) !== '')
						$location = trim($this->prefix . trim($location));
				}
			
				$this->f3->set($key_global, implode('; ', $locations));
			}

		}, $directories);


		$this->views_dir = $this->prefix . substr($this->f3->get('CACHE'), 7); //Voir note au dessus
	}
	
	private function loadTwig() {
		require_once $this->lib_dir . '/Twig/Autoloader.php';
		Twig_Autoloader::register();
		
		$twig_loader = new Twig_Loader_Filesystem($this->f3->get('UI')); //On configure twig avec le dossier contenant les vues
		$this->twig = new Twig_Environment($twig_loader, array(
			'cache' => $this->views_dir,
			'auto_reload' => true,
		));

		$this->twig->addFilter('f3', new Twig_Filter_Function('F3::get'));
		
		// $this->twig->addGlobal('is_ajax', Web::isajax());

		
		if($this->f3->get('DEBUG') == 0) {
			//On affiche le minimum en DEBUG = 0 (prod)

			$this->f3->set('ONERROR', //On définit un handler pour les erreurs
				function($f3) {
					$error = array(
						'code' => $f3->get('ERROR.code') ?: 500, //Erreur par défaut : 500
						'title' => $f3->get('ERROR.title') !== null ?: $f3->status($f3->get('ERROR.code')),
						'text' => $f3->get('ERROR.text'),
						'trace' => $f3->get('ERROR.trace')
					);
					//$error contient les infos de base de l'erreur
					
					
					$error_template = $f3->get('ERROR_' . $error['code']) ?: $f3->get('ERROR_FALLBACK');
					
					//N'affichera rien si le fichier de template n'est pas lisible
					if($error_template == null)
						echo '<!doctype html><html><head><title>' . $error['code'] . '</title></head><body><h1>ERROR</h1></body></html>';
					else if(!is_readable($error_template))
						echo '<!doctype html><html><head><title>' . $error['code'] . '</title></head><body><h1>ERROR</h1><p>You have an error and the template "' . $error_template . '" does not exist or is not readable.</p></body></html>';
					else
						echo $this->twig->render($error_template, array('error' => $error));
				}
			);
		}
	}
}