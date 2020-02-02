v<?php
	set_time_limit(0);

	// Version: 4.1.2
	if(!file_exists("./includes/config.php") == true) {
		die("Plik config.php nie istnieje!");
	}else{
		$fgc = file_get_contents('./includes/config.php');
	}
	$fgc = preg_replace('/\'ver\'(.*)=> \'([0-9.]+)\'/', '\'ver\'	=> \'4.1.3\'', $fgc);
	if(preg_match("/'channel_name'.*=>.*'(.*)'\/\/(.*)./", $fgc)){
		$fgc = preg_replace("/'channel_name'.*=>.*'(.*)'\/\/(.*)./", "'channel_name'	=>	'$1',\t//$2\n					'info' => true\t//Czy ma wysyłać informacje o tym, że użytkownik rozpoczął transmisję live true - tak false - nie ,\n					'info_text' => 'Hejka naklejka {1} właśnie odpalił live {2} Zapraszamy!'	//Tekst, który ma wysyłać gdy użytkownik odpali live {1} - nick {2} - link do kanału", $fgc);
		echo "Dodano dwie opcje do config.php 'info' - która pozwala nam ustawić czy ma wysyłać informacje o tym, że użytkownik rozpoczął transmisję live oraz 'info_text' - która pozwala ustawić tekst wysyłany podczas odpalania transmisji";
	}else{
		echo "Coś poszło nie tak:C Nie można zaktualizować config.php";
	}
	if(preg_match("/\/\/Czy ma zmieniać nazwę kanału i ustawiać liczbę nowych użytkowników 1 - tak 0 - nie.*\],(.*)\/\/Poke/s", $fgc)){
		
		$fgc = preg_replace("/(\/\/Czy ma zmieniać nazwę kanału i ustawiać liczbę nowych użytkowników 1 - tak 0 - nie.*\],)(.*)(\/\/Poke)/s", "$1\n\n	//Points() Funkcja .\n		'functions_Points' => [\n			'on'	=> true,	//true - włączona false - wyłączona\n			'inst'	=> 2, //ID Instancji \n			'top_list' => true,	//Czy ma ustawiać TOP w opisie kanału.\n			'cid' => 1,	//ID kanału gdzie ma wyświetlać top.\n			'gid'		=> [\n				1, 2\n			],	//ID grupy, której ma nie wyświetlać w topce.\n			'cldbid'	=> [\n				1, 2, 3\n			],	//Client database id użytkowników, których ma nie wyświetlać w topce np. MusicBOT czy też ten bot.\n			'limit'		=> 20	//Limit osób, które ma wyświetlać w top.\n		],\n$3", $fgc);
		echo "Dodano dwie opcje do config.php 'info' - która pozwala nam ustawić czy ma wysyłać informacje o tym, że użytkownik rozpoczął transmisję live oraz 'info_text' - która pozwala ustawić tekst wysyłany podczas odpalania transmisji";
	}else{
		echo "Coś poszło nie tak:C Nie można zaktualizować config.php";
	}
	
	file_put_contents('./includes/config.php', $fgc);

?>
