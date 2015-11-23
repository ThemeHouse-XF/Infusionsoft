<?php

class ThemeHouse_Infusionsoft_Route_PrefixAdmin_Infusionsoft implements XenForo_Route_Interface
{

    /**
     * Match a specific route for an already matched prefix.
     *
     * @see XenForo_Route_Interface::match()
     */
    public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
    {
        return $router->getRouteMatch('ThemeHouse_Infusionsoft_ControllerAdmin_Infusionsoft', $routePath, 'infusionsoft');
    }

    /**
     * Method to build a link to the specified page/action with the provided
     * data and params.
     *
     * @see XenForo_Route_BuilderInterface
     */
    public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
    {
        return XenForo_Link::buildBasicLink($outputPrefix, $action, $extension);
    }
}