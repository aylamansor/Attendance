<!-- begin::Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous">


<div class="m-5 text-center">
<a class="btn btn-dark" href="index.php?insert=yes">INSERT DATA</a>
</div>


<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include 'db/mongodb.php';

// $filter = ['_id'=>new \MongoDB\BSON\ObjectId('62665eaf9c5c49681ca42f3f'),'IN1'=> ['$exists'=> true], 'OUT1'=> ['$exists'=> true]];
$filter = ['IN1'=> ['$exists'=> true], 'OUT1'=> ['$exists'=> true]];
$query = new MongoDB\Driver\Query($filter);
$cursor = $MongodbDatabase->executeQuery('GoNGetzSmartSchool.TempAttendance',$query);                  
foreach ($cursor as $document)
{	
    $TempAttendance = strval($document->_id);
    $UserID = $document->UserID;
    $Name = $document->Name;
    $Dept = $document->Dept;
    $Date = $document->Date; //18/4/2022
    $Shift = $document->Shift;
    $IN1 = $document->IN1; //9:23
    $OUT1 = $document->OUT1; //20:00

    list($day, $month, $year) = explode("/",$Date); //18-day 4-month 2020-year

    //IN
    list($hours, $min) = explode(":", $IN1); //9-hours 23-min
    $Date_in = $year."-".$month."-".$day."\T".$hours.":".$min.":00"; //20-hours 00-min

    $Date_in = date($Date_in); //2022-4-18T9:08:00
    echo $Date_in;
    $Date_in = new MongoDB\BSON\UTCDateTime((new DateTime($Date_in))->getTimestamp()*1000); //1650244080000

    //OUT
    list($hours, $min) = explode(":", $OUT1);
    $Date_out = $year."-".$month."-".$day."\T".$hours.":".$min.":00";
    $Date_out = date($Date_out);
    $Date_out = new MongoDB\BSON\UTCDateTime((new DateTime($Date_out))->getTimestamp()*1000);

    //$Date = date_format($Date,"Y-m-d\TH:i:s");
    // echo $Date_new."<br>".$IN1."<br>".$OUT1."<br>";

    $AttendanceRemark_id = "";
    $filter = ['AttendanceRemark'=>$TempAttendance];
    $query = new MongoDB\Driver\Query($filter);
    $cursor = $MongodbDatabase->executeQuery('GoNGetzSmartSchool.Attendance',$query);                  
    foreach ($cursor as $document)
    {	
        $AttendanceRemark_id = $document->AttendanceRemark;
    }

    if($AttendanceRemark_id != $TempAttendance)
    {
        if (isset($_GET['insert']))
        {
            $bulk = new MongoDB\Driver\BulkWrite(['ordered' => TRUE]);
            $bulk->insert([
                "SchoolID"=> $Dept,
                "CardID"=> $Name,
                "AttendanceDate"=> $Date_in,
                "AttendanceRemark"=> $TempAttendance
            ]);
            
            $writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 1000);
            try
            {
                $result=$MongodbDatabase->executeBulkWrite('GoNGetzSmartSchool.Attendance', $bulk, $writeConcern);
            }
            catch (MongoDB\Driver\Exception\BulkWriteException $e)
            {
                $result = $e->getWriteResult();
                // Check if the write concern could not be fulfilled
                if ($writeConcernError = $result->getWriteConcernError())
                {
                    printf("%s (%d): %s\n",
                        $writeConcernError->getMessage(),
                        $writeConcernError->getCode(),
                        var_export($writeConcernError->getInfo(), true)
                    );
                }
                // Check if any write operations did not complete at all
                foreach ($result->getWriteErrors() as $writeError)
                {
                    printf("Operation#%d: %s (%d)\n",
                        $writeError->getIndex(),
                        $writeError->getMessage(),
                        $writeError->getCode()
                    );
                }
            }
            catch (MongoDB\Driver\Exception\Exception $e)
            {
                printf("Other error: %s\n", $e->getMessage());
                exit;
            }

            $bulk = new MongoDB\Driver\BulkWrite(['ordered' => TRUE]);
            $bulk->insert([
                "SchoolID"=> $Dept,
                "CardID"=> $Name,
                "AttendanceDate"=> $Date_out,
                "AttendanceRemark"=> $TempAttendance
            ]);
            
            $writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 1000);
            try
            {
                $result=$MongodbDatabase->executeBulkWrite('GoNGetzSmartSchool.Attendance', $bulk, $writeConcern);
            }
            catch (MongoDB\Driver\Exception\BulkWriteException $e)
            {
                $result = $e->getWriteResult();
                // Check if the write concern could not be fulfilled
                if ($writeConcernError = $result->getWriteConcernError())
                {
                    printf("%s (%d): %s\n",
                        $writeConcernError->getMessage(),
                        $writeConcernError->getCode(),
                        var_export($writeConcernError->getInfo(), true)
                    );
                }
                // Check if any write operations did not complete at all
                foreach ($result->getWriteErrors() as $writeError)
                {
                    printf("Operation#%d: %s (%d)\n",
                        $writeError->getIndex(),
                        $writeError->getMessage(),
                        $writeError->getCode()
                    );
                }
            }
            catch (MongoDB\Driver\Exception\Exception $e)
            {
                printf("Other error: %s\n", $e->getMessage());
                exit;
            }
        }
    }
    
}

?>
<table id="attendance" class="table table-bordered text-center p-3 mb-5 rounded">
    <thead class="bg-white text-dark">
        <tr>
            <th>Date</th>
            <th>IN</th>
            <th>OUT</th>
        </tr>
    </thead>
    <tbody class="bg-white text-danger">
        <tr>
            <td class="default"><?php
            $varcounting = 0;
            $filter = ['AttendanceRemark'=>'62665eaf9c5c49681ca42f3f'];
            $option = ['limit' => 1];
            $query = new MongoDB\Driver\Query($filter,$option);
            $cursor = $MongodbDatabase->executeQuery('GoNGetzSmartSchool.Attendance',$query);
            foreach ($cursor as $document)
            {
                $AttendanceDate = $document->AttendanceDate;
                $AttendanceDate = new MongoDB\BSON\UTCDateTime(strval($AttendanceDate));
                $AttendanceDate = $AttendanceDate->toDateTime()->setTimezone(new \DateTimeZone(date_default_timezone_get()));
                echo date_format($AttendanceDate,"Y-m-d")."<br>";

            }
            ?></td>
            <td class="default"><?php
            $filter = ['AttendanceRemark'=>'62665eaf9c5c49681ca42f3f'];
            $option = ['sort' => ['_id' => 1],'limit' => 1];
            $query = new MongoDB\Driver\Query($filter,$option);
            $cursor = $MongodbDatabase->executeQuery('GoNGetzSmartSchool.Attendance',$query);
            foreach ($cursor as $document)
            {
                $Attendance_in = $document->AttendanceDate;
                $Attendance_in = new MongoDB\BSON\UTCDateTime(strval($Attendance_in));
                $Attendance_in = $Attendance_in->toDateTime()->setTimezone(new \DateTimeZone(date_default_timezone_get()));

                echo date_format($Attendance_in,"H:i:s")."<br>";
            }
            ?></td>
            <td class="default"><?php
            $filter = ['AttendanceRemark'=>'62665eaf9c5c49681ca42f3f'];
            $option = ['sort' => ['_id' => -1],'limit' => 1];
            $query = new MongoDB\Driver\Query($filter,$option);
            $cursor = $MongodbDatabase->executeQuery('GoNGetzSmartSchool.Attendance',$query);
            foreach ($cursor as $document)
            {
                $Attendance_out = $document->AttendanceDate;
                $Attendance_out = new MongoDB\BSON\UTCDateTime(strval($Attendance_out));
                $Attendance_out = $Attendance_out->toDateTime()->setTimezone(new \DateTimeZone(date_default_timezone_get()));

                echo date_format($Attendance_out,"H:i:s")."<br>";
            }
            ?></td>
        </tr>
    </tbody>
</table>