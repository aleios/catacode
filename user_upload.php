<?php

// Pull default config into configs array.
$config = include('config.php');

/**
 * Creates a table in the database with the email, name and surname fields.
 * 
 * @return bool
 */
function createSqlTable()
{
	global $config;
	$inst = new mysqli($config->sqlHost, $config->sqlUser, $config->sqlPass, $config->sqlDatabase);
	
	if($inst->connect_error)
	{
		echo "Error: Could not connect to database: $config->sqlHost.";
		return;
	}
	
	$query = "CREATE TABLE `users` (
	name VARCHAR(50) NOT NULL,
	surname VARCHAR(50) NOT NULL,
	email VARCHAR(320) NOT NULL,
	UNIQUE (email)
	)";
	
	// Execute the query and exit on any errors.
	if($inst->query($query) === FALSE)
	{
		echo "Error: Could not execute query.\nReason: " . $inst->error . "\n";
		return;
	}
	
	// Close the database connection.
	$inst->close();
}

/**
 * This function takes a name and surname fields and trims any leading/trailing whitespace, removes captilization, capatilizes the first character of the name.
 * 
 * @param string &$name
 * @param string &$surname
 * @return void
 */
function sanitizeNameFields(&$name, &$surname)
{
	
}

/**
 * This function takes an email and trims the leading/trailing whitespace, puts all in lowercase and verifies that the email follows a valid format.
 *
 * @param string $email
 * @return string
 */
function sanitizeAndVerifyEmail($email)
{
	
}

/**
 * Takes arguments from the command line and puts them into a key-value pair array.
 *
 * @return array 
 */
function parseComandLineOptions()
{
	// Define single hypenated options
	$shortoptions = "u:";
	$shortoptions .= "p:";
	$shortoptions .= "h:";

	// Define double hypenated options
	$longoptions = array(
		"file:",
		"create_table",
		"dry_run",
		"help"
	);

	// Get the options off the command line.
	return getopt($shortoptions, $longoptions);
}

/**
 *
 *
 *
 * @return void
 */
function showHelpInformation()
{
	echo "USAGE: user_upload --file users.csv\n";
}

/**
 * 
 *
 * @return void
 */
function userUploadEntryPoint()
{
	// Parse command line arguments.
	$options = parseComandLineOptions();
	
	// Check if we are to display help information.
	if(array_key_exists('help', $options))
	{
		// Display help and stop execution.
		showHelpInformation();
		return;
	}
	
	// 
	if(array_key_exists('create_table', $options))
	{
		createSqlTable();
		return;
	}
	
	// 
	if(!array_key_exists('file', $options))
	{
		// Display missing --file error and show help information.
		showHelpInformation();
		return;
	}
	
	if(array_key_exists('dry_run', $options))
	{
		$config->dryRun = true;
	}
	
	// Parse the csv file.
	
}

userUploadEntryPoint();

?>