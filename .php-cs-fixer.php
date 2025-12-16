<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests');

$config = new PhpCsFixer\Config();
return $config->setRules([
    '@PER-CS' => true,
    '@PhpCsFixer' => true,
    'concat_space' => [
        'spacing' => 'one',
    ],
    'types_spaces' => false,
    'trailing_comma_in_multiline' => false,
    'php_unit_internal_class' => false,
    'php_unit_test_class_requires_covers' => false,
    // Following configuration added to make CI builds pass
    // @todo remove in #95
    'nullable_type_declaration_for_default_null_value' => false,
    'ordered_types' => false,
])->setFinder($finder);
