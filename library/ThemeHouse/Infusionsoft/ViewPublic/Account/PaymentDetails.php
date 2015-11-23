<?php

class ThemeHouse_Infusionsoft_ViewPublic_Account_PaymentDetails extends XenForo_ViewPublic_Base
{

    public function prepareParams()
    {
        parent::prepareParams();

        foreach ($this->_params['creditCards'] as $id => $creditCard) {
            if (!empty($creditCard['CardType'])) {
                $this->_params['creditCards'][$id]['icon'] = strtolower(
                    preg_replace("/[^A-Za-z]/", '', $creditCard['CardType']));
            }
        }
    }

    public function renderJson()
    {
        $creditCards = array();
        foreach ($this->_params['creditCards'] as $id => $creditCard) {
            $creditCards[$creditCard['Id']] = $this->createTemplateObject('th_credit_card_infusionsoftapi',
                array(
                    'creditCard' => $creditCard
                ));
        }

        return XenForo_ViewRenderer_Json::jsonEncodeForOutput(
            array(
                'creditCards' => $creditCards,
                'ids' => implode(',', array_keys($creditCards))
            ));
    }
}