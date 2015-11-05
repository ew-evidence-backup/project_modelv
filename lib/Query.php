<?php

/**
 * @author Evin Weissenberg
 */

class Query {

    public $query;
    public $data;

    /**
     * @param $query
     * @return Query
     */
    function setQuery($query) {

        $this->query = (string)$query;

        return $this;
    }

    /**
     * @param $property
     * @return mixed
     */
    function __get($property) {
        return $this->$property;
    }

    /**
     * @return array
     */
    function run() {

        $query = $this->query;
        $run = mysql_query($query) or mysql_error();
        $this->data = mysql_fetch_assoc($run);


    }
}