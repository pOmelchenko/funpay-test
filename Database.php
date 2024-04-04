<?php

namespace FpDbTest;

use Exception;
use mysqli;

class Database implements DatabaseInterface
{
    private mysqli $mysqli;

    private int $actualBlock = 0;
    private array $blocks = [];

    public function __construct(mysqli $mysqli)
    {
        $this->mysqli = $mysqli;
    }

    public function buildQuery(string $query, array $args = []): string
    {
        if ([] === $args) {
            return $query;
        }

        $newQuery = '';
        $inBlock = false;

        for ($i = 0, $iMax = strlen($query); $i < $iMax; $i++) {
            $chunk = '';
            if ('{' === $query[$i]) {
                $inBlock = true;
                $this->blocks[$this->actualBlock]['chunk'] = '';
                continue;
            }

            if ('}' === $query[$i]) {
                $inBlock = false;



                if (str_contains($this->blocks[$this->actualBlock]['chunk'], 'block = 1')) {
                    $newQuery .= $this->blocks[$this->actualBlock]['chunk'];
                }

                $this->actualBlock++;
                continue;
            }

            if ('?' === $query[$i]) {
                $chunk .= match ($query[$i + 1]) {
                    'd' => (int) array_shift($args),
                    'f' => (float) array_shift($args),
                    'a' => $this->buildArray(array_shift($args)),
                    '#' => "`" . implode("`, `", (array) array_shift($args)) . "`",
                    ' ' => "'" . array_shift($args) . "'",
                    default => $query[$i],
                };
                ' ' !== $query[$i + 1] && $i++;
            } else {
                $chunk .= $query[$i];
            }

            if ($inBlock) {
                $this->blocks[$this->actualBlock]['chunk'] .= $chunk;
            } else {
                $newQuery .= $chunk;
            }
        }

        return $newQuery;
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
            'NULL' => 'NULL',
            default => $arg,
        };
    }

    public function skip()
    {
        return false;
    }
}
