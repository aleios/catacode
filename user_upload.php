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

function showHelpInformation()
{
	echo "USAGE: user_upload --file users.csv\n";
	echo "";
}

// Script Entry point
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
}

userUploadEntryPoint();

?>