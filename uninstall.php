<?php
/* 
	Le code contenu dans cette page ne sera �xecut� qu'� la d�sactivation du plugin 
	Vous pouvez donc l'utiliser pour supprimer des tables SQLite, des dossiers, ou executer une action
	qui ne doit se lancer qu'� la d�sinstallation ex :
*/

/*
$table = new modele();
$table->drop();
*/


$table = new DHT();
$table->drop();

$table_section = new Section();
$id_section = $table_section->load(array("label"=>"sondes dht"))->getId();
$table_section->delete(array('label'=>'sondes dht'));

$table_right = new Right();
$table_right->delete(array('section'=>$id_section));
?>