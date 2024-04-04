<?php

function buildQuery(string $query, array $args = []): string
{
    if ([] === $args) return $query;

    $res = '';

    for ($i = 0; $i < strlen($query); $i++) {
        if ($query[$i] === '?') {
            $res .= match ($query[$i + 1]) {
                'd' => (int) array_shift($args),
                'f' => (float) array_shift($args),
                'a' => buildArray(array_shift($args)),
                '#' => "`" . implode("`, `", (array) array_shift($args)) . "`",
                ' ' => "'" . array_shift($args) . "'",
                default => $query[$i],
            };
            $query[$i + 1] !== ' ' && $i++;
        } else {
            $res .= $query[$i];
        }

    }

    return $res;
}

function buildArray($args) {
    if (!array_is_list($args)) {
        $args = array_map(
            fn ($k, $v) => "`$k` = ".getval($v),
            array_keys($args),
            array_values($args)
        );
    }
    return implode(", ", $args);
}

function getval($arg):string {
    return match (gettype($arg)) {
        'string' => "'$arg'",
        'NULL'=> 'NULL',
        default => $arg,
    };
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

echo PHP_EOL;

echo buildQuery(
    'UPDATE users SET ?a WHERE user_id = -1',
    [['name' => 'Jack', 'email' => null]]
);

//  UPDATE users SET `name`='Jack',`email`= NULL WHERE user_id = -1
//  UPDATE users SET `name` = 'Jack',`email` =  NULL WHERE user_id = -1
//  UPDATE users SET `name` = 'Jack', `email` =  NULL WHERE user_id = -1
//  UPDATE users SET `name` = 'Jack', `email` = NULL WHERE user_id = -1
// 'UPDATE users SET `name` = 'Jack', `email` = NULL WHERE user_id = -1',
