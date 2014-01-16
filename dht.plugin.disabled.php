<?php
/*
@name DHT
@author Aymeric HM aka fry <f_r_y_@hotmail.com>
@link https://github.com/f-r-y/yana-dht
@licence CC by nc sa
@version 0.0.2
@description plugin de lecture de sondes DHT (DHT11/DHT22/AM2302) via la lib Adafruit
  ( http://learn.adafruit.com/dht-humidity-sensing-on-raspberry-pi-with-gdocs-logging/overview
  et plus particulierement http://learn.adafruit.com/dht-humidity-sensing-on-raspberry-pi-with-gdocs-logging/software-install )
  /!\ www-data doit etre dans les sudoers (la lib adafruit doit etre appellée via sudo)/!\
*/

//Si vous utiliser la base de donnees a ajouter
include('DHT.class.php');

function dht_plugin_setting_page(){
	global $_,$myUser;
	if(isset($_['section']) && $_['section']=='dht' ){

		if($myUser!=false){
			$DHTManager = new DHT();
			$DHTs = $DHTManager->populate();
			$roomManager = new Room();
			$rooms = $roomManager->populate();
			$types = array(11=>'DHT11', 22=>'DHT22', 2803=>'AM2803');
			$selected =  new DHT();

			//Si on est en mode modification
			if (isset($_['id']))
				$selected = $DHTManager->getById($_['id']);
			
			?>

		<div class="span9 userBloc">


		<h1>DHT</h1>
		<p>Gestion des sondes DHT11 / DHT22 / AM2803</p>  

		<form action="action.php?action=dht_add_dht" method="POST">
		<fieldset>
		    <legend>Formulaire de la sonde</legend>

		    <div class="left">
			    <label for="nameDHT">Nom</label>
			    <input type="hidden" name="id" value="<?php echo $selected->getId(); ?>">
			    <input type="text" id="nameDHT" value="<? echo $selected->getName(); ?>" onkeyup="$('#vocalCommand').html($(this).val());" name="nameDHT" placeholder="le bureau …"/>
			    <small>Commande vocale associée : "<?php echo VOCAL_ENTITY_NAME; ?>, sonde <span id="vocalCommand">le bureau</span>"</small>
			    <label for="descriptionDHT">Description</label>
			    <input type="text" name="descriptionDHT" value="<?echo $selected->getDescription(); ?>" id="descriptionDHT" placeholder="Sonde du bureau…" />
			    <label for="pinDHT">Pin GPIO (Numéro de pin réel du connecteur, cd adafruit)</label>
			    <input type="text" name="pinDHT" value="<? echo $selected->getPin(); ?>" id="pinDHT" placeholder="25 …" />
				<label for="typeDHT">Type du capteur (DHT11 DHT22 AM2803)</label>
			    <select name="typeDHT" id="typeDHT">
			    	<?php foreach($types as $type=>$label){ ?>
			    	<option <? if ($selected->getType()== $type){echo 'selected="selected"';} ?> value="<?php echo $type; ?>"><?php echo $label; ?></option>
			    	<?php } ?>
			    </select>
			    <label for="roomDHT">Pièce</label>
			    <select name="roomDHT" id="roomDHT">
			    	<?php foreach($rooms as $room){ ?>
			    	<option <? if ($selected->getRoom()== $room->getId()){echo "selected";} ?> value="<?php echo $room->getId(); ?>"><?php echo $room->getName(); ?></option>
			    	<?php } ?>
			    </select>
			     
			</div>

  			<div class="clear"></div>
		    <br/><button type="submit" class="btn">Enregistrer</button>
	  	</fieldset>
		<br/>
	</form>

		<table class="table table-striped table-bordered table-hover">
	    <thead>
	    <tr>
	    	<th>Nom</th>
		    <th>Description</th>
		    <th>Pin GPIO</th>
		    <th>Type</th>
		    <th>Pièce</th>
		    <th></th>
	    </tr>
	    </thead>
	    
	    <?php foreach($DHTs as $DHT){ 

	    	$room = $roomManager->load(array('id'=>$DHT->getRoom())); 
	    	?>
	    <tr>
	    	<td><?php echo $DHT->getName(); ?></td>
		    <td><?php echo $DHT->getDescription(); ?></td>
		    <td><?php echo $DHT->getPin(); ?></td>
		    <td><?php echo $types[$DHT->getType()]; ?></td>
		    <td><?php echo $room->getName(); ?></td>
		    <td><a class="btn" href="action.php?action=dht_delete_dht&id=<?php echo $DHT->getId(); ?>"><i class="icon-remove"></i></a>
		    <a class="btn" href="setting.php?section=dht&id=<?php echo $DHT->getId(); ?>"><i class="icon-edit"></i></a></td>
		    </td>
	    </tr>
	    <?php } ?>
	    </table>
		</div>

<?php }else{ ?>

		<div id="main" class="wrapper clearfix">
			<article>
					<h3>Vous devez être connecté</h3>
			</article>
		</div>
<?php
		}
	}

}

function dht_plugin_setting_menu(){
	global $_;
	echo '<li '.(isset($_['section']) && $_['section']=='dht'?'class="active"':'').'><a href="setting.php?section=dht"><i class="icon-chevron-right"></i> Sondes DHT</a></li>';
}




function dht_display($room){
	global $_;


	$DHTManager = new DHT();
	$DHTs = $DHTManager->loadAll(array('room'=>$room->getId()));
	
	$types = array(11=>'DHT11', 22=>'DHT22', 2803=>'AM2803');
	
	foreach ($DHTs as $DHT) {
			
	?>

	<div class="span3">
          <h5><?php echo $DHT->getName() ?></h5>
		   
		   <p><?php echo $DHT->getDescription() ?>
		  	</p><ul>
		  		<li>PIN GPIO : <code><?php echo $DHT->getPin() ?></code></li>
		  		<li>Type : <code>Sonde <?php echo $types[$DHT->getType()]; ?></code></li>
		  		<li>Emplacement : <code><?php echo $room->getName() ?></code></li>
		  		<!--li>Valeurs : <code><?php /*echo $DHT->getValues() //ralentis trop la page (pb de com' avec le capteur) améliorer en stockant la date et le dernier relevé dans la bd? */?></code></li-->
		  	</ul>
		  <p></p>
		  	 <div class="btn-toolbar">
				<div class="btn-group">
				</div>
			</div>
        </div>


	<?php
	}
}


function dht_vocal_command(&$response,$actionUrl){
	$DHTManager = new DHT();

	$DHTs = $DHTManager->populate();
	
	foreach($DHTs as $DHT){
		$response['commands'][] = array('command'=>VOCAL_ENTITY_NAME.', sonde '.$DHT->getName(),'url'=>$actionUrl.'?action=dht_read_values&engine='.$DHT->getId().'&webservice=true','confidence'=>'0.9');
	}
}

function dht_action_dht(){
	global $_,$conf,$myUser;

	switch($_['action']){
		case 'dht_delete_dht':
			if($myUser->can('sondes dht','d')){
				$DHTManager = new DHT();
				$DHTManager->delete(array('id'=>$_['id']));
			}
			header('location:setting.php?section=dht');
		break;/*
		case 'dht_plugin_setting':
			$conf->put('plugin_wireRelay_emitter_pin',$_['emiterPin']);
			$conf->put('plugin_wireRelay_emitter_code',$_['emiterCode']);
			header('location: setting.php?section=preference&block=wireRelay');
		break;*/

		case 'dht_add_dht':
			if($myUser->can('sondes dht',$_['id']!=''? 'u' : 'c')){
				$DHTManager = new DHT();
				$DHT = $_['id']!=''?$DHTManager->getById($_['id']): new DHT();
				
				$DHT->setName($_['nameDHT']);
				$DHT->setDescription($_['descriptionDHT']);
				$DHT->setPin($_['pinDHT']);
				$DHT->setType($_['typeDHT']);
				$DHT->setRoom($_['roomDHT']);
				$DHT->save();
			}
			header('location:setting.php?section=dht');

		break;
		case 'dht_read_values':
			global $_,$myUser;

			
			if($myUser->can('sondes dht','u')){
				$DHTManager = new DHT();
				$DHT = $DHTManager->getById($_['engine']);
				$values = str_replace(array('\r', '\n'), ' ', $DHT->getValues());

				//TODO change bdd state
				
				if(!isset($_['webservice'])){
					header('location:index.php?module=room&id='.$DHT->getRoom());
				}else{
					$affirmation = 'Il fait '.$values;
					$response = array('responses'=>array(
											array('type'=>'talk','sentence'=>$affirmation)
														)
									);

					$json = json_encode($response);
					echo ($json=='[]'?'{}':$json);
				}
			}else{
				$response = array('responses'=>array(
											array('type'=>'talk','sentence'=>'Je ne vous connais pas, je refuse de faire ça!')
														)
									);
				echo json_encode($response);
			}
		break;
	}
}


Plugin::addCss("/css/style.css");

Plugin::addHook("action_post_case", "dht_action_dht"); 
Plugin::addHook("node_display", "dht_display");   
Plugin::addHook("setting_bloc", "dht_plugin_setting_page");
Plugin::addHook("setting_menu", "dht_plugin_setting_menu");  
Plugin::addHook("vocal_command", "dht_vocal_command");
?>