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
	// eg. -u
	$shortoptions = "u:";
	$shortoptions .= "p:";
	$shortoptions .= "h:";
	$shortoptions .= "d:";

	// Define double hypenated options
	// eg. --file
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
 * Shows the help information for the user upon request or switch error.
 *
 * @return void
 */
function showHelpInformation()
{
	echo "USAGE: user_upload --file users.csv\n";
	echo "--file [csv filename] - Name of the file to be parsed.\n";
	echo "--create_table - Builds the table in the database.\n";
	echo "--dry_run - Will execute all relevant functions but will not modify or store results in the database.\n";
	echo "-u [MySQL username]\n";
	echo "-p [MySQL password]\n";
	echo "-h [MySQL host]\n";
	echo "-d [MySQL database]\n";
	echo "--help - Shows this help text.\n";
}

/**
 * Takes each row within the specifed array and sets the key to the respective header column.
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
 * Parses the given csv file and inserts the data into the database.
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
	
	if(!in_array('name', $header) || !in_array('surname', $header) || !in_array('email', $header))
	{
		echo "Error: Malformed header.\n";
		return;
	}
	
	// Go through each row in the array and attach header as key to values.
	array_walk($csvarr, 'attachHeaderCallback', $header);
	
	// Try connect to the database when not a dry run.
	$inst = null;
	if(!$config->dryRun)
	{
		$inst = new mysqli($config->sqlHost, $config->sqlUser, $config->sqlPass, $config->sqlDatabase);
		
		if($inst->connect_error)
		{
			echo "Error: Could not connect to database: $config->sqlHost.";
			return;
		}
	}
	
	// Update the database with the data from the csv (unless dry run).
	$totalsuccess = 0;
	foreach($csvarr as $user)
	{
		$name = sanitizeNameField($user['name']);
		$surname = sanitizeNameField($user['surname']);
		$email = $user['email'];
		
		// Test if fields are empty.
		if(empty($name) || empty($surname) || empty($email))
		{
			echo "Error: Missing fields for entry. Skipping.\nName: $name\nSurname: $surname\nEmail: $email\n";
			continue;
		}
		
		// Check if email is valid. If not valid then skip and report error.
		if(!sanitizeAndVerifyEmail($email))
		{
			echo "Error: Invalid email for '$email'. Skipping...\n";
			continue;
		}
		
		// Insert to database.
		$success = true;
		$stmterror = "";
		if(!$config->dryRun)
		{
			$query = "INSERT INTO `users` (name, surname, email) VALUES (?, ?, ?)";
			
			$stmt = $inst->prepare($query);
			
			if(!$stmt)
			{
				$success = false;
				$stmterror = $inst->error;
			}
			else
			{
				$stmt->bind_param('sss', $name, $surname, $email);
				
				$success = $stmt->execute();
				$stmterror = $stmt->error;
				
				$stmt->close();
			}
		}
		
		// Test for a successful database insertion. On --dry_run test always succeeds.
		if(!$success)
		{
			echo "Error: Failed to insert $email into the database.\nReason: " . $stmterror . "\n";
		}
		else
		{	
			echo "Inserted $name $surname | $email into database.\n";
			$totalsuccess++;
		}
	}
	
	// Display the total succeeded versus the total amount of insertable rows.
	echo "Total: $totalsuccess / " . count($csvarr) . " successful.\n";
}

/**
 * Entry point for the script.
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
	
	// Creates the table in the mysql database. When this is present no other functions are run.
	if(array_key_exists('create_table', $options))
	{
		createSqlTable();
		return;
	}
	
	// Check for a --file command present.
	if(!array_key_exists('file', $options))
	{
		// Display missing --file error and show help information.
		echo "Error: No input file specified. Please specify with --file [filename]\n";
		showHelpInformation();
		return;
	}
	
	// Initiate a dry run if --dry_run present.
	if(array_key_exists('dry_run', $options))
	{
		$config->dryRun = true;
	}
	
	// Parse the csv file.
	parseCsv($options['file']);
	
}

// Call the entry point to the script functions.
userUploadEntryPoint();

?>