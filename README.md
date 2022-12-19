# XA-Transactions

## A detailed guide on how to setup XA Transactions in MySQL (Centos 7.9)

To set up CentOS Linux release `7.9.2009` to accept `XA transactions` in MySQL, you will need to perform the following steps:

Install the necessary packages on your system. This will include the mysql-server package, which provides the MySQL server and command-line tools, as well as the `libdbi-devel` package, which provides the development files for the `DBI (Database Independent) API`.</br>
You can install these packages using the following command:

### Prerequisites

* To use XA transactions in MySQL, the database engine used by the tables involved in the transaction must support XA transactions.
    In MySQL, only the `InnoDB engine` supports `XA transactions`. </br>
    Therefore, to use XA transactions, you must convert the tables involved in the transaction to the `InnoDB engine`.

### Steps

1. To check if `mysql-server libdbi-devel` is installed in CentOS Linux `7.9.2009`, use the yum command:

```bash
    yum list installed | grep mysql-server libdbi-devel
```

1. If the packages are not installed, install them using the following command:

```bash
    sudo yum install mysql-server libdbi-devel
```

1. Once the packages are installed, start the MySQL server and create a user account that has the necessary privileges to create and manage `XA transactions`:

```bash
    sudo systemctl start mysqld
```

```sql
    mysql -u root -p
    # From the MySQL command-line interface create a new user account and grant necessary privileges:
    CREATE USER 'xa_user'@'localhost' IDENTIFIED BY 'password';
    -- GRANT CREATE SESSION, CREATE PROCEDURE, CREATE XA TRANSACTION ON *.* TO 'xa_user'@'localhost';
    GRANT ALL PRIVILEGES ON *.* TO 'xa_user'@'localhost';
    FLUSH PRIVILEGES;
    EXIT;
```

4. Set the `innodb_support_xa` variable to `YES` in the MySQL configuration file `/etc/my.cnf`:

```bash
    [mysqld]
    innodb_support_xa=ON
```

If we get an error message saying that innodb_support_xa is an `unknown variable`, it is possible that our MySQL server does not support this variable.

`innodb_support_xa` is deprecated; expect it to be removed in a future MySQL release. InnoDB support for two-phase commit in XA transactions is always enabled as of `MySQL 5.7.10`. Disabling `innodb_support_xa` is no longer permitted as it makes replication unsafe and prevents performance gains associated with binary log group commit.

Test the `XA transactions` by creating a new table and starting an XA transaction (stupid basic example follows):

```sql
    CREATE TABLE test (id INT);
    XA START 'trx1';
    INSERT INTO test VALUES (1);
    UPDATE test SET id = 2 WHERE id = 1;
    XA END 'trx1';
```

## XA Transaction Statements

| XA Transaction SQL Statement | Explanation |
|------------------------------|-------------|
| `XA START`                   | Begins a new XA transaction with the specified transaction ID. |
| `XA END`                     | Ends the current XA transaction and prepares it for committing or rolling back. |
| `XA PREPARE`                 | Prepares the current XA transaction for committing or rolling back. |
| `XA COMMIT`                  | Commits the current XA transaction. |
| `XA ROLLBACK`                | Rolls back the current XA transaction. |
| `XA RECOVER`                 | Retrieves a list of prepared XA transactions that are currently in progress. |

In general, we should use the `XA START`, `XA END`, `XA COMMIT`, and `XA ROLLBACK` statements as the main commands
for working with `XA transactions`.

The `XA PREPARE` and `XA RECOVER` commands may be used in certain situations,
but are not typically used as frequently as the other commands.

> XA transactions can be used with a single database, as well as multiple databases.

## Πως διαχειριζόμαστε τα Errors

Αν κάποιο από τα SQL statements που εκτελούνται αποτύχει, τότε επαναφέρουμε τη συναλλαγή χρησιμοποιώντας μπλοκς TRY-CATCH.

Π.χ.:

```php
try {
  // Start the XA transaction
  $db->query("XA START 'transaction_id'");

  // Execute the SQL statements that are part of the transaction
  $db->query("UPDATE Table1 SET Column1 = 'Value1' WHERE Column2 = 'Value2'");
  $db->query("UPDATE Table2 SET Column3 = 'Value3' WHERE Column4 = 'Value4'");

  // End the XA transaction and prepare it for committing or rolling back
  $db->query("XA END 'transaction_id'");

  // Commit the transaction
  $db->query("XA COMMIT 'transaction_id'");
} catch (Exception $e) {
  // An error occurred, so roll back the transaction
  $db->query("XA ROLLBACK 'transaction_id'");
}
```
