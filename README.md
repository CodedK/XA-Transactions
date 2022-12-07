# XA-Transactions

## A detailed guide on how to setup Centos 7.9 to accept XA Transactions in MySQL

To set up CentOS Linux release `7.9.2009` to accept `XA transactions` in MySQL, you will need to perform the following steps:

Install the necessary packages on your system. This will include the mysql-server package, which provides the MySQL server and command-line tools, as well as the `libdbi-devel` package, which provides the development files for the `DBI (Database Independent) API`.</br>
You can install these packages using the following command:

To check if mysql-server libdbi-devel is installed in CentOS Linux `7.9.2009`, use the yum command:

```bash
yum list installed | grep mysql-server libdbi-devel
```

```bash
sudo yum install mysql-server libdbi-devel
```

Once the packages are installed, start the MySQL server by running the following command:

```bash
sudo systemctl start mysqld
```

Next, you will need to create a user account that has the necessary privileges to create and manage `XA transactions`.
You can do this by running the following commands:

```bash
mysql -u root -p
```

This will open the MySQL command-line interface. From here, you can run the following commands to create a new user account and grant it the necessary privileges:

```sql
CREATE USER 'xa_user'@'localhost' IDENTIFIED BY 'password';
GRANT CREATE SESSION, CREATE PROCEDURE, CREATE XA TRANSACTION ON *.* TO 'xa_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# GRANT XA_RECOVER_ADMIN ON *.* TO 'xauser'@'localhost' IDENTIFIED BY 'password';
```

Once the user is created and the necessary privileges are granted, you can connect to the MySQL server using the `xa_user` account
and begin creating and managing XA transactions. You can do this by running the following command:

```bash
    mysql -u xa_user -p
```

This will open the MySQL command-line interface, where you can run the necessary commands to create and manage XA transactions.
For more information on how to use XA transactions in MySQL, please see the MySQL documentation.

Set the `innodb_support_xa` variable to `ON` in the MySQL configuration file `/etc/my.cnf`:

```bash
[mysqld]
innodb_support_xa=ON
```

Test the XA transactions by creating a new table and starting an XA transaction:

```sql
CREATE TABLE test (id INT);
XA START 'trx1';
INSERT INTO test VALUES (1);
```
