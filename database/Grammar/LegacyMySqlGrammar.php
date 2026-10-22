<?php

namespace App\Database\Grammar;

use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Grammars\MySqlGrammar;

class LegacyMySqlGrammar extends MySqlGrammar
{
    public function __construct(?Connection $connection = null)
    {
        if ($connection) {
            parent::__construct($connection);
        }
    }

    public function compileColumns($schema, $table)
    {
        return sprintf(
            'select column_name as `name`, data_type as `type_name`, column_type as `type`, '
            . 'collation_name as `collation`, is_nullable as `nullable`, '
            . 'column_default as `default`, column_comment as `comment`, '
            . '\'\' as `expression`, extra as `extra` '
            . 'from information_schema.columns where table_schema = %s and table_name = %s '
            . 'order by ordinal_position asc',
            $schema ? $this->quoteString($schema) : 'schema()',
            $this->quoteString($table)
        );
    }
}