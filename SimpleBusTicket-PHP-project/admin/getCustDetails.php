<?php 
$servername = 'localhost';
$username = 'root';
$password = '';
$database = 'sbtbsphp';

$conn = mysqli_connect($servername, $username, $password, $database);

//echo 'HELOOOOOOO';
//$_POST['customer_id'];
      if(isset($_POST['customer_id'])){
            
            $customer_id = $_POST['customer_id'];
            $resultSql = "SELECT `customer_firstname`,`customer_lastname`,`customer_phone` FROM `customers` WHERE `customer_id` ='$customer_id'";                           
            $resultSqlResult = mysqli_query($conn, $resultSql);

            while($rows = mysqli_fetch_array($resultSqlResult)){
                  $data['customer_firstname'] = $rows['customer_firstname'];
                  $data['customer_lastname'] = $rows['customer_lastname'];
                  $data['customer_phone'] = $rows['customer_phone'];
            }

            echo json_encode($data);

      }

 //============Getting Time SLots for selected Date============
 
if(isset($_POST['bookingdate'])){
      $timeslot = [];
     $getDate =  $_POST['bookingdate']; 
     
     //$getDate =  '2022-09-20';
    
    $Sql = "SELECT * FROM `time_slot` LEFT JOIN `booking_timeslot`  ON `time_slot`.`time_id` = `booking_timeslot`.`timeslot_id` AND `book_date` = '$getDate' WHERE `booking_timeslot`.`timeslot_id` IS NULL ORDER BY `time_slot`.`time_id` ASC";                                           
    $SqlResult = mysqli_query($conn, $Sql);
    
    while($row = mysqli_fetch_assoc($SqlResult)){
         array_push($timeslot, [
                     'time_id' => $row['time_id'],
                     'time_slot' => $row['time_slot']
                    ]);
           
         
     
      }
       echo json_encode($timeslot);
    
      
       
    
  } 
 

?>