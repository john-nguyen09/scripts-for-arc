<?php

//set up variables and enter your credentials here
$dbname = '';
$dbhost = 'localhost:9090';
$dbpass = '';
$dbuser = 'root';

//set up your master array! Array goes in this or
$replace_array = [
    'https://site.com.au' => 'http://site.local',
    '//site.com.au' => '//site.local',
    // And path
];

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

    $replace_sql = "UPDATE `{$table_name}` SET";
    $replace_params = [];
    $replace_columns_sql = [];
    foreach ($replace_array as $find => $replace) {
        while ($column_row = $sth2->fetch()) {
            $column = $column_row['Field'];

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
