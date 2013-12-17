# PHP-Based Command Line Interface Logger

This is a system for running scripts through a PHP wrapper on the command line of a Linux-based system (it might work on Windows, but... well, I don't do Windows; if you wanna help, let me know).  Go to the project website to learn more, the page is actually quite informative: [ http://www.crazedsanity.com/projects/cs-clilogger ].

Basically, it logs information about the script so it can be viewed from a webpage, instead of looking through logs on a server.  There's some work to be done on the interface (you know, like *making* one).

# SETUP

## Foreword

This may not be a complete reference, as this program is still in development.

## Limitations

 * the database MUST be PostgreSQL
 * the database name is hard-coded ("cli_logger")
 * the database host MUST be local
 * the password is hard-coded (must be manually changed)

## Setup Instructions

 1.) connect to local PostgreSQL database as a superuser (e.g. postgres)
```
psql -U postgres
```

 2.) create CLI user:
```
postgres=> CREATE USER cli;
```

 3.) Create database:
```
postgres=> CREATE DATABASE cli_logger WITH OWNER cli;
```

 4.) Reconnect as the new "cli" user

 5.) Build the database.
```
postgres=>\c cli_logger
cli_logger=> \i docs/schema.sql
```

 6.) Perform a test run
```
/usr/bin/php script_wrapper.php "" /bin/false
```

7.) check that the inserted records seem correct (check all tables in database)

# License
Copyright (c) 2013 "crazedsanity" Dan Falconer
Dual licensed under the MIT and GPL Licenses.

