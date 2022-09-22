<!-- Show these admin pages only when the admin is logged in -->
<?php  require '../assets/partials/_admin-check.php';   ?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookings</title>
        <!-- google fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/d8cfbe84b9.js" crossorigin="anonymous"></script>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
    <!-- External CSS -->
    <?php
        require '../assets/styles/admin.php';
        require '../assets/styles/admin-options.php';
        $page="schedule_booking";
    ?>
</head>
<body>
    <!-- Requiring the admin header files -->
    <?php require '../assets/partials/_admin-header.php';?>
<!-- Add, Edit and Delete Bookings -->
<?php
        /*
            1. Check if an admin is logged in
            2. Check if the request method is POST
        */
        if($loggedIn && $_SERVER["REQUEST_METHOD"] == "POST")
        {
            if(isset($_POST["submit"]))
            {
                /*
                    ADDING Bookings
                 Check if the $_POST key 'submit' exists
                */
                // Should be validated client-side
                // echo "<pre>";
                // var_export($_POST);
                // echo "</pre>";
                // die;
                $customer_id = $_POST["customer_id"];
                $bookingdate = $_POST["bookingdate"];
                $bookingday = $_POST["bookingday"];
                $timeslot = $_POST["timeslot"];
                $amount = $_POST["amount"];
                $amount_paid = $_POST["amount_paid"];
                $status = 1;
                //$amount = $_POST["bookAmount"];
                // $dep_timing = $_POST["dep_timing"];
                //$status = $route_source . " &rarr; " . $route_destination;

                $booking_exists = exist_booking($conn, $customer_id, $booking_id,$time_slot_id);
                $booking_added = false;
        
                if(!$booking_exists)
                {
                    // Route is unique, proceed
                    $sql = "INSERT INTO `bookings` (`customer_id`, `booking_date`, `booking_day`, `time_slot_id`, `amount`, `amount_paid`,`status`,`booking_created`) VALUES ('$customer_id', '$bookingdate','$bookingday', '$timeslot', '$amount','$amount_paid','$status', current_timestamp());";

                    $result = mysqli_query($conn, $sql);
                    // Gives back the Auto Increment id
                    $autoInc_id = mysqli_insert_id($conn);
                    // If the id exists then, 
                    if($autoInc_id)
                    {
                        $key = "1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                        $code = "";
                        for($i = 0; $i < 5; ++$i)
                            $code .= $key[rand(0,strlen($key) - 1)];
                        
                        // Generates the unique bookingid
                        $booking_id = $code.$autoInc_id;
                        
                        $query = "UPDATE `bookings` SET `booking_id` = '$booking_id' WHERE `bookings`.`id` = $autoInc_id;";
                        $queryResult = mysqli_query($conn, $query);

                        // TODO: Use the function created in the _function.php module to insert into the BOOK_TIME_SLOT TABLE

                        if(!$queryResult)
                            echo "Not Working";
                    }

                    if($result)
                        $booking_added = true;
                }
    
                if($booking_added)
                {
                    // Show success alert
                    echo '<div class="my-0 alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Successful!</strong> Booking Added
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>';

                    // Update the Seats table
                    $bus_no = get_from_table($conn, "routes", "route_id", $route_id, "bus_no");
                    $seats = get_from_table($conn, "seats", "bus_no", $bus_no, "seat_booked");
                    if($seats)
                    {
                        $seats .= "," . $booked_seat;
                    }
                    else 
                        $seats = $booked_seat;

                    $updateSeatSql = "UPDATE `seats` SET `seat_booked` = '$seats' WHERE `seats`.`bus_no` = '$bus_no';";
                    mysqli_query($conn, $updateSeatSql);
                }
                else{
                    // Show error alert
                    echo '<div class="my-0 alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Error!</strong> Booking already exists
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>';
                }
            }
            if(isset($_POST["edit"]))
            {
                // EDIT BOOKING
                // echo "<pre>";
                // var_export($_POST);
                // echo "</pre>";die;
                $cname = $_POST["cname"];
                $cphone = $_POST["cphone"];
                $id = $_POST["id"];
                $customer_id = $_POST["customer_id"];
                $id_if_customer_exists = exist_customers($conn,$cname,$cphone);

                if(!$id_if_customer_exists || $customer_id == $id_if_customer_exists)
                {
                    $updateSql = "UPDATE `customers` SET
                    `customer_name` = '$cname',
                    `customer_phone` = '$cphone' WHERE `customers`.`customer_id` = '$customer_id';";

                    $updateResult = mysqli_query($conn, $updateSql);
                    $rowsAffected = mysqli_affected_rows($conn);
    
                    $messageStatus = "danger";
                    $messageInfo = "";
                    $messageHeading = "Error!";
    
                    if(!$rowsAffected)
                    {
                        $messageInfo = "No Edits Administered!";
                    }
    
                    elseif($updateResult)
                    {
                        // Show success alert
                        $messageStatus = "success";
                        $messageHeading = "Successfull!";
                        $messageInfo = "Customer details Edited";
                    }
                    else{
                        // Show error alert
                        $messageInfo = "Your request could not be processed due to technical Issues from our part. We regret the inconvenience caused";
                    }
    
                    // MESSAGE
                    echo '<div class="my-0 alert alert-'.$messageStatus.' alert-dismissible fade show" role="alert">
                    <strong>'.$messageHeading.'</strong> '.$messageInfo.'
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>';
                }
                else{
                    // If customer details already exists
                    echo '<div class="my-0 alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Error!</strong> Customer already exists
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>';
                }

            }
            if(isset($_POST["delete"]))
            {
                // DELETE BOOKING
                $id = $_POST["id"];
                $route_id = $_POST["route_id"];
                // Delete the booking with id => id
                $deleteSql = "DELETE FROM `bookings` WHERE `bookings`.`id` = $id";

                $deleteResult = mysqli_query($conn, $deleteSql);
                $rowsAffected = mysqli_affected_rows($conn);
                $messageStatus = "danger";
                $messageInfo = "";
                $messageHeading = "Error!";

                if(!$rowsAffected)
                {
                    $messageInfo = "Record Doesn't Exist";
                }

                elseif($deleteResult)
                {   
                    $messageStatus = "success";
                    $messageInfo = "Booking Details deleted";
                    $messageHeading = "Successfull!";

                    // Update the Seats table
                    $bus_no = get_from_table($conn, "routes", "route_id", $route_id, "bus_no");
                    $seats = get_from_table($conn, "seats", "bus_no", $bus_no, "seat_booked");

                    // Extract the seat no. that needs to be deleted
                    $booked_seat = $_POST["booked_seat"];

                    $seats = explode(",", $seats);
                    $idx = array_search($booked_seat, $seats);
                    array_splice($seats,$idx,1);
                    $seats = implode(",", $seats);

                    $updateSeatSql = "UPDATE `seats` SET `seat_booked` = '$seats' WHERE `seats`.`bus_no` = '$bus_no';";
                    mysqli_query($conn, $updateSeatSql);
                }
                else{

                    $messageInfo = "Your request could not be processed due to technical Issues from our part. We regret the inconvenience caused";
                }

                // Message
                echo '<div class="my-0 alert alert-'.$messageStatus.' alert-dismissible fade show" role="alert">
                <strong>'.$messageHeading.'</strong> '.$messageInfo.'
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
            }
        }
        ?>
        <?php
            $resultSql = "SELECT bookings.booking_id,customers.customer_firstname,customers.customer_lastname,customers.customer_phone,
            bookings.booking_day,time_slot.time_slot,
            bookings.amount,bookings.amount_paid,status.description,bookings.booking_date
            FROM `bookings` JOIN customers ON customers.customer_id = bookings.customer_id
            JOIN `time_slot` ON bookings.time_slot_id = time_slot.time_id
            JOIN `status` ON bookings.status = status.id ORDER BY bookings.booking_date DESC";
                            
            $resultSqlResult = mysqli_query($conn, $resultSql);

            if(!mysqli_num_rows($resultSqlResult)){ ?>
                <!-- Bookings are not present -->
                <div class="container mt-4">
                    <div id="noCustomers" class="alert alert-dark " role="alert">
                        <h1 class="alert-heading">No Bookings Found!!</h1>
                        <p class="fw-light">Be the first person to add one!</p>
                        <hr>
                        <div id="addCustomerAlert" class="alert alert-success" role="alert">
                                Click on <button id="add-button" class="button btn-sm"type="button"data-bs-toggle="modal" data-bs-target="#addModal">ADD <i class="fas fa-plus"></i></button> to add a booking!
                        </div>
                    </div>
                </div>
            <?php }
            else { ?>   
            <section id="booking">
                <div id="head">
                    <h4>Booking Status</h4>
                </div>
                <div id="booking-results">
                    <div>
                        <button id="add-button" class="button btn-sm"type="button"data-bs-toggle="modal" data-bs-target="#addModal">Add Bookings<i class="fas fa-plus"></i></button>
                    </div>
                    <table class="table table-hover table-bordered">
                        <thead>
                            <th>S/N</th>
                            <th>Booking ID</th>
                            <th>Customer Name</th>
                            <th>Customer Phone</th>
                            <th>Booking Day</th>
                            <th>Booked Time Slot</th>
                            <th>Amount</th>
                            <th>Amount Paid</th>
                            <th>Date Booked</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </thead>
                        <?php 
                           $i = 1;
                            while($row = mysqli_fetch_assoc($resultSqlResult))
                            {
                                  //$i++;
                                    // echo "<pre>";
                                    // var_export($row);
                                    // echo "</pre>";
                                 $booking_id = $row["booking_id"];
                                 $customer_fullname = $row["customer_firstname"]. ' '.$row["customer_lastname"] ;
                                 $customer_phone = $row["customer_phone"];
                                 $booking_day = $row["booking_day"];
                                 $time_slot = $row["time_slot"];
                                 $amount = $row["amount"];
                                 $amount_paid = $row["amount_paid"];
                                 $booking_date = $row["booking_date"];
                                 $status = $row["description"];

                               

                                // $customer_name = get_from_table($conn, "customers","customer_id", $customer_id, "customer_firstname");
                                
                                // $customer_phone = get_from_table($conn,"customers","customer_id", $customer_id, "customer_phone");

                                // $bus_no = get_from_table($conn, "routes", "route_id", $route_id, "bus_no");

                                // $route = $row["customer_route"];

                                // $booked_seat = $row["booked_seat"];
                                
                                // $booked_amount = $row["booked_amount"];

                                // $dep_date = get_from_table($conn, "routes", "route_id", $route_id, "route_dep_date");

                                // $dep_time = get_from_table($conn, "routes", "route_id", $route_id, "route_dep_time");

                                // $booked_timing = $row["booking_created"];
                        ?>
                        <tr>
                            <td>
                                <?php 
                                    echo $i++;
                                ?>
                            </td>
                            <td>
                                <?php 
                                    echo $booking_id;
                                ?>
                            </td>
                            <td>
                                <?php 
                                    echo $customer_fullname;
                                ?>
                            </td>
                            <td>
                                <?php 
                                    echo $customer_phone ;
                                ?>
                            </td>
                            <td>
                                <?php 
                                    echo $booking_day;
                                ?>
                            </td>
                            <td>
                                <?php 
                                    echo $time_slot;
                                ?>
                            </td>
                            <td>
                                <?php 
                                    echo $amount;
                                ?>
                            </td>
                            <td>
                                <?php 
                                    echo $amount_paid;
                                ?>
                            </td>
                            <td>
                                <?php 
                                    echo $booking_date;
                                ?>
                            </td>
                            <td>
                                <?php 
                                    echo $status;
                                ?>
                            </td>
                            <td>
                            <button class="button btn-sm edit-button" data-link="<?php echo $_SERVER['REQUEST_URI']; ?>" data-customerid="<?php 
                                                echo $customer_id;?>" data-id="<?php 
                                                echo $id;?>" data-name="<?php 
                                                echo $customer_name;?>" data-phone="<?php 
                                                echo $customer_phone;?>" >Edit</button>
                                <button class="button delete-button btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal" 
                                data-id="<?php 
                                                echo $id;?>" data-bookedseat="<?php 
                                                echo $booked_seat;
                                            ?>" data-routeid="<?php 
                                            echo $route_id;
                                        ?>"> Delete</button>
                            </td>
                        </tr>
                        <?php 
                        }
                    ?>
                    </table>
                </div>
            </section>
            <?php } ?> 
        </div>
    </main>
    <!-- Requiring _getJSON.php-->
    <!-- Will have access to variables 
        1. routeJson
        2. customerJson
        3. seatJson
        4. busJson
        5. adminJson
        6. bookingJSON
    -->
    <?php require '../assets/partials/_getJSON.php';?>
    
    <!-- All Modals Here -->
    <!-- Add Booking Modal -->
    <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Make Bookings</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addBookingForm" action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="POST">
                            <!-- Passing Route JSON -->
                            <input type="hidden" id="routeJson" name="routeJson" value='<?php echo $routeJson; ?>'>
                            <!-- Passing Customer JSON -->
                            <input type="hidden" id="customerJson" name="customerJson" value='<?php echo $customerJson; ?>'>
                            <!-- Passing Seat JSON -->
                            <input type="hidden" id="seatJson" name="seatJson" value='<?php echo $seatJson; ?>'>

                            <div class="mb-3">
                                <label for="destinationSearch" class="form-label">Group/Customer ID</label>

                                <input type="text" class="form-control" id="customer_id" name="customer_id">

                            </div>

                            
                            <div class="mb-3">
                                <label for="cname" class="form-label">Customer Name</label>
                                <input type="text" class="form-control" id="cname" name="cname" readonly>
                            </div>
                            <div class="mb-3">
                                <label for="cphone" class="form-label">Contact Number</label>
                                <input type="tel" class="form-control" id="cphone" name="cphone" readonly>
                            </div>
                            <div class="mb-3">
                                <label for="routeSearch" class="form-label">Booking Date</label>
                                <!-- Search Functionality -->
                                <div class="searchQuery">
                                    <input type="date" class="form-control searchInput" id="bookingdate" name="bookingdate" >
                                </div>
                            </div>
                            <!-- Send the route_id -->
                            <input type="hidden" name="route_id" id="route_id">
                            <!-- Send the departure timing too -->
                            <input type="hidden" name="dep_timing" id="dep_timing">

                            <div class="mb-3">
                                <label for="sourceSearch" class="form-label">Booking Day</label>
                                <!-- Search Functionality -->
                                <div class="searchQuery">
                                    <input type="text" class="form-control searchInput" id="bookingday" name="bookingday" readonly>
                                    <div class="sugg">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="destinationSearch" class="form-label">Time Slot</label>
                                <select name="timeslot" id="timeslot" class="form-control">
                                  <option selected>Choose...</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="destinationSearch" class="form-label">Amount</label>
                                <!-- Search Functionality -->
                                <div class="searchQuery">
                                    <input type="text" class="form-control searchInput" id="amount" name="amount" readonly>
                                    <div class="sugg">
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="destinationSearch" class="form-label">Amount Paid</label>
                                <!-- Search Functionality -->
                                <div class="searchQuery">
                                    <input type="text" class="form-control searchInput" id="amount_paid" name="amount_paid" required>
                                    <div class="sugg">
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-success" name="submit">Submit</button>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <!-- Add Anything -->
                    </div>
                    </div>
                </div>
        </div>
        <!-- Delete Modal -->
        <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel"><i class="fas fa-exclamation-circle"></i></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h2 class="text-center pb-4">
                    Are you sure?
                </h2>
                <p>
                    Do you really want to delete this booking? <strong>This process cannot be undone.</strong>
                </p>
                <!-- Needed to pass id -->
                <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" id="delete-form"  method="POST">
                    <input id="delete-id" type="hidden" name="id">
                    <input id="delete-booked-seat" type="hidden" name="booked_seat">
                    <input id="delete-route-id" type="hidden" name="route_id">
                </form>
            </div>
            <div class="modal-footer d-flex justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="delete-form" name="delete" class="btn btn-danger">Delete</button>
            </div>
            </div>
        </div>
    </div>
    <script src="../assets/scripts/admin_booking.js"></script>
    <!-- Option 1: Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
    <script>
        
        $(document).ready(function() {
            $('#Amount').val('')
           var weekDay = []
           var weekDayPeakTime = []
           var weekDayNonPeakTime = []

           var weekEnd = []
           var weekEndPeakTime = []
           var weekEndNonPeakTime = []
            // =======Deactivating Previous Dates in Calendar=======
            var date = new Date();
            var tday = date.getDate();
            var tmonth = date.getMonth() + 1;
            var year = date.getUTCFullYear()
            if(tday < 10 ){
                  tday = '0' + tday;
            }
            if(tmonth < 10 ){
                  tmonth = '0' + tmonth;    
            }
             var minDate = year + '-' + tmonth + '-' + tday;
             $('#bookingdate').attr('min', minDate)

            //========Getting Customer Information Through the Cust ID using Ajax====
            $('#customer_id').change(function() {   
                var cust_id = $('#customer_id').val()
                //alert(cust_id)
                $.ajax({
                    url:'getCustDetails.php',
                    method:'POST',
                    data:{
                        customer_id : cust_id
                    },
                      dataType: 'JSON',
                      success: function(data){
                         var fullname = data["customer_firstname"] + ' ' + data["customer_lastname"]
                         $('#cname').val(fullname);
                         $('#cphone').val(data["customer_phone"]);
                        //console.log(data)
                      }
                });
            });
                        
            // =========Getting Day Of Week========================
            $('#bookingdate').change(function() {

                var getDate = $('#bookingdate').val();
                
                var from1 = $('#bookingdate').val().split("-");
                var bookingDate  = new Date(from1[0], from1[1]-1, from1[2]);
                var dayOfWeek = bookingDate.getDay()
                if(dayOfWeek == 0){
                    $('#bookingday').val('Sunday')
                }else if(dayOfWeek == 1){
                    $('#bookingday').val('Monday')
                }else if(dayOfWeek == 2){
                    $('#bookingday').val('Tuesday')
                }else if(dayOfWeek == 3){
                    $('#bookingday').val('Wednesday')
                }else if(dayOfWeek == 4){
                    $('#bookingday').val('Thursday')
                }else if(dayOfWeek == 5){
                    $('#bookingday').val('Friday')
                }else if(dayOfWeek == 6){
                    $('#bookingday').val('Saturday')
                }else{
                    $('#bookingday').val('')
                }
            // ======Ajax Call to Get Time Slot for Day Selected==========
                    $('#timeslot').val('');
                    $('#timeslot').find('option').remove();
                    $.ajax({
                    url:'getCustDetails.php',
                    method:'POST',
                    data:{
                        bookingdate : getDate
                    },
                      dataType: 'JSON',
                      success: function(timeslot){
                        //console.log(timeslot);
                        if(timeslot){
                            for(let i=0; i < timeslot.length; i++){
                            $('#timeslot').append(timeslot);
                             
                             $('#timeslot').append('<option value="' + timeslot[i]['time_id']+'">'+ timeslot[i]['time_slot']+'</option>');
                              
                              
                            }
                        };
                       
                      }
                });

	        });


            $('#timeslot').change(function() {
                // ========Week Day Price configuration
                // Peak Time
                weekDay = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"];
                weekDayPeakTime =["1","2","3","4","5","6","7","8","9"]

                // Non Peak Time
                weekDayNonPeakTime =["10","11","12","13","14"]
                // =======================================================

                //=========== WeekEnd Price Configuration===========
                // Peak Time
                weekEnd = ["Sunday", "Saturday"]
                weekEndPeakTime =["1","2","3","4","10","11","12","13","14"]
                
                // Non Peak Time
                weekEndNonPeakTime = ["6","7","8","9"]

                //===============================
                var bookingday = $('#bookingday').val()
                var timeslot = this.value;
                 
                //========== Week End Pricing==================
                var WeekEndPeakTimeResult = weekEndPeakTime.includes(timeslot);
                var WeekEndNonPeakTimeResult = weekEndNonPeakTime.includes(timeslot);
                var weekEndSlot = weekEnd.includes(bookingday);

                //======= Week Day Pricing ==================
                var weekDaySlot = weekDay.includes(bookingday);
                var weekDayPeakTimeResult = weekDayPeakTime.includes(timeslot);
                //alert(weekDayPeakTimeResult)
                
                var weekDayNonPeakTimeResult = weekDayNonPeakTime.includes(timeslot);
                //alert(weekDayNonPeakTimeResult)

                if( (weekEndSlot ==true) && (WeekEndPeakTimeResult == true)){
                     
                      $('#Amount').val('20,000')

                }else if((weekEndSlot ==true) && (WeekEndNonPeakTimeResult ==true)){
                   
                   $('#Amount').val('15,000')

                }else if((weekDaySlot ==true) && (weekDayPeakTimeResult == true)){
                    
                    $('#Amount').val('20,000')

                }else if((weekDaySlot ==true) && (weekDayNonPeakTimeResult == true)){
                   
                    $('#Amount').val('15,000')

                }else{
                    $('#Amount').val('')
                }


                
            
              

            });

        });

    </script>
</body>
</html>