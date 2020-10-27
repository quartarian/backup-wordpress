<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if(!isset($argv[1]) || !file_exists($argv[1]."/wp-config.php")) {
	die("Please provide the directory of the wordpress website\n");
}
else {
	// Load Wordpress global variables from 
	// Wp-config.ph without loading entire
	// wordpress installation
	$WPDEFINES = [];
	$linesInFile = file($argv[1]."/wp-config.php");
	foreach($linesInFile as $line){
		$line = trim($line);
		if(substr($line, 0, 6) === "define"){
			//echo $line."\n";
			$pattern = '/\'[^\'"]*\'(?=(?:[^"]*"[^"]*")*[^"]*$)/i';
			preg_match_all($pattern, $line, $matches);
			if(isset($matches[0][1])){
				$WPDEFINES[trim($matches[0][0],"'")]=trim($matches[0][1],"'");
			}
		}
	}
	$pathParts = explode("/",$argv[1]);
	$homeDirName = $pathParts[2];
	echo "Creating backup for $homeDirName\n";
	echo "===========================\n";

	$filePrefix = $homeDirName."-".date("m-d-Y");

	echo "Creating DB backup\n";
	$command = "mysqldump -u ".$WPDEFINES['DB_USER']. " --password='".$WPDEFINES['DB_PASSWORD']."' ".$WPDEFINES['DB_NAME']." > ".$filePrefix.".sql";
	echo "\t".$command."\n";shell_exec($command);

	echo "Creating website archive\n";
	$command = ("tar -cvf ".$filePrefix.".tar ".$argv[1]." --exclude='".$argv[1]."/wp-content/uploads'");
	echo "\t".$command."\n";shell_exec($command);

	echo "Adding DB backup to archive\n";
	$command = "tar -rvf ".$filePrefix.".tar ".$filePrefix.".sql";
	echo "\t".$command."\n";shell_exec($command);

	//echo "Gziping archive\n";
	//$command = "gzip ".$filePrefix.".tar";
	//echo "\t".$command."\n";shell_exec($command);

	unlink($filePrefix.".sql");
}

