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

4. Set the `innodb_support_xa` variable to `ON` in the MySQL configuration file `/etc/my.cnf`:

```bash
    [mysqld]
    innodb_support_xa=ON
```

For more information on how to use XA transactions in MySQL, please see the MySQL documentation.

Test the `XA transactions` by creating a new table and starting an XA transaction:

```sql
    CREATE TABLE test (id INT);
    XA START 'trx1';
    INSERT INTO test VALUES (1);
```
