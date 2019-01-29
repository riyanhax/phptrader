<?php
class MyDB extends SQLite3 {
      function __construct() {
         $this->open(__DIR__.'/database.db');
      }
   }
   
$db = new MyDB();
if(!$db){
   echo $db->lastErrorMsg();
} else {
   echo "Opened database successfully\n";
}
function query($sql, $db){
   $ret = $db->exec($sql);
   if(!$ret) {
      echo $db->lastErrorMsg();
   }
   return true;
  
}

?>