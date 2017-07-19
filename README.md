# IT tests collection

## User Upload

Requires a Mysql/MariaDB database to be available. Using `--create_table` will generate a table with fields:
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
- -d [MySQL database]
- --help - Shows the application help text.

### Deliverable Assumptions

- It has been assumed that with a lack of a --file parameter that the application will terminate after displaying the help text.
- With the lack of any of the MySQL parameters it will be assumed that the following defaults will be used per missing parameter:
  - Username: root
  - Password: [no value]
  - Host: localhost
  - Database: catadb
- To accompany the default database a -d option has been included to select the database from the command line.

## Foobar Test

PHP file contains the required code to:
- Output numbers 1 to 100
- When the number is divisible by 3 it will output the word "foo"
- When it is divisible by 5 it will output "bar"
- When divisible by both 3 and 5 it will output "foobar"