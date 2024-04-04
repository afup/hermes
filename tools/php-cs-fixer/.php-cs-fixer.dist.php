<?php

declare(strict_types=1);

if (!file_exists(__DIR__ . '/../../app/src')) {
    exit(0);
}

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        'simplified_null_return' => false,
        'concat_space' => ['spacing' => 'one'],
        'phpdoc_summary' => false,
        'linebreak_after_opening_tag' => true,
        'ordered_imports' => true,
        'phpdoc_order' => true,
        'array_syntax' => ['syntax' => 'short'],
        'declare_strict_types' => true,
        'no_superfluous_phpdoc_tags' => true,
        'phpdoc_types_order' => ['null_adjustment' => 'none', 'sort_algorithm' => 'none'],
        'single_line_throw' => false,
        'phpdoc_to_comment' => false,
        'no_extra_blank_lines' => ['tokens' => [
            // All except 'case' due to enum compatibility rule
            'break',
            'continue',
            'curly_brace_block',
            'default',
            'extra',
            'parenthesis_brace_block',
            'return',
            'square_brace_block',
            'switch',
            'throw',
            'use',
        ]],
    ])
    ->setRiskyAllowed(true)
    ->setFinder(
        (new PhpCsFixer\Finder())
            ->in(sprintf('%s/../../app/src', __DIR__))
            ->in(sprintf('%s/../../app/tests', __DIR__))
    )
    ->setCacheFile('.php-cs-fixer.cache')
    ;
