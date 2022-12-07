<?php
    // Connect to the first database using the xa_user user
    $db1 = new mysqli("localhost", "xa_user", "password", "database1");

    // Connect to the second database using the xa_user user
    $db2 = new mysqli("localhost", "xa_user", "password", "database2");

    // Start an XA transaction
    $db1->query("XA START 'trx1'");

    // Execute a query on the first database
    $db1->query("INSERT INTO table1 VALUES (1)");

    // Execute a query on the second database
    $db2->query("INSERT INTO table2 VALUES (1)");

    // Commit the XA transaction
    $db1->query("XA END 'trx1'");
    $db1->query("XA PREPARE 'trx1'");
    $db1->query("XA COMMIT 'trx1'");

    // Close the connections to the databases
    $db1->close();
    $db2->close();

?>