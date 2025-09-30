<?php
$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude(['vendor','tests','storage','.github']);

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],
        'no_unused_imports' => true,
        'no_trailing_whitespace' => true,
        'single_quote' => true,
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
    ])
    ->setFinder($finder);
