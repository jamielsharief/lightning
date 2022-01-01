<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude('somedir')
    ->notPath('src/Symfony/Component/Translation/Tests/fixtures/resources.php')
    ->in(__DIR__);

$config = new PhpCsFixer\Config();

return $config->setRules([
    '@PSR12' => true,
    'array_indentation' => true,
    'array_syntax' => ['syntax' => 'short'],
    'binary_operator_spaces' => true,
    'blank_line_after_opening_tag' => false,
    'blank_line_after_namespace' => true,
    'blank_line_before_statement' => true,
    'braces' => true,
    'class_definition' => true,
    'method_argument_space' => true,
    'method_chaining_indentation' => true,
    'no_extra_blank_lines' => true,
    'method_argument_space' => [
        'on_multiline' => 'ignore'
    ],
    'multiline_whitespace_before_semicolons' => true,
    'echo_tag_syntax' => false,
    'no_spaces_around_offset' => true,
    'no_unused_imports' => true,
    'no_whitespace_before_comma_in_array' => true,
    'not_operator_with_successor_space' => true,
    'ordered_imports' => ['sort_algorithm' => 'length'],
    'return_type_declaration' => ['space_before' => 'none'],
    'single_quote' => true,
    'trailing_comma_in_multiline' => false,
    'trim_array_spaces' => true
])
    ->setFinder($finder);
