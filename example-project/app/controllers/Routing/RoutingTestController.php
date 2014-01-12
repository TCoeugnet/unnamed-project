<?php

namespace Routing; //Car on se trouve dans le dossier controllers/Routing
use DefaultController\Controller;//Un controleur DOIT heriter de la classe Controller, sinon pas de twig ni de kernel (explication dans les exemples)
//Cette classe se trouve définie dans le fichier controllers/DefaultController/Controller, c'est pour cela que l'on ajoute cette ligne


/**
* 
* Classe d'exemple pour tester les fonctions de Routage
* @see https://github.com/bcosca/fatfree#routing-engine
* @author Thomas Coeugnet
* @version 0.0.1
* @since 0.0.1
*
*/
class RoutingTestController extends Controller { //Comme dit precedemment, la classe doit heriter de la classe Controller
	
	
	/**
	* 
	* Fonction de base appelée pour la route nommée @index
	* @param {Base} f3 l'objet FatFree de base
	* @param {Array} params un tableau associatif ayant pour clé le nom du paramètre et pour valeur sa valeur
	* 
	*/
	public function indexAction($f3, $params) {
		/*
			Ici $f3 est l'objet Base FatFree
				$params est la liste des parametres + l'url ayant permis d'acceder a ce controleur
				Il est de la forme :
				[0] 		=> '/chemin/relatif',
				['param1'] 	=> 'val1',
				...
			
			On utilise $this->twig->render('chemin/relatif/vers/vue.html.twig', array('param1' => 'val1', 'param2' => array('param2_1' => $objet1)));
			Les variables passees en second parametre seront accessibles via twig grace a la syntaxe : {{ param1 }}, {{ param2['param2_1'] }}, {{ param2['param2_1'].membre }}
			Plus d'infos dans la documentation de Twig http://twig.sensiolabs.org/documentation
			L'objet $this->twig est cree lors de la creation de la classe Controller, dont tous les controleurs doivent heriter
			
		*/
		
		echo $this->twig->render('index/index.html.twig', array('params' => $params));
	}
	
	/**
	* 
	* Fonction permettant de tester le passage de paramètre ainsi que les redirections sur des routes nommées
	* @param {Base} f3 l'objet FatFree de base
	* @param {Array} params un tableau associatif ayant pour clé le nom du paramètre et pour valeur sa valeur
	* 
	*/
	public function articleAction($f3, $params) {
		
		$id = $params['idArt']; //Recupération du paramètre 'idArt'
		
		if(!preg_match('/^\d+$/', $id)) //S'il ne s'agit pas d'un nombre entier positif (pour plus d'infos sur les REGEX PERL, voir http://www.php.net/manual/fr/reference.pcre.pattern.syntax.php
		{
			/*
				L'objet Kernel ayant initié le routage est stocké dans $this->kernel
				La methode Kernel->reroute permet d'envoyer un header 'Location: ...' et ainsi rediriger le client en modifiant l'URL
				Il s'agit d'un alias de la fonction $f3->reroute mais qui permet de transmettre au controleur twig ainsi que le kernel.
				ATTENTION : Si on fait $f3->reroute directement, il est probable que cela ne fonctionne pas, il faut au préalable supprimer les appels à la fonction Kernel->afterRoute
							Il est donc fortement deconseille de faire un $f3->reroute, surtout que ca n'apporte rien de plus au $this->kernel->reroute
				
				Ce type de redirection force le client a realiser une autre requete HTTP, ce qui ralentit la navigation (certes tres peu, mais si on peut eviter...)
				Toutefois, cela reste indispensable dans de nombreuses situations car cela ne change pas seulement le contenu, mais aussi l'URL.
				
				Pour changer seulement de contenu (et donc ne pas modifier l'URL, et ne pas creer de requete HTTP), plusieurs methodes sont disponibles :
					- (1) Invoquer directement le controleur et la methode choisis. C'est sale et difficile a maintenir. A eviter
					- (2) Faire un : return $f3->mock('GET /chemin/sur/nouveau/contenu'); C'est mieux, mais ca reste difficile a maintenir et il est impossible d'utiliser le nom de la route
									 En plus, dans ce cas, on devra remettre les valeurs de twig et du kernel manuellement dans le controleur
					- Faire un : 	 (3) return $this->kernel->redirect('GET /chemin/sur/nouveau/contenu'); OU
									 (4) return $this->kernel->redirect('@nomRoute(@param1=val1,@param2=val2)');
								 
				La dernière méthode (4) est conseillée. Elle est facile a maintenir et a comprendre.
				
				(Pour les developpeurs)
				Toutefois, etant donne que ce n'est pas prevu par FatFree de base, il a fallu dupliquer une partie du code.
				Il est donc IMPERATIF de modifier la methode Kernel->redirect en cas de modification de FatFree. Pour plus d'informations, voir ladite methode Kernel->redirect
			*/
			
			
			return $this->kernel->reroute('@article(@idArt=0)'); //Redirection 302 sur la route nommee 'article' en mettant le parametre idArt = 0
			// Voir le fichier de configuration des routes
			
			/*
			
			Ces 2 redirections sont les memes que celle effectuee precedemment mais en utilisant la redirection sans modification de l'URL
			
			return $this->kernel->redirect('@article(@idArt=0)');
			return $this->kernel->redirect('/article-0');
			
			*/
		
		}
		
		$article = array(
			'id' => $id ?: 0, // $id != null ? $id : 0
			'content' => 'Un bel article'
		);
		
		echo $this->twig->render('index/article.html.twig', array('article' => $article));
		
	}

}