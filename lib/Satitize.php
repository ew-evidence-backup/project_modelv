<?php


/**
 * @author Evin Weissenberg
 */

class Sanitize {

    private $variable;

    function setVariable($variable) {

        $this->variable = $variable;
        return $this;

    }

    function getVariable($property) {

        return $this->$property;

    }

    function clean() {

        $clean = mysql_real_escape_string($this->variable);

        return $clean;

    }

    function cleanArray($array)
    {
        foreach($array as $key=>$value)
        {
            $array[$key] = mysql_real_escape_string($value);
        }

        return $array;
    }

}

//$s = new Sanitize();
//$s->setVariable('String')->clean();