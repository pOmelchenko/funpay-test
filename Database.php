<?php

namespace FpDbTest;

use Exception;
use mysqli;

class Database implements DatabaseInterface
{
    private mysqli $mysqli;

    public function __construct(mysqli $mysqli)
    {
        $this->mysqli = $mysqli;
    }

    public function buildQuery(string $query, array $args = []): string
    {
        if ([] === $args) return $query;

        $res = '';

        for ($i = 0; $i < strlen($query); $i++) {
            if ($query[$i] === '?') {
                $res .= match ($query[$i + 1]) {
                    'd' => (int) array_shift($args),
                    'f' => (float) array_shift($args),
                    'a' => $this->buildArray(array_shift($args)),
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

    private function buildArray($args): string
    {
        if (!array_is_list($args)) {
            $args = array_map(
                fn ($k, $v) => "`$k` = " . $this->getVal($v),
                array_keys($args),
                array_values($args)
            );
        }
        return implode(", ", $args);
    }

    private function getVal($arg): string
    {
        return match (gettype($arg)) {
            'string' => "'$arg'",
            'NULL'=> 'NULL',
            default => $arg,
        };
    }

    public function skip()
    {
        throw new Exception();
    }
}
