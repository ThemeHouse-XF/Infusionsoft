<?php

class ThemeHouse_Infusionsoft_Listener_CriteriaUser
{

    public static function criteriaUser($rule, array $data, array $user, &$returnValue)
    {
        switch ($rule) {
            case 'infusionsoft_contact_group_id_th':
                if (!empty($user['infusionsoft_contact_group_ids_th'])) {
                    if (strpos(",{$user['infusionsoft_contact_group_ids_th']},", ",{$data['contact_group_id']},") !==
                         false) {
                        $returnValue = true;
                    }
                }
                break;
        }
    } /* END criteriaUser */
}