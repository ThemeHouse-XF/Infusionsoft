<?php

class ThemeHouse_Infusionsoft_Option_DataFormFields
{

    /**
     * Renders custom field drop-down menus.
     *
     * @param XenForo_View $view View object
     * @param string $fieldPrefix Prefix for the HTML form field name
     * @param array $preparedOption Prepared option info
     * @param boolean $canEdit True if an "edit" link should appear
     *
     * @return XenForo_Template_Abstract Template object
     */
    public static function renderDataFormMenus(XenForo_View $view, $fieldPrefix, array $preparedOption, $canEdit)
    {
        /* @var $dataFormTabModel ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_DataFormTab */
        $dataFormTabModel = XenForo_Model::create('ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_DataFormTab');

        $dataFormTabs = $dataFormTabModel->getDataFormTabsForForm(-1);

        /* @var $dataFormGroupModel ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_DataFormGroup */
        $dataFormGroupModel = XenForo_Model::create('ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_DataFormGroup');

        $dataFormGroups = $dataFormGroupModel->getDataFormGroupsForTabs(array_keys($dataFormTabs));

        /* @var $dataFormFieldModel ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_DataFormField */
        $dataFormFieldModel = XenForo_Model::create('ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_DataFormField');

        $dataFormFields = $dataFormFieldModel->getDataFormFieldsForForm(-1);

        $dataFormGroupedFields = array(
        	'' => '(' . new XenForo_Phrase('unspecified') . ')',
            'FirstName' => new XenForo_Phrase('th_first_name_infusionsoftapi')
        );
        foreach ($dataFormFields as $dataFormFieldId => $dataFormField) {
            $dataFormGroup = $dataFormGroups[$dataFormField['GroupId']];

            $dataFormGroupedFields[$dataFormGroup['Name']][$dataFormFieldId] = array(
                'label' => $dataFormField['Label'],
                'value' => '_' . $dataFormField['Name']
            );
        }

        $preparedOption['formatParams'] = $dataFormGroupedFields;

        $fieldTitles = $dataFormFieldModel->getFieldTitles();

        $preparedOption['title'] = $fieldTitles;

        return XenForo_ViewAdmin_Helper_Option::renderOptionTemplateInternal(
            'th_option_list_option_groups_infusionsoftapi', $view, $fieldPrefix, $preparedOption, $canEdit);
    }
}