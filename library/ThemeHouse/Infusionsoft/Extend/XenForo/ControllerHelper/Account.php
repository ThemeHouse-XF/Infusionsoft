<?php
if (false) {

    class XFCP_ThemeHouse_Infusionsoft_Extend_XenForo_ControllerHelper_Account extends XenForo_ControllerHelper_Account
    {
    }
}

class ThemeHouse_Infusionsoft_Extend_XenForo_ControllerHelper_Account extends XFCP_ThemeHouse_Infusionsoft_Extend_XenForo_ControllerHelper_Account
{

    /**
     *
     * @see XenForo_ControllerHelper_Account::getWrapper()
     */
    public function getWrapper($selectedGroup, $selectedLink, XenForo_ControllerResponse_View $subView)
    {
        $wrapper = parent::getWrapper($selectedGroup, $selectedLink, $subView);

        if ($wrapper instanceof XenForo_ControllerResponse_View) {
            $wrapper->params['canEditPaymentDetails'] = true;
        }

        return $wrapper;
    }
}