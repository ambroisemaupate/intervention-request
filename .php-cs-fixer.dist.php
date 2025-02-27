<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude([
        'var',
        'docker',
        'config',
        'vendor',
        '.data',
        '.github',
    ])
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        'blank_line_after_opening_tag' => true,
        'declare_strict_types' => true,
    ])
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setRiskyAllowed(true)
    ->setFinder($finder)
;
