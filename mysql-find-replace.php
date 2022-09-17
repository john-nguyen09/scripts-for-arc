<?php
$config = require __DIR__ . '/config.php';

extract($config);

//connect to database
try {
    $db = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
} catch (PDOException $ex) {
    die($ex->getMessage());
}

$sth = $db->prepare("SELECT table_name FROM information_schema.tables WHERE table_schema = ?", [
    PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY,
]);
$sth->execute([$dbname]);

$db->beginTransaction();
while ($row = $sth->fetch()) {
    $table_name = $row['table_name'];
    $sth2 = $db->prepare("SHOW COLUMNS FROM `{$table_name}`");
    $sth2->execute();
    $columns = [];

    while ($column_row = $sth2->fetch()) {
        $columns[] = $column_row['Field'];
    }

    $replace_sql = "UPDATE `{$table_name}` SET";
    $replace_params = [];
    $replace_columns_sql = [];
    foreach ($replace_array as $find => $replace) {
        foreach ($columns as $column) {
            $replace_columns_sql[] = "`{$column}` = REPLACE(`{$column}`, ?, ?)";
            $replace_params[] = $find;
            $replace_params[] = $replace;
        }
    }
    $replace_sql .= ' ' . implode(', ', $replace_columns_sql);

    print("Replacing table {$table_name}\n");
    $replace_sth = $db->prepare($replace_sql);
    $replace_sth->execute($replace_params);
}
$db->commit();
