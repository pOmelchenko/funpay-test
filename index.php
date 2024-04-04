<?php

function buildQuery(string $query, array $args = []): string
{
    $res = '';

    for ($i = 0; $i < strlen($query); $i++) {
        $res .= match ($query[$i]) {
            '?' => "'" . array_pop($args) . "'",
            default => $query[$i],
        };
    }

    return $res;
}


echo buildQuery(
    'SELECT * FROM users WHERE name = ? AND block = 0',
    ['Jack']
);
