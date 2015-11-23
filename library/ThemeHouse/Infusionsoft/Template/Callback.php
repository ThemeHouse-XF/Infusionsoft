<?php

class ThemeHouse_Infusionsoft_Template_Callback
{

    public static function getTagOptions($content, $params)
    {
        /* @var $contactGroupCategoryDataModel ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_ContactGroupCategory */
        $contactGroupCategoryDataModel = XenForo_Model::create('ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_ContactGroupCategory');

        $contactGroupCategories = $contactGroupCategoryDataModel->getContactGroupCategories();

        $contactGroupCategoryIds = XenForo_Application::arrayColumn($contactGroupCategories, 'Id');

        /* @var $contactGroupDataModel ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_ContactGroup */
        $contactGroupDataModel = XenForo_Model::create('ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_ContactGroup');

        $contactGroups = $contactGroupDataModel->getContactGroupsForCategory($contactGroupCategoryIds);

        foreach ($contactGroups as $contactGroupId => $contactGroup) {
            $contactGroupCategories[$contactGroup['GroupCategoryId']]['groups'][$contactGroupId] = $contactGroup;
        }

        $content .= '<select id="ctrl_user_criteriainfusionsoft_contact_group_id_thdatacontact_group_id" name="user_criteria[infusionsoft_contact_group_id_th][data][contact_group_id]" class="textCtrl autoSize" />';
        foreach ($contactGroupCategories as $contactGroupCategory) {
            if (!empty($contactGroupCategory['groups'])) {
                $content .= '<optgroup label="' . $contactGroupCategory['CategoryName'] . '">';
                foreach ($contactGroupCategory['groups'] as $contactGroupId => $contactGroup) {
                    $selected = ($params['value'] == $contactGroupId) ? ' selected="selected"' : '';
                    $content .= '<option value="' . $contactGroupId . '" label="' . $contactGroup['GroupName'] . '"' . $selected . '/>';
                }
                $content .= '</optgroup>';
            }
        }
        $content .= '</select>';

        return $content;
    }
}