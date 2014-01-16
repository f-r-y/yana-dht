<?php

/*
 @nom: DHT
 @auteur: Aymeric HM aka fry <f_r_y_@hotmail.com>
 @description:  plugin de lecture de sondes DHT (DHT11/DHT22/AM2302) via la lib Adafruit
  ( http://learn.adafruit.com/dht-humidity-sensing-on-raspberry-pi-with-gdocs-logging/overview
  et plus particulierement http://learn.adafruit.com/dht-humidity-sensing-on-raspberry-pi-with-gdocs-logging/software-install )
  /!\ www-data doit etre dans les sudoers (la lib adafruit doit etre appellée via sudo)/!\
 */

//Ce fichier permet de gerer vos donnees en provenance de la base de donnees

//Il faut changer le nom de la classe ici (je sens que vous allez oublier)
class DHT extends SQLiteEntity{

	
	protected $id,$name,$description,$pin,$type,$room; //Pour rajouter des champs il faut ajouter les variables ici...
	protected $TABLE_NAME = 'plugin_dht'; 	//Penser a mettre le nom du plugin et de la classe ici
	protected $CLASS_NAME = 'DHT';
	
	protected $nb_tries = 10;//la valeur retournée par la sonde n'est pas toujours bonne, nombre de tentatives avant abandon
	protected $delay_tries = 500000;//attente entre les essais en microsecondes par defaut 500000 soit 0.5s

	
	protected $object_fields = 
	array( //...Puis dans l'array ici mettre nom du champ => type
		'id'=>'key',
		'name'=>'string',
		'description'=>'string',
		'pin'=>'int',
		'type'=>'int',
		'room'=>'int',
	);

	function __construct(){
		parent::__construct();
	}
//Methodes pour recuperer et modifier les champs (set/get)
//potentiellement améliorable avec des getter/setter magiques?
	function setId($id){
		$this->id = $id;
	}
	
	function getId(){
		return $this->id;
	}

	function getName(){
		return $this->name;
	}

	function setName($name){
		$this->name = $name;
	}

	function getDescription(){
		return $this->description;
	}

	function setDescription($description){
		$this->description = $description;
	}

	function getPin(){
		return $this->pin;
	}

	function setPin($pin){
		$this->pin = $pin;
	}

	function getType(){
		return $this->type;
	}

	function setType($type){
		$this->type = $type;
	}

	function getRoom(){
		return $this->room;
	}

	function setRoom($room){
		$this->room = $room;
	}
	
	
	
	//récupération des infos de la sonde via la lib adafruit, future amélioration: integrer la lib au plugin et l'adapter
	function getValues(){
		$cmd = 'sudo /home/pi/Adafruit-Raspberry-Pi-Python-Code/Adafruit_DHT_Driver/Adafruit_DHT '.$this->type.' '.$this->pin;
		//'sudo /home/pi/Adafruit-Raspberry-Pi-Python-Code/Adafruit_DHT_Driver/Adafruit_DHT 11 25'
		$data = exec($cmd,$out);//a l'arrache, ne prend que la derniere ligne (ca nous arrange du coup, mais pas propre je trouve)
		$tries = $this->nb_tries;
		while ($tries>0 && !preg_match("/temp/i", $data)) {
			usleep($this->delay_tries);
			$data = exec($cmd,$out);
			$tries--;
		}
		if (!preg_match("/temp/i", $data)) {
			$data = 'Données non valides';
		}		
		return $data;
	}
}

?>