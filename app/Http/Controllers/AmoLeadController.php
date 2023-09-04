<?php

namespace App\Http\Controllers;

use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Exceptions\AmoCRMMissedTokenException;
use AmoCRM\Exceptions\AmoCRMoAuthApiException;
use App\Services\AmoClientService;

class AmoLeadController extends Controller
{
    /**
     * @throws AmoCRMApiException
     * @throws AmoCRMMissedTokenException
     * @throws AmoCRMoAuthApiException
     */
    public function changeLead(AmoClientService $service)
    {
        $updates = request()->input('leads.update') ?? request()->input('leads.add');

        if ($updates) {
            foreach ($updates as $update) {
                $dealId = $update['id'] ?? null;
                $price = $update['price'] ?? null;

                $lead = $service->apiClient->leads()->getOne($dealId);
                $customFieldsValuesCollection = $lead->getCustomFieldsValues();

                $newCustomFieldsValuesArray[$dealId] = $service->newCustomFieldsValuesArray($customFieldsValuesCollection);
                $newCustomFieldsValuesArray[$dealId]['price'] = $price;

                $oldCustomFieldsValuesArray = $service->amoLeadService->getLeads();
                if ($oldCustomFieldsValuesArray && array_key_exists($dealId, $oldCustomFieldsValuesArray)) {

                    if ($service->checkEqualityOldAndNewValues($dealId, $newCustomFieldsValuesArray,
                        $oldCustomFieldsValuesArray)) continue;
                }

                $newCustomFieldsValuesArray[$dealId][$service->profitFieldId]['value'] = $service->resultProfitValues(
                    $newCustomFieldsValuesArray[$dealId]['price'], $newCustomFieldsValuesArray[$dealId][$service->primeCostId]['value']
                );

                $oldCustomFieldsValuesArray[$dealId] = $newCustomFieldsValuesArray[$dealId];
                $service->amoLeadService->saveLeads($oldCustomFieldsValuesArray);

                $service->addCustomFieldsIfEmpty($lead, $customFieldsValuesCollection,
                    $newCustomFieldsValuesArray[$dealId], $newCustomFieldsValuesArray[$dealId]['empty'] ?? []);

                $service->assignNewValueProfitForLead($customFieldsValuesCollection,
                    $newCustomFieldsValuesArray[$dealId][$service->profitFieldId]['value']);

                $service->apiClient->leads()->updateOne($lead);
            }
        }
    }
}
