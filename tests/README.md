# Action Scheduler tests

To run unit tests:

1. Make sure that PHPUnit is installed by running:
    ```
    $ composer install
    ```

2. Install WordPress and the WP Unit Test lib using the `install.sh` script:
    ```
    $ tests/bin/install.sh <db-name> <db-user> <db-password> [db-host] [wp-version] [skip-database-creation]
    ```

You may need to quote strings with backslashes to prevent them from being processed by the shell or other programs.

Then, to run the tests:
    ```
    $ composer run test
    ```
