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
        if($view_reports){
            echo '<li><input type="submit" name="reports" value="View Reports"></li>';
        }
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
    if($_GET['reports']){
        $_SESSION['item_array'] = array();
        header("Location: http://lewisla.myweb.cs.uwindsor.ca/60334/project/html/reports.php");
        exit();
    }
    if($_GET['add_items']){
        $_SESSION['item_array'] = array();
        header("Location: http://lewisla.myweb.cs.uwindsor.ca/60334/project/html/add_items.php");
        exit();
    }
    if($_GET['edit_users']){
        $_SESSION['item_array'] = array();
        header("Location: http://lewisla.myweb.cs.uwindsor.ca/60334/project/html/edit_users.php");
        exit();
    }

echo '<main><div id="left-box"><form class="item-menu" name="item_select" method="get" action=""><table>';
    
    $query  = "SELECT DISTINCT category FROM Item";
    $result = $conn->query($query);
    if (!$result) die($conn->error);
    $rows_1 = $result->num_rows;
    
    echo '<tr>';
    
    for ($j = 0 ; $j < $rows_1 ; ++$j) {
        $result->data_seek($j);
        $row = $result->fetch_array(MYSQLI_ASSOC);
            echo '<td><input class="item-select" type="submit" name="category" value=" ' . $row[category] . '"></td>';
    }
    
    echo '</table></form>';
    
    if($_GET['category']){
        $_SESSION['category'] = $_GET['category'];
    }
    
    echo '</tr>';

echo '<form class="item-list" name="item_select" method="get" action="">';

    $query  = "SELECT * FROM Item WHERE category='" . trim($_SESSION['category']) . "'";
    $result = $conn->query($query);
    if (!$result) die($conn->error);
    $rows_2 = $result->num_rows;
    
    for ($i = 0 ; $i < $rows_2 ; ++$i) {
        $result->data_seek($i);
        $row = $result->fetch_array(MYSQLI_ASSOC);
            echo '<div class="item"><input type="submit" name="item" value=" ' . $row[name] . '"><p>' . $row[price] . '</p></div>';
    }

echo '</form></div>';

    if($_GET['item']){
        if(!isset($_SESSION['item_array'])){
            $_SESSION['item_array'] = $_GET['item'];
        }
        else {
            array_push($_SESSION['item_array'], $_GET['item']);
        }
    }

echo '<div id="right-box"><div class="transaction-title">';

    $query  = "SELECT * FROM Transactions";
    $result = $conn->query($query);
    if (!$result) die($conn->error);
    $count = $result->num_rows;
    
    $transaction_num = $count + 1;

    echo '<p> Transaction #: ' . $transaction_num . '</p><p>Employee #: ' . $_SESSION['employee_id'] . '</p>';

echo '</div><div class="transaction-details"><table>';

    $_SESSION['total'] = 0;

    for($a = 0 ; $a < sizeof($_SESSION['item_array']) ; ++$a) {
        $query  = "SELECT * FROM Item WHERE name='" . trim($_SESSION['item_array'][$a]) . "'";
        $result = $conn->query($query);
        if (!$result) die($conn->error);
        $rows_3 = $result->num_rows;
        
        echo '<tr>';
        
        for ($k = 0 ; $k < $rows_3 ; ++$k) {
            $result->data_seek($k);
            $row = $result->fetch_array(MYSQLI_ASSOC);
        
            echo '<td>' . $row[name] . '</td>';
            echo '<td></td>';
            echo '<td>' . $row[price] . '</td>';
            $_SESSION['total'] = $_SESSION['total'] + $row[price];
        }
        
        echo '</tr>';
    }

echo '</table></div><form class="transaction-footer" name="transaction_end" method="get" action=""><table>';

    echo '<tr>';
    echo '<td>Total: ' . $_SESSION['total'] . '</td>';
    echo '<td><input type="submit" name="transaction_end" value="Paid"></td>';
    
    if($_GET['transaction_end']) {
        $query = "INSERT INTO Transactions VALUES" . "('$transaction_num', '$create_date', '" . $_SESSION['employee_id'] . "', '" . $_SESSION['total'] . "')";
        $result = $conn->query($query);
        
        for($a = 0 ; $a < sizeof($_SESSION['item_array']) ; ++$a) {
            $query  = "SELECT * FROM Item WHERE name='" . trim($_SESSION['item_array'][$a]) . "'";
            $result = $conn->query($query);
            if (!$result) die($conn->error);
            $rows_3 = $result->num_rows;
            
            for ($k = 0 ; $k < $rows_3 ; ++$k) {
                $result->data_seek($k);
                $row = $result->fetch_array(MYSQLI_ASSOC);
            
                $query = "INSERT INTO transaction_items VALUES" . "('" . $row[item_id] . "', '$transaction_num')";
                $result = $conn->query($query);
            }
        }
        
        $_SESSION['item_array'] = array();
        header( "Location: index.php" );
    }
    
    echo '</tr>';
    
echo '</table></form></div>';

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