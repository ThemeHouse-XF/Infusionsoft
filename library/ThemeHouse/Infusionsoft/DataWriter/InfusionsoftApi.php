<?php

abstract class ThemeHouse_Infusionsoft_DataWriter_InfusionsoftApi extends XenForo_DataWriter
{

    protected function _getUpdateCondition($tableName)
    {
        return $this->get('Id');
    }

    /**
     * Internal save handler.
     */
    protected function _insert()
    {
        $dataServiceModel = $this->_getDataServiceModel();

        foreach ($this->_getTableList() as $tableName) {
            $id = $dataServiceModel->add($tableName, $this->_newData[$tableName]);

            $this->_setAutoIncrementValue($id, $tableName, true);
        }
    }

    /**
     * Internal update handler.
     */
    protected function _update()
    {
        $dataServiceModel = $this->_getDataServiceModel();

        foreach ($this->_getTableList() as $tableName) {
            if (!($update = $this->getUpdateCondition($tableName)) || empty($this->_newData[$tableName])) {
                continue;
            }
            $dataServiceModel->update($tableName, $update, $this->_newData[$tableName]);
        }
    }

    /**
     * Deprecated.
     *
     * @return boolean
     */
    protected function _beginDbTransaction()
    {
        return true;
    }

    /**
     * Deprecated.
     *
     * @return boolean
     */
    protected function _commitDbTransaction()
    {
        return true;
    }

    /**
     * Deprecated.
     *
     * @return boolean
     */
    protected function _rollbackDbTransaction()
    {
        return true;
    }

    /**
     *
     * @return ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService
     */
    protected function _getDataServiceModel()
    {
        return $this->getModelFromCache('ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService');
    }
}