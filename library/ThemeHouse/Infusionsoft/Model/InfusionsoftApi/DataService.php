<?php

class ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService extends ThemeHouse_Infusionsoft_Model_InfusionsoftApi
{

    public function add($table, array $values)
    {
        $query = array(
            $table,
            $values
        );

        return $this->call('DataService.add', $query);
    }

    public function update($table, $id, array $values)
    {
        $query = array(
            $table,
            $id,
            $values
        );

        return $this->call('DataService.update', $query);
    }

    public function delete($table, $id)
    {
        $query = array(
            $table,
            $id
        );

        return $this->call('DataService.delete', $query);
    }

    public function query($table, $limit, $page, array $queryData, array $selectedFields, $orderBy = '',
        $ascending = false, $key = 'Id')
    {
        if (!$queryData) {
            $queryData = array(
            	$key => '%'
            );
        }

        $query = array(
            (string) $table,
            (int) $limit,
            (int) $page,
            $queryData,
            $selectedFields,
            (string) $orderBy,
            (boolean) $ascending
        );

        $results = $this->call('DataService.query', $query);

        $keyedResults = array();
        if (in_array($key, $selectedFields)) {
            foreach ($results as $result) {
                if (!empty($result[$key])) {
                    $keyedResults[$result[$key]] = $result;
                }
            }
            return $keyedResults;
        }

        return $results;
    }
}