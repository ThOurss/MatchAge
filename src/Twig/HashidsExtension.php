<?php

namespace App\Twig;

use App\Service\HashidsService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class HashidsExtension extends AbstractExtension
{
    private HashidsService $hashidsService;

    public function __construct(HashidsService $hashidsService)
    {
        $this->hashidsService = $hashidsService;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('hashid_encode', [$this->hashidsService, 'encode']),
            new TwigFunction('hashid_decode', [$this->hashidsService, 'decode']),
        ];
    }
}
