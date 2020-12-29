<?php

class Common extends CI_Model {

    //insert data into database and returns true and false.
    //used mostly when primary key field is not an auto increment.
    function insert_data($data, $tablename) {
        if ($this->db->insert($tablename, $data)) {
            return true;
        } else {
            return false;
        }
    }

    //insert data into database and returns last insert id or 0
    function insert_data_getid($data, $tablename) {
        if ($this->db->insert($tablename, $data)) {
            return $this->db->insert_id();
        } else {
            return 0;
        }
    }

    public function insert_batch($data, $tablename) {
        $res = $this->db->insert_batch($tablename, $data);
        if ($res) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function select_data_by_multiple_condition($tablename, $condition_array = array(), $data = '*', $where_in_col = '', $orderby = '', $limit = '', $offset = '', $join_str = array(), $groupby = '', $condition_or_arr = array(), $where_in_val = array()) {
        //select_data_by_multiple_condition('biometric_student_attendance', $condition_arr, $selected,$where_in,$orderby, '', '', $join_str,'','');
        $this->db->select($data);
        $this->db->from($tablename);

        //if join_str array is not empty then implement the join query
        if (!empty($join_str)) {
            foreach ($join_str as $join) {
                if (!isset($join['join_type'])) {
                    $this->db->join($join['table'], $join['join_table_id'] . '=' . $join['from_table_id']);
                } else {
                    $this->db->join($join['table'], $join['join_table_id'] . '=' . $join['from_table_id'], $join['join_type']);
                }
            }
        }

        //condition array pass to where condition
        $this->db->where($condition_array);
        //$this->db->where('student_assignment_reply.student_id is null');
        if (!empty($where_in_val)) {
            $this->db->where_in($where_in_col, $where_in_val);
        } else {
            $this->db->where_in($where_in_col);
        }
        if (!empty($condition_or_arr)) {
            $this->db->group_start();
            $this->db->or_where($condition_or_arr);
            $this->db->group_end();
        }
        //Setting Limit for Paging
        if ($limit != '' && $offset == 0) {
            $this->db->limit($limit);
        } else if ($limit != '' && $offset != 0) {
            $this->db->limit($limit, $offset);
        }

        if ($groupby != '') {
            $this->db->group_by($groupby);
        }
        //order by query

        if ($orderby = '') {
            $this->db->order_by($orderby);
        }


        $query = $this->db->get();

        //if limit is empty then returns total count
        if ($limit == '') {
            $query->num_rows();
        }
        //if limit is not empty then return result array
        return $query->result_array();
    }

    //update database and returns true and false based on single column
    function update_data($data, $tablename, $columnname, $columnid) {
        $this->db->where($columnname, $columnid);
        if ($this->db->update($tablename, $data)) {
            return true;
        } else {
            return false;
        }
    }

    function update_data_by_conditions($data, $tablename, $conditions) {
        if ($this->db->update($tablename, $data, $conditions)) {
            return true;
        } else {
            return false;
        }
    }

    // select data using column id
    function select_data_by_id($tablename, $columnname, $columnid, $data = '*', $join_str = array()) {
        $this->db->select($data);
        $this->db->from($tablename);
        //if join_str array is not empty then implement the join query
        if (!empty($join_str)) {
            foreach ($join_str as $join) {
                //check for join type
                if (!isset($join['join_type'])) {
                    $this->db->join($join['table'], $join['join_table_id'] . '=' . $join['from_table_id']);
                } else {
                    $this->db->join($join['table'], $join['join_table_id'] . '=' . $join['from_table_id'], $join['join_type']);
                }
            }
        }
        $this->db->where($columnname, $columnid);
        $query = $this->db->get();
        return $query->result_array();
    }

    // select data using multiple conditions
    function select_data_by_condition($tablename, $condition_array = array(), $data = '*', $sortby = '', $orderby = '', $limit = '', $offset = '', $join_str = array()) {

        $this->db->select($data);
        $this->db->from($tablename);

        //if join_str array is not empty then implement the join query
        if (!empty($join_str)) {
            foreach ($join_str as $join) {
                if (!isset($join['join_type'])) {
                    $this->db->join($join['table'], $join['join_table_id'] . '=' . $join['from_table_id']);
                } else {
                    $this->db->join($join['table'], $join['join_table_id'] . '=' . $join['from_table_id'], $join['join_type']);
                }
            }
        }

        //condition array pass to where condition
        $this->db->where($condition_array);


        //Setting Limit for Paging
        if ($limit != '' && $offset == 0) {
            $this->db->limit($limit);
        } else if ($limit != '' && $offset != 0) {
            $this->db->limit($limit, $offset);
        }

        //order by query
        if ($sortby != '' && $orderby != '') {
            $this->db->order_by($sortby, $orderby);
        }

        $query = $this->db->get();

        //if limit is empty then returns total count
        if ($limit == '') {
            $query->num_rows();
        }
        //if limit is not empty then return result array
        return $query->result_array();
    }

    // select data using multiple conditions and search keyword
    function select_data_by_search($tablename, $search_condition, $condition_array = array(), $data = '*', $sortby = '', $orderby = '', $limit = '', $offset = '', $join_str = array()) {

        $this->db->select($data);
        $this->db->from($tablename);

        //if join_str array is not empty then implement the join query
        if (!empty($join_str)) {
            foreach ($join_str as $join) {
                if (!isset($join['join_type'])) {
                    $this->db->join($join['table'], $join['join_table_id'] . '=' . $join['from_table_id']);
                } else {
                    $this->db->join($join['table'], $join['join_table_id'] . '=' . $join['from_table_id'], $join['join_type']);
                }
            }
        }

        if ($search_condition != '') {
            $this->db->where($search_condition);
        }
        if (!empty($condition_array)) {
            $this->db->where($condition_array);
        }

        //Setting Limit for Paging
        if ($limit != '' && $offset == 0) {
            $this->db->limit($limit);
        } else if ($limit != '' && $offset != 0) {
            $this->db->limit($limit, $offset);
        }
        //order by query
        if ($sortby != '' && $orderby != '') {
            $this->db->order_by($sortby, $orderby);
        }

        $query = $this->db->get();
        //if limit is empty then returns total count
        if ($limit == '') {
            $query->num_rows();
        }
        //if limit is not empty then return result array
        return $query->result_array();
    }

    //table records count
    function get_count_of_table($table) {
        $query = $this->db->get($table)->num_rows();
        return $query;
    }

    // delete data
    function delete_data($tablename, $columnname, $columnid) {
        $this->db->where($columnname, $columnid);
        if ($this->db->delete($tablename)) {
            return true;
        } else {
            return false;
        }
    }

    // check unique avaliblity
    function check_unique_avalibility($tablename, $columname1, $columnid1_value, $columname2, $columnid2_value, $condition_array) {

        // if edit than $columnid2_value use
        if ($columnid2_value != '') {
            $this->db->where($columname2 . " != ", $columnid2_value);
        }

        if (!empty($condition_array)) {
            $this->db->where($condition_array);
        }

        $this->db->where($columname1, $columnid1_value);
        $query = $this->db->get($tablename);
        return $query->result();
    }

    public function selectDataById($table, $id, $filed) {
        $this->db->where($filed, $id);
        // $this->db->where('status', 'Enable');
        if ($sortby != '' && $orderby != "") {
            $this->db->order_by($sortby, $orderby);
        }
        $query = $this->db->get($table);
        if ($query->num_rows() > 0) {

            return $query->result();
        } else {
            return array();
        }
    }

    public function selectRecord($table) {
        $query = $this->db->get($table);
        return $query->row_array();
    }

    function get_all_record($tablename, $data, $sortby, $orderby) {
        $this->db->select($data);
        $this->db->from($tablename);
        //$this->db->where('status', 'Enable');
        if ($sortby != '' && $orderby != "") {
            $this->db->order_by($sortby, $orderby);
        }
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result_array();
        } else {
            return array();
        }
    }

    /*
     * Function Name :selectRecordById
     * Parameters :variables
     * Return :array
     */

    public function selectRecordById($table, $id, $filed) {

        $this->db->where($filed, $id);
        $query = $this->db->get($table);
        return $query->row_array();
    }

    public function selectRecordByName($table, $name, $filed) {

        $this->db->where($filed, $name);
        $query = $this->db->get($table);
        return $query->row_array();
    }

    /*
     * Function Name :saveTableImg
     * Parameters :variables
     * Return :variable
     */

    public function saveTableImg($tablename, $filed, $id, $data) {


        $this->db->where($filed, $id);
        $que = $this->db->update($tablename, $data);
        return $que;
    }

    /*
     * Function Name :checkAddTimeRecord
     * Parameters :variables
     * Return :variable
     */

    public function checkAddTimeRecord($columnVal, $columnName, $table) {

        $this->db->where($columnName, $columnVal);
        $query = $this->db->get($table);
        $num = $query->num_rows();

        if ($num != 0) {
            $res = 1;
        } else {
            $res = 0;
        }
        return $res;
    }

    /*
     * Function Name :checkEditTimeRecord
     * Parameters :variables
     * Return :variable
     */

    public function checkEditTimeRecord($columnVal, $columnName, $table, $id, $tableid) {

        $notequal = '<>';
        $tableId = $tableid . " " . $notequal;

        $this->db->where($tableId, $id);
        $this->db->where($columnName, $columnVal);
        $query = $this->db->get($table);
        $num = $query->num_rows();

        if ($num > 0) {
            $this->db->where($columnName, $columnVal);
            $query = $this->db->get($table);
            $rnum = $query->num_rows();
            if ($rnum > 0) {
                $res = 1;
            } else {
                $res = 0;
            }
        } else {
            $res = 0;
        }

        return $res;
    }

    function getSettingDetails() {
        return $this->db->get('settings')->result_array();
    }

    function select_data_by_allcondition($tablename, $condition_array = array(), $data = '*', $sortby = '', $orderby = '', $limit = '', $offset = '', $join_str = array(), $groupby = '') {
        $this->db->select($data);
        $this->db->from($tablename);
        //if join_str array is not empty then implement the join query
        if (!empty($join_str)) {
            foreach ($join_str as $join) {
                if (!isset($join['join_type'])) {
                    $this->db->join($join['table'], $join['join_table_id'] . '=' . $join['from_table_id']);
                } else {
                    $this->db->join($join['table'], $join['join_table_id'] . '=' . $join['from_table_id'], $join['join_type']);
                }
            }
        }

        //condition array pass to where condition
        $this->db->where($condition_array);


        //Setting Limit for Paging
        if ($limit != '' && $offset == 0) {
            $this->db->limit($limit);
        } else if ($limit != '' && $offset != 0) {
            $this->db->limit($limit, $offset);
        }
        if ($groupby != '') {
            $this->db->group_by($groupby);
        }
        if ($sortby != '' && $orderby != '') {
            $this->db->order_by($sortby, $orderby);
        }

        $query = $this->db->get();
        //if limit is empty then returns total count
        if ($limit == '') {
            $query->num_rows();
        }
        //if limit is not empty then return result array
        return $query->result_array();
    }

    /*
     * This function is to create the data source of the Jquery Datatable
     * 
     * @param $tablename Name of the Table in database
     * @param $datatable_fields Fields in datatable that are available for filtering in datatable andnumber of column and order sequence is must maintan with apearance in datatable and add blank filed for not related to database fileds.
     * @param $condition_array conditions for Query 
     * @param $data The field set to be return to datatables, it can contain any number of fileds
     * @param $request The Get or Post Request Sent from Datatable
     * @param $join_str Join array for Query
     * @param $group_by Group by clause array if present in query
     * @return JSON data for datatable
     */

    function getDataTableSource($tablename, $datatable_fields = array(), $conditions_array = array(), $data = '*', $request, $join_str = array(), $group_by = array()) {

        $output = array();
        //Fields tobe display in datatable
        $this->db->select($data);
        $this->db->from($tablename);
        //Making Join with tables if provided
        if (!empty($join_str)) {
            foreach ($join_str as $join) {
                if (!isset($join['join_type'])) {
                    $this->db->join($join['table'], $join['join_table_id'] . '=' . $join['from_table_id']);
                } else {
                    $this->db->join($join['table'], $join['join_table_id'] . '=' . $join['from_table_id'], $join['join_type']);
                }
            }
        }

        //Conditions for Query  that is defaultly available to Datatable data source.
        if (!empty($conditions_array)) {
            $this->db->where($conditions_array);
        }

        //Applying groupby clause to query
        if (!empty($group_by)) {
            $this->db->group_by($group_by);
        }

        //Total record in query tobe return
        $output['recordsTotal'] = $this->db->count_all_results(NULL, FALSE);

        //Filtering based on the datatable_fileds
        if ($request['search']['value'] != '') {
            $this->db->group_start();
            for ($i = 0; $i < count($datatable_fields); $i++) {
                if ($request['columns'][$i]['searchable'] == true) {
                    $this->db->or_like($datatable_fields[$i], $request['search']['value']);
                }
            }
            $this->db->group_end();
        }

        //Total number of records return after filtering not no of record display on page.
        //It must be counted before limiting the resultset.
        $output['recordsFiltered'] = $this->db->count_all_results(NULL, FALSE);

        //Setting Limit for Paging
        $this->db->limit($request['length'], $request['start']);

        //ordering the query
        if (isset($request['order']) && count($request['order'])) {
            for ($i = 0; $i < count($request['order']); $i++) {
                if ($request['columns'][$request['order'][$i]['column']]['orderable'] == true) {
                    $this->db->order_by($datatable_fields[$request['order'][$i]['column']] . ' ' . $request['order'][$i]['dir']);
                }
            }
        }

        $query = $this->db->get();
        $output['draw'] = $request['draw'];
        $output['data'] = $query->result_array();

        return json_encode($output);
    }

    function getDataTableSource1($tablename, $datatable_fields = array(), $conditions_array = array(), $data = '*', $request, $join_str = array(), $group_by = '') {
        $output = array();

        //Fields tobe display in datatable
        $this->db->distinct();
        $this->db->select($data, FALSE);
        //Making Join with tables if provided
        if (!empty($join_str)) {
            foreach ($join_str as $join) {
                if (!isset($join['join_type'])) {
                    $this->db->join($join['table'], $join['join_table_id'] . '=' . $join['from_table_id']);
                } else {
                    $this->db->join($join['table'], $join['join_table_id'] . '=' . $join['from_table_id'], $join['join_type']);
                }
            }
        }
        //COnditions for Query
        if (!empty($conditions_array)) {
            $this->db->where($conditions_array);
        }
        if ($group_by != '') {
            $this->db->group_by($group_by);
        }


        //Total record in query tobe return
        $output['recordsTotal'] = $this->db->count_all_results($tablename, FALSE);
        //echo $this->db->last_query(); die();
        //Filtering based on the datatable_fileds
        if ($request['search']['value'] != '') {
            $this->db->group_start();
            for ($i = 0; $i < count($datatable_fields); $i++) {
                if ($request['columns'][$i]['searchable'] == 'true') {

                    $this->db->or_like($datatable_fields[$i], $request['search']['value']);
                }
            }
            $this->db->group_end();
        }

        //Total number of records return after filtering not no of record display on page.
        //It must be counted before limiting the resultset.
        $output['recordsFiltered'] = $this->db->count_all_results(NULL, FALSE);

        //Setting Limit for Paging
        $this->db->limit($request['length'], $request['start']);

        //ordering the query
        if (isset($request['order']) && count($request['order'])) {
            for ($i = 0; $i < count($request['order']); $i++) {
                if ($request['columns'][$request['order'][$i]['column']]['orderable'] == 'true') {
                    $this->db->order_by($datatable_fields[$request['order'][$i]['column']] . ' ' . $request['order'][$i]['dir']);
                }
            }
        }

        $query = $this->db->get();
        $output['draw'] = $request['draw'];
        $output['data'] = $query->result_array();

        return json_encode($output);
    }

    // select data using multiple conditions. Only use when you want to create pagination like datatable.
    function select_data_by_condition_with_count($tablename, $condition_array = array(), $data = '*', $sortby = '', $orderby = '', $limit = '', $offset = '', $join_str = array()) {
        $this->db->select($data);
        $this->db->from($tablename);

        //if join_str array is not empty then implement the join query
        if (!empty($join_str)) {
            foreach ($join_str as $join) {
                if ($join['join_type'] == '') {
                    $this->db->join($join['table'], $join['join_table_id'] . '=' . $join['from_table_id']);
                } else {
                    $this->db->join($join['table'], $join['join_table_id'] . '=' . $join['from_table_id'], $join['join_type']);
                }
            }
        }

        //condition array pass to where condition
        $this->db->where($condition_array);

        //Applying groupby clause to query
        if (!empty($group_by)) {
            $this->db->group_by($group_by);
        }

        $output['recordsTotal'] = $this->db->count_all_results(NULL, FALSE);
        //Setting Limit for Paging
        if ($limit != '' && $offset == 0) {
            $this->db->limit($limit);
        } else if ($limit != '' && $offset != 0) {
            $this->db->limit($limit, $offset);
        }
        //order by query
        if ($sortby != '' && $orderby != '') {
            $this->db->order_by($sortby, $orderby);
        }

        $query = $this->db->get();
        //if limit is empty then returns total count
        if ($limit == '') {
            $query->num_rows();
        }
        $output['data'] = $query->result_array();
        return $output;
    }

}
