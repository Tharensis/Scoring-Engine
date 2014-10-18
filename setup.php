#!/usr/bin/php

<?

	// Add system commands here that we need to check
	$commands = array('dig', 'pwgen');

	foreach ($commands as &$command) {
		exec($command . ' --help 2>&1',$output,$status);
		if ($status == '127') {
			print "You are missing the following dependency: $command\n";
			exit;
		}
	}

	// Check for php modules we need
	$phpmods = array('mysql');

	foreach ($phpmods as &$module) {
		if (!extension_loaded("$module")) {
			print "You are missing the following PHP module: $module\n";
			exit;
		}
	}

	// Check for python modules we need
	$pymods = array('paramiko');

	foreach ($pymods as &$module) {
		$command = "python -c 'import $module' 2>&1";
		exec($command,$output,$status);
		if ($status == '1') {
			print "You are missing the following Python module: $module\n";
			exit;
		}
	}

	// We have everything we need. Start doing some work.
	require('class/ccdc.class.php');

	$con = ccdc::pconnect();

	// Create the database if it doesn't already exist
	if ( mysql_query('CREATE DATABASE IF NOT EXISTS ccdc', $con) )
		{ print "Database created...\n"; } 
	else
		{ die( 'Error creating database: ' . mysql_error() ); }

	mysql_select_db('ccdc');

	// Drop old tables if present
	try {
		mysql_query('DROP TABLE IF EXISTS services', $con);
		mysql_query('DROP TABLE IF EXISTS teams', $con);
	} catch (Exception $e) {
		print "Could not drop tables: $e->getMessage()\n";
	}

	// Create new tables
	try {
		mysql_query("CREATE TABLE services ( name varchar(10) NOT NULL, teamid int(10) NOT NULL, attempts INT(10) DEFAULT 0, success INT(10) DEFAULT 0, lastcheck INT(1) DEFAULT 0, active INT(1) DEFAULT 0, useauth INT(1) DEFAULT 0, host VARCHAR(30) NOT NULL, user VARCHAR(25), pass VARCHAR(25), poller VARCHAR(25) NOT NULL )");
		mysql_query("CREATE TABLE teams ( id INT(10) NOT NULL AUTO_INCREMENT, name VARCHAR(25) NOT NULL, PRIMARY KEY(id) )");
	} catch (Exception $e) {
		print "Could not create tables: $e->getMessage()\n";
	}

	print "Tables added...\n";



	// Comment out if you don't want dump imported:


	$filename = "class/dump.sql";
	// Temporary variable, used to store current query
	$templine = '';
	// Read in entire file
	$lines = file($filename);
	// Loop through each line
	foreach ($lines as $line)
	{
	// Skip it if it's a comment
	if (substr($line, 0, 2) == '--' || $line == '')
	    continue;

	// Add this line to the current segment
	$templine .= $line;
	// If it has a semicolon at the end, it's the end of the query
	if (substr(trim($line), -1, 1) == ';')
	{
	    // Perform the query
	    mysql_query($templine) or print('Error performing query \'<strong>' . $templine . '\': ' . mysql_error() . '<br /><br />');
	    // Reset temp variable to empty
	    $templine = '';
	}
	}
	 echo "dump imported successfully";



	ccdc::dbclose($con);
?>
