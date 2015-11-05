<?php ob_start();

/**
 * @author Evin Weissenberg 2013
 */


mysql_connect('localhost', 'econline_mv', 'KeHG9.C9,n0b') or die(mysql_error());
mysql_select_db('econline_mv') or die(mysql_error());


include 'lib/Query.php';
include 'lib/Satitize.php';

$s = new Sanitize();
$data = $s->cleanArray($_REQUEST);

$q = new Query();
$go = $q->setQuery("UPDATE mv_users SET user_type='".$data['type']."' WHERE ID=".$data['ID'])->run();


header('Location: /user/admin/');


