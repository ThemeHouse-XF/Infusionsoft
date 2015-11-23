<?php
if (false) {

    class XFCP_ThemeHouse_Infusionsoft_Extend_XenForo_ControllerPublic_Register extends XenForo_ControllerPublic_Register
    {
    }
}

class ThemeHouse_Infusionsoft_Extend_XenForo_ControllerPublic_Register extends XFCP_ThemeHouse_Infusionsoft_Extend_XenForo_ControllerPublic_Register
{

    public function actionRegister()
    {
        $GLOBALS['XenForo_ControllerPublic_Register'] = $this;
        
        return parent::actionRegister();
    }
}