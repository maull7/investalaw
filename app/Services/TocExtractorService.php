<?php

namespace App\Services;

class TocExtractorService
{
    public function __construct(
        private readonly DocumentParser $documentParser
    ) {}
}
