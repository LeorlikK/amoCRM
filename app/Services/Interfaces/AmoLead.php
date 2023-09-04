<?php

namespace App\Services\Interfaces;

interface AmoLead
{
    public function getLeads(): array;

    public function saveLeads(array $arrayLeads): void;
}
