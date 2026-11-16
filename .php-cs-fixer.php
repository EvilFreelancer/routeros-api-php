<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . DIRECTORY_SEPARATOR . 'tests')
    ->in(__DIR__ . DIRECTORY_SEPARATOR . 'src')
    ->append([__FILE__]);

$rules = [
    '@Symfony'                   => true,
    'phpdoc_no_empty_return'     => false,
    'phpdoc_summary'             => false,
    'no_superfluous_phpdoc_tags' => false,
    'phpdoc_separation'          => false,
    'phpdoc_trim'                => false,
    'phpdoc_align'               => false,
    'array_syntax'               => ['syntax' => 'short'],
    'yoda_style'                 => true,
    'binary_operator_spaces'     => false,
    'concat_space'               => ['spacing' => 'one'],
    'not_operator_with_space'    => false,
    'phpdoc_no_package'          => false,
    'single_trait_insert_per_statement' => false,
    'fully_qualified_strict_types' => false,
    'ordered_imports'            => false,
    'no_extra_blank_lines'       => false,
    'blank_line_before_statement' => false,
];

$rules['increment_style'] = ['style' => 'post'];

return (new PhpCsFixer\Config())
    ->setUsingCache(true)
    ->setRules($rules)
    ->setFinder($finder);
