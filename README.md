# IT tests collection

## CSV Processor

Requires a Mysql/MariaDB database to be available with a table `users` containing the following columns:
- email
- name
- surname

The following commands are available:
- --file [csv filename] - Name of the file to be parsed.
- --create_table - Builds the table in the database .
- --dry_run - Will execute all relevant functions but will not modify or store results in the database.
- -u [MySQL username]
- -p [MySQL password]
- -h [MySQL host]
- --help - Shows the application help text.

## Foobar Test

PHP file contains the required code to:
- Output numbers 1 to 100
- When the number is divisible by 3 it will output the word "foo"
- When it is divisible by 5 it will output "bar"
- When divisible by both 3 and 5 it will output "foobar"