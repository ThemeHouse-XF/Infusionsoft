<?php
if (false) {

    class XFCP_ThemeHouse_Infusionsoft_Extend_ThemeHouse_Invoices_Model_Invoice extends ThemeHouse_Invoices_Model_Invoice
    {
    }
}

class ThemeHouse_Infusionsoft_Extend_ThemeHouse_Invoices_Model_Invoice extends XFCP_ThemeHouse_Infusionsoft_Extend_ThemeHouse_Invoices_Model_Invoice
{

    /**
     * Gets the specified invoice ID by Infusionsoft order ID.
     *
     * @param integer $orderId
     *
     * @return int
     */
    public function getInvoiceIdByInfusionsoftOrderId($orderId)
    {
        if (empty($orderId)) {
            return false;
        }

        return $this->_getDb()->fetchOne(
            '
			SELECT invoice_id
			FROM invoice_th
			WHERE infusionsoft_invoice_id_th = ?
		', $orderId);
    }
}