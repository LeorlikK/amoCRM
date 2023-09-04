<?php

namespace App\Services;

use App\Services\Interfaces\AmoLead;
use Illuminate\Support\Facades\File;

class AmoLeadService implements AmoLead
{
    public function getLeads(): array
    {
        $filePath = storage_path("amo_crm/deals/deal.json");
        return json_decode(File::get($filePath), true) ?? [];
    }

    public function saveLeads(array $arrayLeads): void
    {
        File::put(storage_path("amo_crm/deals/deal.json"), json_encode($arrayLeads));
    }
}
