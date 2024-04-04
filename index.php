<?php

function buildQuery(string $query, array $args = []): string
{
    if ([] === $args) return $query;

    $res = '';

    for ($i = 0; $i < strlen($query); $i++) {
        if ($query[$i] === '?') {
            $res .= match ([$query[$i], $query[$i + 1]]) {
                ['?', 'd'] => (int) array_shift($args),
                ['?', 'f'] => (float) array_shift($args),
                ['?', 'a'] => '',
                ['?', '#'] => "'" . implode("', '", (array) array_shift($args)) . "'",
                ['?', ' '] => "'" . array_shift($args) . "'",
                default => $query[$i],
            };
            if ($query[$i + 1] !== ' ') $i++;
        } else {
            $res .= $query[$i];
        }

    }

    return $res;
}


echo buildQuery('SELECT name FROM users WHERE user_id = 1');

echo PHP_EOL;

echo buildQuery(
    'SELECT * FROM users WHERE name = ? AND block = 0',
    ['Jack']
);

echo PHP_EOL;

echo buildQuery(
    'SELECT ?# FROM users WHERE user_id = ?d AND block = ?d',
    [['name', 'email'], 2, true]
);
