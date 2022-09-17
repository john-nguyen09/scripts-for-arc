<?php

// Copy this file and rename the new file to `config.php`

return [
    // Set up variables and enter your credentials here
    'dbname' => '',
    'dbhost' => '',
    'dbpass' => '',
    'dbuser' => '',
    // Set up your master array! Array goes in this
    'replace_array' => [
        'https://site.com.au' => 'http://site.local',
        '//site.com.au' => '//site.local',
        // And path
    ],
];
