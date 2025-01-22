<?php

namespace App\Service;

use Hashids\Hashids;

class HashidsService
{
    private $hashids;

    public function __construct(int $minLength = 10)
    {
        $this->hashids = new Hashids('', $minLength); // Longueur minimale de 10 caractères
    }

    public function encode(int $id): string
    {
        return $this->hashids->encode($id);
    }

    public function decode(string $hash): ?int
    {
        $decoded = $this->hashids->decode($hash);
        return $decoded[0] ?? null;
    }
}