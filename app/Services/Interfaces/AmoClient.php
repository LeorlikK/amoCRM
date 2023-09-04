<?php

namespace App\Services\Interfaces;

use AmoCRM\Collections\CustomFieldsValuesCollection;
use AmoCRM\Models\LeadModel;

interface AmoClient
{
    public function newCustomFieldsValuesArray(?CustomFieldsValuesCollection $customFieldsValuesCollection): array;

    public function checkEqualityOldAndNewValues(int $dealId, array $newCustomFieldsValuesArray, array $oldCustomFieldsValuesArray): bool;

    public function resultProfitValues($price, $primeCost): string;

    public function addCustomFieldsIfEmpty(
        LeadModel                     &$lead,
        ?CustomFieldsValuesCollection &$customFieldsValuesCollection,
        array                         $newCustomFieldsValuesArray,
        array                         $emptyArray
    ): void;

    public function assignNewValueProfitForLead(
        CustomFieldsValuesCollection &$customFieldsValuesCollection, string $newValueProfit): void;

    public function addNewNumericCustomFieldInCollection(
        CustomFieldsValuesCollection &$customFieldsValuesCollection, string|int $id, string $name, $value): void;
}
