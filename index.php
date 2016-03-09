<html>
<head>
	<title>Connection</title>
	<link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
</head>
<body>
	
	<?php
	//URL à afficher
	$url = "https://pictawall.com";
	
	//Retour form
	if(isset($_GET['action'])) {

		$ssid = $_POST['ssid'];
		$password = $_POST['password'];
		
		//Vérifier si le SSID donné est présent
		$ssidSearch = shell_exec('iwlist wlan0 scan | grep \"'.${ssid}.'\" ');
		
		//Si le ssid donné est bien présent -> ajout des données de connexions
		if($ssidSearch != NULL){
			
			//Edit le fichier wpa_supplicant.conf
			shell_exec('echo "ctrl_interface=DIR=/var/run/wpa_supplicant GROUP=netdev
update_config=1

network={
        ssid=\"'.${ssid}.'\"
        psk=\"'.${password}.'\"
        key_mgmt=WPA-PSK
}" > /etc/wpa_supplicant/wpa_supplicant.conf ');
			
			//Restart wlan0 pour prendre en charge les nouvelles données
			shell_exec('sudo ifdown wlan0');
			shell_exec('sudo ifup wlan0 ');
			
			//Sert à vérifier si la connexion wlan0 est up ou non
			$connection = NULL;
			
			//Compteur -> Si la connexion est anormalement longue
			$i=0;
			
			//Tant que la connexion n'est pas up on loop
			while ($connection == NULL){	
				$i++;
				if($i > 20){
					break;
				}
				sleep(1);
				$connection = shell_exec('/sbin/ifconfig | grep "Bcast" ');
			}
			
			//Si echec connection -> retour index
			if($i > 20){
				header('Location: index.php');
			}else{
			
			//Une fois connecté -> redirection
			header('Location: '.$url.' ' );
			}
		
		//Si le ssid donné n'est pas présent -> retour index
		}else{
			header('Location: index.php');
		}

	}else{
		
		//Vérifier la connection de eth0
		$eth0 = shell_exec('ip link show | egrep -w "eth0|no-CARRIER" ');
		
		//if eth0 no up -> Connection wlan0
		if($eth0){	
			
			//Check si SSID dispo
			$ssidDispo = shell_exec('sudo iwlink wlan0 scan | grep "ESSID"  ');
			if(!$ssidDispo){
		?>
		
				<h1>Connection Wifi</h1>
		
				<form method="POST" action="index.php?action=connect">
					<input type="text" placeholder="ssid" name="ssid" />
					<input type="password" placeholder="password" name="password" />
					<input type="submit" />
				</form>

		<?php
			//Si aucun AP disponible
			}else{
				echo 'Erreur: Aucun AP Disponible <br>';
				echo 'SSIDISPO : '.$ssidDispo;
				echo '<a href="index.php">Recommencer</a>';
			}
		
		//if eth0 up -> redirection vers URL directement
		}else{
			header('Location: '.$url.' ' );
		}



	}
	?>
</body>
</html>
