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
 * This function takes a name or surname field and trims any leading/trailing whitespace, removes captilization, capatilizes the first character of the name.
 * 
 * @param string $name
 * @return string
 */
function sanitizeNameField($name)
{
	$tmpname = $name;
	$tmpname = trim($tmpname, ' -\'');
	$tmpname = strtolower($tmpname);
	$tmpname = ucfirst($tmpname);
	
	// Remove invalid symbols. Use a regular expression to match invalid symbols. ' and - are special characters.
	$tmpname = preg_replace("/[^A-Za-z'-]/", "", $tmpname);
	
	return $tmpname;
}

/**
 * This function takes an email and trims the leading/trailing whitespace, puts all in lowercase and verifies that the email follows a valid format.
 *
 * @param string &$email
 * @return boolean
 */
function sanitizeAndVerifyEmail(&$email)
{
	$tmpemail = $email;
	$tmpemail = trim($tmpemail);
	$tmpemail = strtolower($tmpemail);
	
	// Test if this is a valid email.
	if(filter_var($tmpemail, FILTER_VALIDATE_EMAIL))
	{
		$email = $tmpemail;
		return true;
	}
	return false;
}

/**
 * Takes arguments from the command line and puts them into a key-value pair array.
 *
 * @return array 
 */
function parseCommandLineOptions()
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
 * @param string &$row Reference to array row.
 * @param string $key Array key
 * @param string $header Header value.
 * @return void
 */
function attachHeaderCallback(&$row, $key, $header) 
{
	$row = array_combine($header, $row);
}

/**
 * 
 *
 * @param string $filename 
 * @return void
 */
function parseCsv($filename)
{
	global $config;
	
	// Check if file exists
	if(!is_readable($filename))
	{
		echo "Error: File doesn't exist or is not readable.\n";
		return;
	}
	
	// Try to open the file.
	$csvfile = file($filename);
	if($csvfile === FALSE)
	{
		echo "Error: Failed to open file.\n";
		return;
	}
	
	// Parse CSV to array.
	$csvarr = array_map('str_getcsv', $csvfile);

	
	// Pop the header row of the csv off the top of the array.
	$header = array_shift($csvarr);
	
	// Sanitize header.
	$headercount = count($header);
	for($i = 0; $i < $headercount; $i++)
	{
		$tmp = $header[$i];
		$tmp = trim($tmp);
		$tmp = strtolower($tmp);
		$header[$i] = $tmp;
	}
	
	// Go through each row in the array and attach header as key to values.
	array_walk($csvarr, 'attachHeaderCallback', $header);
	
	// Update the database with the data from the csv (unless dry run).
	
}

/**
 * 
 *
 * @return void
 */
function userUploadEntryPoint()
{
	global $config;
	
	// Parse command line arguments.
	$options = parseCommandLineOptions();
	
	// Check if we are to display help information.
	if(array_key_exists('help', $options))
	{
		// Display help and stop execution.
		showHelpInformation();
		return;
	}
	
	// Mysql details.
	if(array_key_exists('u', $options))
	{
		$config->sqlUser = $options['u'];
	}
	
	if(array_key_exists('p', $options))
	{
		$config->sqlPass = $options['p'];
	}
	
	if(array_key_exists('h', $options))
	{
		$config->sqlHost = $options['h'];
	}
	
	if(array_key_exists('d', $options))
	{
		$config->sqlDatabase = $options['d'];
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
	parseCsv($options['file']);
	
}

userUploadEntryPoint();

?>