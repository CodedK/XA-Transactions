<?php

// First, connect to the first database
$db1 = new mysqli('localhost', 'user', 'password', 'database1');

// Then, connect to the second database
$db2 = new mysqli('localhost', 'user', 'password', 'database2');

// Start a new XA transaction
$db1->query('XA START') or die($db1->error);
$db2->query('XA START') or die($db2->error);

// Perform some operations on the first database
$result1 = $db1->query('UPDATE table1 SET column1 = value1 WHERE column2 = value2') or die($db1->error);

// Perform some operations on the second database
$result2 = $db2->query('UPDATE table2 SET column3 = value3 WHERE column4 = value4') or die($db2->error);

// Commit the transaction
$db1->query('XA COMMIT') or die($db1->error);
$db2->query('XA COMMIT') or die($db2->error);

// Close the connections
$db1->close();
$db2->close();

?>