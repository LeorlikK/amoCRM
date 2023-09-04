<?php

namespace App\Services;

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Collections\CustomFieldsValuesCollection;
use AmoCRM\Models\CustomFieldsValues\NumericCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\NumericCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueModels\NumericCustomFieldValueModel;
use AmoCRM\Models\LeadModel;
use App\Services\Interfaces\AmoClient;
use League\OAuth2\Client\Token\AccessToken;

class AmoClientService implements AmoClient
{
    public AmoCRMApiClient $apiClient;
    public string $profitFieldId;
    public string $primeCostId;
    public AmoTokenService $amoTokenService;
    public AmoLeadService $amoLeadService;

    public function __construct(AmoTokenService $amoJsonService, AmoLeadService $amoLeadService)
    {
        $clientId = config('amoCRM.client_id');
        $clientSecret = config('amoCRM.client_secret');
        $redirectUri = config('amoCRM.redirect_uri');
        $accountBaseDomain = config('amoCRM.base_domain');
        $expiresTime = config('amoCRM.expires_time');
        $amoJsonService->checkAccessToken($clientId, $clientSecret, $redirectUri);
        $access_token = $amoJsonService->access_token;
        $this->profitFieldId = config('amoCRM.profit_field_id');
        $this->primeCostId = config('amoCRM.prime_cost');
        $this->amoTokenService = $amoJsonService;
        $this->amoLeadService = $amoLeadService;

        $apiClient = new AmoCRMApiClient($clientId, $clientSecret, $redirectUri);
        $accessToken = new AccessToken(['access_token' => $access_token, 'expires' => time() + $expiresTime]);

        $apiClient->setAccessToken($accessToken);
        $apiClient->setAccountBaseDomain($accountBaseDomain);

        $this->apiClient = $apiClient;
    }

    public function newCustomFieldsValuesArray(?CustomFieldsValuesCollection $customFieldsValuesCollection): array
    {
        $newCustomFieldsValuesArray = [];

        if ($customFieldsValuesCollection) {
            foreach ($customFieldsValuesCollection as $customField) {
                $fieldId = (string)$customField->getFieldId();
                $fieldName = $customField->getFieldName();
                $fieldValues = $customField->getValues();

                if ($fieldId === $this->primeCostId || $fieldId === $this->profitFieldId) {
                    $newCustomFieldsValuesArray[$fieldId] = [
                        'name' => $fieldName,
                        'value' => $fieldValues->first()->getValue()
                    ];
                }
            }
        }

        if (!isset($newCustomFieldsValuesArray[$this->primeCostId])) {
            $newCustomFieldsValuesArray['empty'][] = $this->primeCostId;
            $newCustomFieldsValuesArray[$this->primeCostId] = [
                'name' => 'Себестоимость',
                'value' => '0'
            ];
        }

        if (!isset($newCustomFieldsValuesArray[$this->profitFieldId])) {
            $newCustomFieldsValuesArray['empty'][] = $this->profitFieldId;
            $newCustomFieldsValuesArray[$this->profitFieldId] = [
                'name' => 'Прибыль',
                'value' => '0'
            ];
        }

        return $newCustomFieldsValuesArray;
    }

    public function checkEqualityOldAndNewValues(int $dealId, array $newCustomFieldsValuesArray, array $oldCustomFieldsValuesArray): bool
    {
        $newPrimeCostValue = (string)$newCustomFieldsValuesArray[$dealId][$this->primeCostId]['value'];
        $newProfitValue = (string)$newCustomFieldsValuesArray[$dealId][$this->profitFieldId]['value'];
        $newPrice = (string)$newCustomFieldsValuesArray[$dealId]['price'];

        $oldPrimeCostValue = (string)$oldCustomFieldsValuesArray[$dealId][$this->primeCostId]['value'];
        $oldProfitValue = (string)$oldCustomFieldsValuesArray[$dealId][$this->profitFieldId]['value'];
        $oldPrice = (string)$oldCustomFieldsValuesArray[$dealId]['price'];

        return ($oldPrimeCostValue === $newPrimeCostValue
            && $oldProfitValue === $newProfitValue
            && $oldPrice === $newPrice);
    }

    public function resultProfitValues($price, $primeCost): string
    {
        $price = $price ?? 0;
        $primeCost = $primeCost ?? 0;

        return (string)(intval($price) - intval($primeCost));
    }

    public function addCustomFieldsIfEmpty(
        LeadModel                     &$lead,
        ?CustomFieldsValuesCollection &$customFieldsValuesCollection,
        array                         $newCustomFieldsValuesArray,
        array                         $emptyArray): void
    {
        if (is_null($customFieldsValuesCollection)) {
            $customFieldsValuesCollection = new CustomFieldsValuesCollection();
            $lead->setCustomFieldsValues($customFieldsValuesCollection);
        }

        foreach ($emptyArray as $emptyValue) {
            if (strval($emptyValue) === $this->profitFieldId) {

                $this->addNewNumericCustomFieldInCollection($customFieldsValuesCollection,
                    $this->profitFieldId, 'Прибыль', $newCustomFieldsValuesArray[$this->profitFieldId]['value']);

            }
            if (strval($emptyValue) === $this->primeCostId) {

                $this->addNewNumericCustomFieldInCollection($customFieldsValuesCollection,
                    $this->primeCostId, 'Себестоимость', $newCustomFieldsValuesArray[$this->primeCostId]['value']);
            }
        }
    }

    public function assignNewValueProfitForLead(
        CustomFieldsValuesCollection &$customFieldsValuesCollection, string $newValueProfit): void
    {
        foreach ($customFieldsValuesCollection as $customField) {
            if (((string)$customField->getFieldId()) === $this->profitFieldId) {
                $customField->getValues()->first()->setValue($newValueProfit);
            }
        }
    }

    public function addNewNumericCustomFieldInCollection(
        CustomFieldsValuesCollection &$customFieldsValuesCollection, string|int $id, string $name, $value): void
    {
        $numericCustomFieldValuesModel = new NumericCustomFieldValuesModel();
        $numericCustomFieldValuesModel->setFieldId($id)->setFieldName($name);
        $numericCustomFieldValuesModel->setValues(
            (new NumericCustomFieldValueCollection())
                ->add((new NumericCustomFieldValueModel())->setValue((string)$value))
        );
        $customFieldsValuesCollection->add($numericCustomFieldValuesModel);
    }
}
