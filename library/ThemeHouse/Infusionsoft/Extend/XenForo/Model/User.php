<?php
if (false) {

    class XFCP_ThemeHouse_Infusionsoft_Extend_XenForo_Model_User extends XenForo_Model_User
    {
    }
}

class ThemeHouse_Infusionsoft_Extend_XenForo_Model_User extends XFCP_ThemeHouse_Infusionsoft_Extend_XenForo_Model_User
{

    /**
     * Gets the specified user ID by contact ID.
     *
     * @param integer $contactId
     *
     * @return int
     */
    public function getUserIdByContactId($contactId)
    {
        if (empty($contactId)) {
            return false;
        }

        return $this->_getDb()->fetchOne(
            '
			SELECT user_id
			FROM xf_user_profile
			WHERE infusionsoft_contact_id_th = ?
		', $contactId);
    }

    /**
     * Returns a user record based on an input contact ID
     *
     * @param string $contactId
     * @param array $fetchOptions User fetch options
     *
     * @return array false
     */
    public function getUserByContactId($contactId, array $fetchOptions = array())
    {
        $this->addFetchOptionJoin($fetchOptions, XenForo_Model_User::FETCH_USER_PROFILE);

        $joinOptions = $this->prepareUserFetchOptions($fetchOptions);

        return $this->_getDb()->fetchRow(
            '
			SELECT user.*
				' . $joinOptions['selectFields'] . '
			FROM xf_user AS user
			' . $joinOptions['joinTables'] . '
			WHERE user_profile.infusionsoft_contact_id_th = ?
		', $contactId);
    }

    /**
     * Returns user records based on input contact IDs
     *
     * @param array $contactIds
     * @param array $fetchOptions User fetch options
     *
     * @return array false
     */
    public function getUsersByContactIds(array $contactIds, array $fetchOptions = array())
    {
        $this->addFetchOptionJoin($fetchOptions, XenForo_Model_User::FETCH_USER_PROFILE);

        $joinOptions = $this->prepareUserFetchOptions($fetchOptions);

        return $this->fetchAllKeyed(
            '
			SELECT user.*
				' . $joinOptions['selectFields'] . '
			FROM xf_user AS user
			' . $joinOptions['joinTables'] . '
			WHERE user_profile.infusionsoft_contact_id_th IN (' . $this->_getDb()->quote($contactIds) . ')
		', 'user_id');
    }

    /**
     * Returns user records based on input emails
     *
     * @param array $emails
     * @param array $fetchOptions User fetch options
     *
     * @return array false
     */
    public function getUsersByEmails(array $emails, array $fetchOptions = array())
    {
        $joinOptions = $this->prepareUserFetchOptions($fetchOptions);

        return $this->fetchAllKeyed(
            '
			SELECT user.*
				' . $joinOptions['selectFields'] . '
			FROM xf_user AS user
			' . $joinOptions['joinTables'] . '
			WHERE user.email IN (' . $this->_getDb()->quote($emails) . ')
		', 'user_id');
    }

    /**
     * Gets the specified contact ID by user ID.
     *
     * @param integer $userId
     *
     * @return int
     */
    public function getContactIdByUserId($userId)
    {
        if (empty($userId)) {
            return false;
        }

        return $this->_getDb()->fetchOne(
            '
			SELECT infusionsoft_contact_id_th
			FROM xf_user_profile
			WHERE user_id = ?
		', $userId);
    }
}