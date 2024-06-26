<?php

use Symfony\Component\Yaml\Yaml;

require_once __DIR__ . '/../vendor/autoload.php';

$ast = Yaml::parseFile(__DIR__ . '/../../crates/pxp-ast/meta/ast.yaml');
$output = <<<'RUST'
// This file is generated by meta/scripts/generate-ast.php.
// Do not make modifications to this file directly.

use crate::utils::CommaSeparated;
use pxp_syntax::comments::{CommentGroup, Comment};
use pxp_type::Type;
use pxp_token::Token;
use pxp_span::Span;
use pxp_symbol::Symbol;
use pxp_syntax::backed_enum_type::BackedEnumType;
use pxp_syntax::name::NameQualification;


RUST;

$reserved = ['as', 'derive'];

foreach ($ast as $node => $structure) {
    if (is_string($structure)) {
        $output .= "pub type {$node} = {$structure};\n\n";
        continue;
    }

    $derive = 'Debug, PartialEq, Eq, Clone';

    if (is_array($structure) && isset($structure['derive'])) {
        $derive .= ', ' . $structure['derive'];
    }

    $output .= "#[derive({$derive})]\n";
    $enum = isset($structure['as']) && $structure['as'] === 'Enum';

    if ($enum) {
        $output .= "pub enum {$node} {\n";
    } else {
        $output .= "pub struct {$node} {\n";
    }

    if ($enum) {
        foreach ($structure as $field => $value) {
            if (in_array($field, $reserved, true)) {
                continue;
            }

            $output .= "    {$field}";

            if ($value === '') {
                $output .= ",\n";
            } elseif (is_string($value)) {
                $output .= "({$value}),\n";
            } elseif (is_array($value)) {
                $output .= " {\n";

                foreach ($value as $subfield => $subtype) {
                    $output .= "        {$subfield}: {$subtype},\n";
                }

                $output .= "    },\n";
            }
        }
    } else {
        foreach ($structure as $field => $type) {
            if (in_array($field, $reserved, true)) {
                continue;
            }

            $output .= "    pub {$field}: {$type},\n";
        }
    }

    $output .= "}\n\n";
}

file_put_contents(__DIR__ . '/../../crates/pxp-ast/src/generated.rs', $output);

echo "AST file generated.\n";