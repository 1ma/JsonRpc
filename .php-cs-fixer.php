<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);

return (new PhpCsFixer\Config())
    ->setFinder($finder)
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12' => true,
        '@PSR12:risky' => true,
    ])
    ->setUsingCache(false);
