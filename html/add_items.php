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
        echo '<li><input type="submit" name="add_transaction" value="Cash Out"></li>';
        if($view_reports){
            echo '<li><input type="submit" name="reports" value="View Reports"></li>';
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
    }
    if($_GET['add_transaction']){
        $_SESSION['item_array'] = array();
        header("Location: http://lewisla.myweb.cs.uwindsor.ca/60334/project/html/index.php ");
        exit();
    }
    if($_GET['edit_users']){
        $_SESSION['item_array'] = array();
        header("Location: http://lewisla.myweb.cs.uwindsor.ca/60334/project/html/edit_users.php ");
        exit();
    }

echo '<main><div id="full-width-box"><form name="del-items" method="post" action=""><table>';

    if (isset($_POST['delete']) && isset($_POST['item_id'])) {
        foreach($_POST['ids'] as $id1) {
            $query  = "DELETE FROM Item WHERE item_id=$id1";
            $result = $conn->query($query);
            if (!$result) echo "DELETE failed: $query<br>" . $conn->error . "<br><br>"; 
        }
    }
    
    $query  = "SELECT * FROM Item";
    $result = $conn->query($query);
    if (!$result) die($conn->error);
    $rows_1 = $result->num_rows;
    
    $item_num = $rows_1 + 1;
    
    echo '<tr><td><h3>NAME</h3></td><td><h3>TYPE</h3></td><td><h3>PRICE</h3></td></tr>';
    
    for ($j = 0 ; $j < $rows_1 ; ++$j) {
        $result->data_seek($j);
        $row = $result->fetch_array(MYSQLI_ASSOC);
            echo '<tr>';
                echo '<td>' . $row[name] . '</td>';
                echo '<td>' . $row[category] . '</td>';
                echo '<td>' . $row[price] . '</td>';
                echo '<td><input type="checkbox" name="ids[]" value=" ' . $row[item_id] . '"></td>';
            echo '</tr>';
    }
    
    echo '<input type="hidden" name="item_id" value="$item_id">';
    echo '<tr><td><input type="submit" name="delete" value="Delete"></td></tr>';
    
echo '</table></form>';

    if (
        isset($_POST['name'])       &&
        isset($_POST['category'])   &&
        isset($_POST['price'])
    )
    {
        $name       = get_post($conn, 'name');
        $category   = get_post($conn, 'category');
        $price      = get_post($conn, 'price');
        $query      = "INSERT INTO Item VALUES" . "('$item_num', '$create_date', '$name', '$category', '$price')";
        $result     = $conn->query($query);
        
        header( "Location: add_items.php" );
    }

echo <<<_END

                </div>
                <div id="full-width-box">
                    <form name="user_add" method="post" action="">
                        <table>
                            <tr>
                                <td>Name:</td>
                                <td><input type="text" name="name" required></td>
                            </tr>
                            <tr>
                                <td>Category:</td>
                                <td><input type="text" name="category" required></td>
                            </tr>
                            <tr>
                                <td>Price:</td>
                                <td><input type="text" name="price" required></td>
                            </tr>
                            <tr>
                                <td><input type="submit" value="Add Item"></td>
                            </tr>
                        </table>
                    </form>
                </div>
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