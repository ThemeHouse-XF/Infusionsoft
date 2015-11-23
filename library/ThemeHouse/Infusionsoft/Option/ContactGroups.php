<?php

class ThemeHouse_Infusionsoft_Option_ContactGroups
{

    protected static $_contactGroupCategories = null;

    public static function renderUserGroupsOption(XenForo_View $view, $fieldPrefix, array $preparedOption, $canEdit)
    {
        /* @var $userGroupModel XenForo_Model_UserGroup */
        $userGroupModel = XenForo_Model::create('XenForo_Model_UserGroup');
        
        $preparedOption['userGroupTitles'] = $userGroupModel->getAllUserGroupTitles();
        
        return self::renderOption($view, $fieldPrefix, $preparedOption, $canEdit);
    }

    public static function renderMultiOption(XenForo_View $view, $fieldPrefix, array $preparedOption, $canEdit)
    {
        $preparedOption['multiple'] = true;
        
        return self::renderOption($view, $fieldPrefix, $preparedOption, $canEdit);
    }

    /**
     * Renders the contact groups option.
     *
     * @param XenForo_View $view View object
     * @param string $fieldPrefix Prefix for the HTML form field name
     * @param array $preparedOption Prepared option info
     * @param boolean $canEdit True if an "edit" link should appear
     *
     * @return XenForo_Template_Abstract Template object
     */
    public static function renderOption(XenForo_View $view, $fieldPrefix, array $preparedOption, $canEdit)
    {
        if (self::$_contactGroupCategories === null) {
            /* @var $contactGroupCategoryDataModel ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_ContactGroupCategory */
            $contactGroupCategoryDataModel = XenForo_Model::create(
                'ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_ContactGroupCategory');
            
            $contactGroupCategories = $contactGroupCategoryDataModel->getContactGroupCategories();
            
            $contactGroupCategoryIds = XenForo_Application::arrayColumn($contactGroupCategories, 'Id');
            
            /* @var $contactGroupDataModel ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_ContactGroup */
            $contactGroupDataModel = XenForo_Model::create(
                'ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_ContactGroup');
            
            $contactGroups = $contactGroupDataModel->getContactGroupsForCategory($contactGroupCategoryIds);
            
            foreach ($contactGroups as $contactGroupId => $contactGroup) {
                $contactGroupCategories[$contactGroup['GroupCategoryId']]['groups'][$contactGroupId] = $contactGroup;
            }
            
            self::$_contactGroupCategories = $contactGroupCategories;
        }
        
        $preparedOption['contactGroupCategories'] = self::$_contactGroupCategories;
        
        return XenForo_ViewAdmin_Helper_Option::renderOptionTemplateInternal(
            'th_option_template_tags_infusionsoftapi', $view, $fieldPrefix, $preparedOption, $canEdit);
    }
}