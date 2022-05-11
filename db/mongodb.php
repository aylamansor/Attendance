<?php
  //database server//
  $MongodbConnectionString="mongodb://admin:TempPassword@139.99.97.227:27017/gngbazzar?authSource=admin&readPreference=primary&appname=MongoDB%20Compass&directConnection=true&ssl=false";
  $MongodbDatabase = new MongoDB\Driver\Manager($MongodbConnectionString);
?>