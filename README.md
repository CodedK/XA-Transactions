# XA-Transactions

## Guide to Setting Up XA Transactions in MySQL on CentOS 7.9

This document outlines the steps to configure CentOS 7.9 to support XA transactions in MySQL. These steps are essential for utilizing transactional features provided by the InnoDB engine in MySQL.

### Prerequisites

* Ensure that the tables involved support XA transactions; this is only possible with the InnoDB engine in MySQL.

### Steps

1. To check if `mysql-server libdbi-devel` is installed in CentOS Linux `7.9.2009`, use the yum command:

```bash
    yum list installed | grep mysql-server libdbi-devel
```

2. If not present, install them using:

```bash
    sudo yum install mysql-server libdbi-devel
```

### Configuration

1. Start the MySQL server:

```bash
    sudo systemctl start mysqld
```
2. Set up a user with appropriate permissions for managing XA transactions:
sql

```sql
    mysql -u root -p

    # From the MySQL command-line interface create a new user account and grant necessary privileges:
    CREATE USER 'xa_user'@'localhost' IDENTIFIED BY 'password';

    -- GRANT CREATE SESSION, CREATE PROCEDURE, CREATE XA TRANSACTION ON *.* TO 'xa_user'@'localhost';
    GRANT ALL PRIVILEGES ON *.* TO 'xa_user'@'localhost';
    FLUSH PRIVILEGES;
    EXIT;
```

3. Ensure `innodb_support_xa` is enabled. Set the `innodb_support_xa` variable to `YES` in the MySQL configuration file `/etc/my.cnf`. (Note: As of MySQL 5.7.10, this is always on and cannot be disabled)

```bash
    [mysqld]
    innodb_support_xa=ON
```
If `innodb_support_xa` shows as an unknown variable, your MySQL version might not need this setting as it's deprecated.

InnoDB support for two-phase commit in XA transactions is always enabled as of `MySQL 5.7.10`. Disabling `innodb_support_xa` is no longer permitted as it makes replication unsafe and prevents performance gains associated with binary log group commit.

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


```php
// Start the XA transaction
$result = $db->query("XA START 'transaction_id'");

if (!$result) {
  // An error occurred, so roll back the transaction
  $db->query("XA ROLLBACK 'transaction_id'");
  // Handle the error
  handleError($db->errno, $db->error);
}

// Execute the SQL statements that are part of the transaction
$result = $db->query("UPDATE Table1 SET Column1 = 'Value1' WHERE Column2 = 'Value2'");

if (!$result) {
  // An error occurred, so roll back the transaction
  $db->query("XA ROLLBACK 'transaction_id'");
  // Handle the error
  handleError($db->errno, $db->error);
}

$result = $db->query("UPDATE Table2 SET Column3 = 'Value3' WHERE Column4 = 'Value4'");

if (!$result) {
  // An error occurred, so roll back the transaction
  $db->query("XA ROLLBACK 'transaction_id'");
  // Handle the error
  handleError($db->errno, $db->error);
}

// End the XA transaction and prepare it for committing or rolling back
$result = $db->query("XA END 'transaction_id'");

if (!$result) {
  // An error occurred, so roll back the transaction
  $db->query("XA ROLLBACK 'transaction_id'");
  // Handle the error
  handleError($db->errno, $db->error);
}

// Commit the transaction
$result = $db->query("XA COMMIT 'transaction_id'");

if (!$result) {
  // An error occurred, so roll back the transaction
  $db->query("XA ROLLBACK 'transaction_id'");
  // Handle the error
  handleError($db->errno, $db->error);
}
```
