<?php

class Generic_model extends CI_Model
{

    //makes this to work with columns and without where,limit and offset
    function getData($tablename = '', $columns_arr = array(), $where_arr = array(), $limit = 0, $offset = 0)
    {
        $limit = ($limit == 0) ? Null : $limit;

        if (!empty($columns_arr)) {
            $this->db->select(implode(',', $columns_arr), FALSE);
        }

        if ($tablename == '') {
            return array();
        } else {
            $this->db->from($tablename);

            if (!empty($where_arr)) {
                $this->db->where($where_arr);
            }

            if ($limit > 0 AND $offset > 0) {
                $this->db->limit($limit, $offset);
            } elseif ($limit > 0 AND $offset == 0) {
                $this->db->limit($limit);
            }

            $query = $this->db->get();

            return $query->result();
        }
    }

    function getSortData($tablename = '', $columns_arr = array(), $where_arr = array(), $limit = 0, $offset = 0, $orderby = '', $order = 'ASC')
    {
        $limit = ($limit == 0) ? Null : $limit;

        if (!empty($columns_arr)) {
            $this->db->select(implode(',', $columns_arr), FALSE);
        }

        if ($tablename == '') {
            return array();
        } else {
            $this->db->from($tablename);

            if (!empty($where_arr)) {
                $this->db->where($where_arr);
            }
            $this->db->order_by($orderby, $order); // or 'DESC'

            if ($limit > 0 AND $offset > 0) {
                $this->db->limit($limit, $offset);
            } elseif ($limit > 0 AND $offset == 0) {
                $this->db->limit($limit);
            }

            $query = $this->db->get();

            return $query->result();
        }
    }


    function getDataOr($tablename = '', $columns_arr = array(), $where_arr = array(), $limit = 0, $offset = 0)
    {
        $limit = ($limit == 0) ? Null : $limit;

        if (!empty($columns_arr)) {
            $this->db->select(implode(',', $columns_arr), FALSE);
        }

        if ($tablename == '') {
            return array();
        } else {
            $this->db->from($tablename);

            if (!empty($where_arr)) {
                $this->db->or_where($where_arr); //Or operator added here
            }

            if ($limit > 0 AND $offset > 0) {
                $this->db->limit($limit, $offset);
            } elseif ($limit > 0 AND $offset == 0) {
                $this->db->limit($limit);
            } elseif ($limit == 0 AND $offset > 0) {
                $this->db->limit(0, $offset);
            }

            $query = $this->db->get();

            return $query->result();
        }
    }

    function getSetting($settingCode = '')
    {
        $settingValue = '';
        $retData = array();
        if ($settingCode == '') {
            $settingValue = '';
        } else {
            $retData = $this->getData('TBL_SETTINGS', array('setting_value'), array('setting_code' => $settingCode), 1);
            if (count($retData) > 0) {
                $settingValue = $retData[0]->setting_value;
            } else {
                $settingValue = '';
            }
        }
        return $settingValue;
    }

    function insertData($tablename, $data_arr = array())
    {
        //SET IDENTITY_INSERT $tablename ON

        $trno = $this->getNextSerialNumber('logtrno');
        $ret = 0;
        $userdata = $this->current_user->id;
        $data_arr['created_user'] = $userdata;
        $action = "Insert";
        try {
            //$data_arr['CREATED_USER'] = $userdata->id;
            $this->db->insert($tablename, $data_arr);
            $ret = $this->db->insert_id() + 0;
            // write($tablename, $data_arr, $action) {

            $this->Log_model->write($tablename, $data_arr, $action, $trno);
            $this->Generic_model->increaseSerialNumber('logtrno');
            return $ret;
        } catch (Exception $err) {

            $this->Log_model->write($tablename, $err->getMessage(), $action, $trno);
            $this->Generic_model->increaseSerialNumber('logtrno');
            return $err->getMessage();
        }
    }

    function insertDataWS($tablename, $data_arr = array())
    {
        //SET IDENTITY_INSERT $tablename ON
        $ret = 0;
        try {

            $this->db->insert($tablename, $data_arr);
            $ret = $this->db->insert_id() + 0;
            return $ret;
        } catch (Exception $err) {

            return $err->getMessage();
        }
    }

    function updateData($tablename, $data_arr, $where_arr)
    {
        //SET IDENTITY_INSERT $tablename ON
        $trno = $this->getNextSerialNumber('logtrno');
        $action = "Update";
        try {
            $result = $this->db->update($tablename, $data_arr, $where_arr);
            $this->Log_model->write($tablename, $data_arr, $action, $trno);

            $report = array();
            $report['error'] = $this->db->error();
            $report['message'] = $this->db->error();
            return $result;
        } catch (Exception $err) {

            return $err->getMessage();
        }
    }
    function updateDataWS($tablename, $data_arr, $where_arr)
    {

        try {
            $result = $this->db->update($tablename, $data_arr, $where_arr);

            $report = array();
            $report['error'] = $this->db->error();
            $report['message'] = $this->db->error();
            return $result;
        } catch (Exception $err) {

            return $err->getMessage();
        }
    }
    function updateDataWithoutlog($tablename, $data_arr, $where_arr)
    {

        try {
            $result = $this->db->update($tablename, $data_arr, $where_arr);

            $report['error'] = $this->db->error();
            $report['message'] = $this->db->error();
            return $result;
        } catch (Exception $err) {

            return $err->getMessage();
        }
    }

    function updateMultipleData($tablename, $data_arr, $keyColumn, $trno = Null)
    {
        $action = "M Update";
        try {
            // write($tablename, $data_arr, $action) {
            if (isset($trnno)) {
                $this->Log_model->write($tablename, $data_arr, $action, $trno);
            }
            return $this->db->update_batch($tablename, $data_arr, $keyColumn);
        } catch (Exception $err) {
            if (isset($trnno)) {
                $this->Log_model->write($tablename, $data_arr, $action, $trno);
            }
            return $err->getMessage();
        }
    }

    function deleteData($tablename, $where_arr)
    {

        try {
            $this->db->where($where_arr, NULL, FALSE);
            $result = $this->db->delete($tablename);
        } catch (Exception $err) {
            $result = $err->getMessage();
        }
        return $result;
    }

    function deleteMultipleData($tablename, $value_arr, $keyColumn, $trno = Null)
    {
        $action = "M Delete";
        try {
            // write($tablename, $data_arr, $action) {
            if (isset($trnno)) {
                $this->Log_model->write($tablename, $value_arr, $action, $trno);
            }
            $this->db->where_in($keyColumn, $value_arr);
            $result = $this->db->delete($tablename);
        } catch (Exception $err) {

            // write($tablename, $data_arr, $action) {
            if (isset($trnno)) {
                $this->Log_model->write($tablename, $value_arr, $action, $trno);
            }
            $result = $err->getMessage();
        }
        return $result;
    }

    /*
     * *************Table row count.getrowcount()*****************
     */

    function getrowcount($tableName, $where_arr = '')
    {
        /*
         *  echo $this->db->count_all_results('my_table');
          // Produces an integer, like 25

          $this->db->like('title', 'match');
          $this->db->from('my_table');
          echo $this->db->count_all_results();
          // Produces an integer, like 17
         */

        if (!empty($where_arr)) {
            $this->db->where($where_arr);
        }
        $count = $this->db->count_all_results($tableName);

        return $count;
    }

    /*
     *  
     */

    /*     * ****** Grid Functions ********* */

    function getcount($tablename, $where_arr = '')
    {
        $count = 0;

        if (count($where_arr) > 0) {

            $this->db->where($where_arr, NULL, FALSE);
        }
        if (isset($tablename)) {
            $count = $this->db->count_all($tablename);
        }
        return $count;
    }

    function getgriddata($tablename, $columns_arr, $where_arr, $like_arr, $sidx, $sord, $limit, $start)
    {
        if (!empty($where_arr)) {
            $this->db->where($where_arr, NULL, FALSE);
        }
        if (!empty($columns_arr)) {
            $this->db->select(implode(',', $columns_arr));
        }

        if (!empty($like_arr)) {
            foreach ($like_arr as $fld => $searchString) {
                $this->db->like($fld, $searchString, 'after');
            }
        }

        $this->db->order_by($sidx, $sord);
        $query = $this->db->get($tablename, $limit, $start);
        return $query->result();
    }

    //Return the field names of the selected table
    function getColumnNames($tableName)
    {
        $fields = $this->db->list_fields($tableName);

        return $fields;
    }

    function dbprefix($tableName)
    {
        $prefix = $this->db->dbprefix($tableName);

        return $prefix;
    }

    function genericQuery($strSQL)
    {
        if (!empty($strSQL)) {
            try {
                $query = $this->db->query($strSQL);
                if (!$query) {
                    throw new Exception($this->db->_error_message(), $this->db->_error_number());
                    return FALSE;
                } else {
                    return $query->result();
                }
            } catch (Exception $e) {
                return;
            }
        } else {
            return FALSE;
        }
    }

    function getFirstValue($strSQL)
    {
        $ret = Null;
        if (!empty($strSQL)) {
            try {
                $query = $this->db->query($strSQL);
                if ($query) {
                    $result = $query->result();
                    if (count($result) > 0) {
                        $resultArray = (array)$result[0];
                        foreach ($result[0] as $key => $value) {
                            $ret = $value;
                            break;
                        }
                    } else {
                        $ret = Null;
                    }
                } else {
                    $ret = Null;
                }
                //
            } catch (Exception $ex) {
                $ret = Null;
            }
        } else {
            $ret = Null;
        }
        return $ret;
    }

    function actionQuery($strSQL)
    {
        if (!empty($strSQL)) {
            try {
                $query = $this->db->query($strSQL);
                if (!$query) {
                    throw new Exception($this->db->_error_message(), $this->db->_error_number());
                    return FALSE;
                } else {
                    return TRUE;
                }
            } catch (Exception $e) {
                return;
            }
        } else {
            return FALSE;
        }
    }

    function getNextSerialNumber($Code)
    {
        try {
            $strSQL = "SELECT snumber from fm_serials where code = '" . $Code . "'";
            $query = $this->db->query($strSQL);
            $currentSN = $query->result();
            if ($currentSN) {
                $serailno = ((int)$currentSN[0]->snumber) + 1;
            } else {
                $serailno = 99999;
            }
        } catch (Exception $ex) {

            $serailno = 900000;
        }
        //$serailno = 100;
        return $serailno;
    }

    function getKeyofTable($Code)
    {
        try {
            $strSQL = "SELECT REPT_KEY from TBL_REPORT_KEY where REPT_TABLE = '" . $Code . "'";
            $query = $this->db->query($strSQL);
            $currentSN = $query->result();
            if ($currentSN) {
                $serailno = ($currentSN[0]->REPT_KEY);
            } else {
                $serailno = NULL;
            }
        } catch (Exception $ex) {

            $serailno = 900000;
        }
        //$serailno = 100;
        return $serailno;
    }

    function increaseSerialNumber($Code)
    {
        try {
            $strSQL = "UPDATE fm_serials SET snumber = snumber + 1 WHERE code = '" . $Code . "'";
            $query = $this->db->query($strSQL);
            $rtn = TRUE;
        } catch (Exception $ex) {

            $rtn = FALSE;
        }
        return $rtn;
    }


//Tharanga Jayasinghe
//2 master with 1 trsnasction join
    function getStructuredData($masterbase, $columns_arr = array(), $where_arr = array(), $joinfirst, $joinsecond, $keyfirst, $keysecond, $limit = 0)
    {
        $limit = ($limit == 0) ? Null : $limit;
        if (!empty($columns_arr)) {
            $this->db->select(implode(',', $columns_arr), FALSE);
        } else {
            $this->db->select('*');
        }

        $this->db->from($masterbase);
        $this->db->join($joinfirst, $joinfirst . '.id= ' . $masterbase . '.' . $keyfirst, 'left');
        $this->db->join($joinsecond, $joinsecond . '.id=' . $masterbase . '.' . $keysecond, 'left');

        if (!empty($where_arr)) {
            $this->db->where($where_arr);
        }
        $this->db->limit($limit);

        $query = $this->db->get();
        return $query->result();
    }

    function getAdvancedData($masterbase, $columns_arr = array(), $where_arr = array(), $joinfirst, $joinsecond, $jointhird, $keyfirst, $keysecond, $keythird, $groupby = '', $orderby = 'id', $order = 'ASC')
    {
        if (!empty($columns_arr)) {
            $this->db->select(implode(',', $columns_arr), FALSE);
        } else {
            $this->db->select('*');
        }

        $this->db->from($masterbase);
        $this->db->join($joinfirst, $joinfirst . '.id= ' . $masterbase . '.' . $keyfirst, 'left');
        $this->db->join($joinsecond, $joinsecond . '.id=' . $masterbase . '.' . $keysecond, 'left');
        $this->db->join($jointhird, $jointhird . '.id=' . $masterbase . '.' . $keythird, 'left');

        if (!empty($where_arr)) {
            $this->db->where($where_arr);
        }
        if (isset($groupby)) {
            $this->db->group_by($groupby);
        }


        $this->db->order_by($orderby, $order); // or 'DESC'
        $query = $this->db->get();
        return $query->result();
    }

    function getJoin($tablename = "", $columns_arr = array(), $where_arr = array(), $jointable = "", $primaryjoinonkey = "", $basejoinkey = "", $limit = 0, $offset = 0)
    {

        if (!empty($columns_arr)) {
            $this->db->select(implode(',', $columns_arr), FALSE);
        }

        if ($tablename == '') {
            return array();
        } else {
            $this->db->from($tablename);
            $primKey = $tablename + '.' + $primaryjoinonkey;
            $basekey = $jointable + '.' + $basejoinkey;
            $this->db->join($jointable, '' . $primKey = $basekey . '');

            if (!empty($where_arr)) {
                $this->db->where($where_arr);
            }

            if ($limit > 0 AND $offset > 0) {
                $this->db->limit($limit, $offset);
            } elseif ($limit > 0 AND $offset == 0) {
                $this->db->limit($limit);
            }
            $query = $this->db->get();
            return $query->result();
        }
    }

    function getAutoFillData($tablename, $fieldName, $value, $keyfield, $limit, $offset)
    {
        $this->db->select($keyfield . ', ' . $fieldName);
        $this->db->from($tablename);
        $this->db->like($fieldName, $value, 'after');
        $this->db->limit($limit);
        $query = $this->db->get();
        //$query = $this->db->get_where($tablename, $where_arr, $limit, $offset);
        return $query->result();
    }

    function getFilteredAutoFillData($tablename, $fieldName, $value, $keyfield, $whereArr, $limit, $offset)
    {
        $this->db->select($keyfield . ', ' . $fieldName);
        $this->db->from($tablename);
        $this->db->like($fieldName, $value, 'after');
        $this->db->where($whereArr);
        $this->db->limit($limit);
        $query = $this->db->get();
        //$query = $this->db->get_where($tablename, $where_arr, $limit, $offset);
        return $query->result();
    }

    function getMultiAutoFillData($tablename, $fieldName, $value, $keyfield, $fieldNext, $limit, $offset)
    {
        $this->db->select($keyfield . ', ' . $fieldName . ', ' . $fieldNext);
        $this->db->from($tablename);
        $this->db->like($fieldName, $value, 'after');
        $this->db->limit($limit);
        $query = $this->db->get();
        //$query = $this->db->get_where($tablename, $where_arr, $limit, $offset);
        return $query->result();
    }

    function getAccountData($tablename, $fieldName, $value, $keyfield, $field1, $field2, $field3, $limit, $offset)
    {
        $this->db->select($keyfield . ', ' . $fieldName . ', ' . $field1 . ', ' . $field2 . ', ' . $field3);
        $this->db->from($tablename);
        $this->db->like($fieldName, $value, 'after');
        $this->db->limit($limit);
        $query = $this->db->get();
        //$query = $this->db->get_where($tablename, $where_arr, $limit, $offset);
        return $query->result();
    }


    function getConfigValue($configname)
    {
        return $this->config->item($configname);
    }

    function addArray($insData = array())
    {
        $prep = array();
        foreach ($insData as $k => $v) {
            $prep[$k] = $v;
        }
        // var_dump($prep);
        $strSQL = "INSERT INTO fm_test ( " . implode(', ', array_keys($insData)) . ") VALUES (" . implode(' , ', array_keys($prep)) . ")";
        var_dump($strSQL);
        die;

        if (!empty($strSQL)) {
            try {
                $query = $this->db->query($strSQL);
                if (!$query) {
                    throw new Exception($this->db->_error_message(), $this->db->_error_number());
                    return FALSE;
                } else {
                    return $query->result();
                }
            } catch (Exception $e) {
                return;
            }
        } else {
            return FALSE;
        }

    }

    function insert_batch($table, $data_arr = array())
    {
        if ($table) {
            try {
                if (!empty($data_arr)) {
                    $this->db->insert_batch($table, $data_arr);
                }
            } catch (Exception $e) {
                return;
            }
        } else {
            $erro_message = "No table selected";
            return $erro_message;
        }

    }


}

?>
