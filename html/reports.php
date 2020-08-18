<?php
session_start();

    require_once 'db_login.php';
    $conn = new mysqli($hn, $un, $pw, $db);
    if ($conn->connect_error) die($conn->connect_error);
    
    if( !isset($_POST['employee_id']) && $_SESSION['employee_id'] == "") {
        $_SESSION['item_array'] = array();
        header("Location: http://lewisla.myweb.cs.uwindsor.ca/60334/project/html/user_login.php");
        exit();
    }
    
    $create_date = date('Y-m-d H:i:s');
    if($_POST['employee_id']) {
        $_SESSION['employee_id']  = $_POST['employee_id'];
    }
    
    $query  = "SELECT * FROM Users WHERE employee_id='" . $_SESSION['employee_id'] . "'";
    $result = $conn->query($query);
    if (!$result) {
        die($conn->error);
        echo 'error';
    }
    $row = $result->fetch_array(MYSQLI_ASSOC);
    $f_name = $row[first_name];
    $l_name = $row[last_name];
    $u_type = $row[user_type];
    
    $query  = "SELECT * FROM Permission WHERE user_type='" . $u_type . "'";
    $result = $conn->query($query);
    if (!$result) {
        die($conn->error);
        echo 'error';
    }
    $row = $result->fetch_array(MYSQLI_ASSOC);
    
    $edit_users = $row[edit_users];
    $view_reports = $row[view_reports];
    $edit_transactions = $row[edit_transactions];
    $add_items = $row[add_items];
    
echo <<<_END

        <!DOCTYPE html>
        <html>
            <head>
                <title>RedBird Cafe</title>
                <link rel="stylesheet" type="text/css" href="../css/style.css">
                <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed&display=swap" rel="stylesheet">
_END;
?>
<script src="https://www.gstatic.com/charts/loader.js"></script> <!-- Script source to pull gstatic table from server-->
    <script type="text/javascript">
        google.charts.load('current', {'packages':['corechart']});
        google.charts.setOnLoadCallback(drawChart);
        
        function drawChart() {
            var data = google.visualization.arrayToDataTable([
                ['employee_id', 'count']
                <?php
                    $query = "SELECT employee_id,COUNT(*) AS 'cnt' FROM `Transactions` WHERE date_added >= '" . $_SESSION['trans_date_curr'] . "' and date_added < '" . $_SESSION['trans_date_next'] . "' group by employee_id";
                    
                    $result = $conn->query($query);
                    
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo ",['{$row['employee_id']}',{$row['cnt']}]\r\n";
                    }
                ?>
            ]);
            
            var options = {title: 'Transactions by Employee'};
            
            var chart = new google.visualization.PieChart(document.getElementById('piechart1'));
            chart.draw(data, options);
        }
    </script>
    <script type="text/javascript">
        google.charts.load('current', {'packages':['corechart']});
        google.charts.setOnLoadCallback(drawChart);
        
        function drawChart() {
            var data = google.visualization.arrayToDataTable([
                ['total', 'count']
                <?php
                
                    $query = "SELECT total,COUNT(*) AS 'cnt' FROM `Transactions` WHERE date_added >= '" . $_SESSION['trans_date_curr'] . "' and date_added < '" . $_SESSION['trans_date_next'] . "' group by employee_id";
                    
                    $result = $conn->query($query);
                    
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo ",['{$row['total']}',{$row['cnt']}]\r\n";
                    }
                ?>
            ]);
            
            var options = {title: 'Transactions by Total'};
            
            var chart = new google.visualization.PieChart(document.getElementById('piechart2'));
            chart.draw(data, options);
        }
    </script>
<?php
echo <<<_END
            </head>
            <body>
                <header>
                    <div id="header-content">
                        <div class="left-header-box">
                            <img class="logo" src="../images/RedBirdLogo.png" width="150px"/>
                            <img class="logo-type" src="../images/RedBirdLogotype.png" width="150px"/>
                        </div>
                        <div class="right-header-box">
_END;

echo '<div class="current-user"><p>' . $f_name . ' ' . $l_name . ' - Employee # ' . $_SESSION['employee_id'] . '</p></div>';
echo '<form id="inline" name="menu" method="get" action="">';
    if($_GET['menu']) {
        echo '<input class="menu" type="submit" name="close" value="close">';
    }
    else {
        echo '<input class="menu" type="submit" name="menu" value="menu">';
    }
echo '</form>';

echo <<<_END
                        </div>
                    </div>
                </header>
_END;

    if($_GET['menu']){
        echo '<nav>';
    }
    else {
        echo '<nav id="hidden">';
    }
    
    echo '<form name="menu-items" method="get" action=""><ul>';
        echo '<li><input type="submit" name="add_transaction" value="Cash Out"></li>';
        if($add_items){
            echo '<li><input type="submit" name="add_items" value="Add New Items"></li>';
        }
        if($edit_users){
            echo '<li><input type="submit" name="edit_users" value="Edit Users"></li>';
        }
        echo '<li><input type="submit" name="logout" value="Logout"></li>';
    echo '</ul></form></nav>';
    
    if($_GET['logout']) {
        $_SESSION['item_array'] = array();
        $_SESSION['employee_id'] = "";
        header("Location: http://lewisla.myweb.cs.uwindsor.ca/60334/project/html/user_login.php");
        exit();
    }
    if($_GET['add_items']){
        $_SESSION['item_array'] = array();
        header("Location: http://lewisla.myweb.cs.uwindsor.ca/60334/project/html/add_items.php");
    }
    if($_GET['edit_users']){
        $_SESSION['item_array'] = array();
        header("Location: http://lewisla.myweb.cs.uwindsor.ca/60334/project/html/edit_users.php");
        exit();
    }
    if($_GET['add_transaction']){
        $_SESSION['item_array'] = array();
        header("Location: http://lewisla.myweb.cs.uwindsor.ca/60334/project/html/index.php ");
        exit();
    }

echo '<main><div id="left-box">';
    
    echo '<form class="item-menu" name="date_select" method="post" action=""><table>';
    
        $date_format = array();
    
        $query  = "SELECT * FROM Transactions";
        $result = $conn->query($query);
        if (!$result) die($conn->error);
        $rows = $result->num_rows;
        
        echo '<td>Date:</td>';
        echo '<td><select name="trans_date">';
        for ($j = 0 ; $j < $rows ; ++$j) {
            $result->data_seek($j);
            $row = $result->fetch_array(MYSQLI_ASSOC);
            
            echo $row[date_added];
            
            if ($row[date_added] == $j+1)
            {
                $selected = 'selected="selected"';
            }
            else
            {
                $selected = '';
            }
            
            $curr_date = date_parse_from_format('Y-m-d h:i:s', $row[date_added]);
            $day_month = $curr_date['day'].$curr_date['month'];
            
            if(!in_array($day_month, $date_format)) {
                echo'<option value="' . $row[date_added] . '" ' . $selected . '>' . $curr_date['day'] . '-' . $curr_date['month'] . '-' . $curr_date['year'] . '</option>';
                array_push($date_format, $day_month);
            }
        }
               
        echo '</select></td>';
        
        echo '<td><input type="submit" name="search" value="Search"></td>';
        if($_GET['search']) {
            header( "Location: reports.php" );
        }
    
    echo '</table></form>';
    
    if(isset($_POST['trans_date'])) {
        $today = date_parse_from_format('Y-m-d h:i:s', $_POST['trans_date']);
        $_SESSION['trans_date_curr'] = $today['year'] . '-' . $today['month'] . '-' . $today['day'];
        $nextday = $today['day'] + 1;
        $_SESSION['trans_date_next'] = $today['year'] . '-' . $today['month'] . '-' . $nextday;
    }
    
_END;
    
    if($_SESSION['trans_date']) {
        
        echo '<div class="item-menu"><form name="trans_select" method="get" action=""><table>';
    
        $query  = "SELECT * FROM Transactions WHERE date_added >= '" . $_SESSION['trans_date_curr'] . "' and date_added < '" . $_SESSION['trans_date_next'] . "'";
        $result = $conn->query($query);
        if (!$result) die($conn->error);
        $rows_1 = $result->num_rows;
        
        echo '<tr>';
        
        for ($j = 0 ; $j < $rows_1 ; ++$j) {
            $result->data_seek($j);
            $row = $result->fetch_array(MYSQLI_ASSOC);
                echo '<input class="item-select" type="submit" name="trans_detail" value="' . $row[transaction_id] . '">';
        }
        
        echo '</tr>';
        echo '</form>';
        
        
        if($_GET['trans_detail']) {
            echo '</table></div><div class="item-list"><table>';
            
            
            $query  = "SELECT * FROM Transactions WHERE transaction_id ='" . $_GET['trans_detail'] . "'";
            $result = $conn->query($query);
            if (!$result) die($conn->error);
            $rows_1 = $result->num_rows;
            
            echo '<h2>Details</h2>';
            echo '<tr><td><h3>TRANS NUM</h3></td><td><h3>EMP ID</h3></td><td><h3>TOTAL</h3></td></tr>';
            
            for ($j = 0 ; $j < $rows_1 ; ++$j) {
                $result->data_seek($j);
                $row = $result->fetch_array(MYSQLI_ASSOC);
                    echo '<tr>';
                        echo '<td>' . $row[transaction_id] . '</td>';
                        echo '<td>' . $row[employee_id] . '</td>';
                        echo '<td>' . $row[total] . '</td>';
                    echo '</tr>';
            }
            
            echo '<table>';
            echo '<h2>Items</h2>';
            
            $trans_items = array();
            
            $query  = "SELECT * FROM transaction_items WHERE transaction_id ='" . $_GET['trans_detail'] . "'";
            $result = $conn->query($query);
            if (!$result) die($conn->error);
            $rows_1 = $result->num_rows;
            
            for ($j = 0 ; $j < $rows_1 ; ++$j) {
                $result->data_seek($j);
                $row = $result->fetch_array(MYSQLI_ASSOC);
                    array_push($trans_items, $row[item_id]);
            }
            
            echo '<tr><td><h3>NAME</h3></td><td><h3>CATEGORY</h3></td><td><h3>PRICE</h3></td></tr>';
            
            for($a = 0 ; $a < sizeof($trans_items) ; ++$a) {
                $query  = "SELECT * FROM Item WHERE item_id='" . trim($trans_items[$a]) . "'";
                $result = $conn->query($query);
                if (!$result) die($conn->error);
                $rows_3 = $result->num_rows;
                
                echo '<tr>';
                
                for ($k = 0 ; $k < $rows_3 ; ++$k) {
                    $result->data_seek($k);
                    $row = $result->fetch_array(MYSQLI_ASSOC);
                
                    echo '<td>' . $row[name] . '</td>';
                    echo '<td>' . $row[category] . '</td>';
                    echo '<td>' . $row[price] . '</td>';
                    $_SESSION['total'] = $_SESSION['total'] + $row[price];
                }
                
                echo '</tr>';
            }
        }
            
        echo '</table></div>';
    }
    
    echo '</div>';
    echo '<div id="right-box">';
    
        echo '<div class="transaction-title"><h2>Report for ' . $_SESSION['trans_date_curr'] . '</h2>';
        echo '</div><div class="transaction-details"><div id="piechart1"></div><div id="piechart2"></div>';
        echo '</div><div class="transaction-footer">';
    
    echo '</div></div>';

echo <<<_END
            </main>
            <footer>
                
            </footer>
        </body>
    </html>

_END;

    $result->close();
    $conn->close();

    function get_post($conn, $var)
    {
    return $conn->real_escape_string($_POST[$var]);
    }
    
exit();
?>