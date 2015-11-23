<?php
if (false) {

    class XFCP_ThemeHouse_Infusionsoft_Extend_XenForo_ControllerAdmin_User extends XenForo_ControllerAdmin_User
    {
    }
}

class ThemeHouse_Infusionsoft_Extend_XenForo_ControllerAdmin_User extends XFCP_ThemeHouse_Infusionsoft_Extend_XenForo_ControllerAdmin_User
{

    public function actionSave()
    {
        $GLOBALS['XenForo_ControllerAdmin_User'] = $this;

        return parent::actionSave();
    }
}