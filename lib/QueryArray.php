<?php
/**
 * @author Evin Weissenberg
 */

class Query_Array {

    public  $query;
    public $data;

    function setQuery($query) {

        $this->query = $query;
        return $this;

    }

    function __get($property) {

        return $this->$property;

    }

    function run() {

        $data = array();
        $query = $this->query;
        $result = mysql_query($query);

        while ($loop = mysql_fetch_assoc($result)) {

            array_push($data, $loop);

        }

        $this->data = $data;

        return $this->data;
    }
}
//$q = new Query_Array();
//$q->setQuery('This is the query')->run();