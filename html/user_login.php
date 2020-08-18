<?php
    require_once 'db_login.php';
    $conn = new mysqli($hn, $un, $pw, $db);
    if ($conn->connect_error) die($conn->connect_error);

    $message="";
    if (
        isset($_POST['employee_id']) &&
        isset($_POST['password'])
    ){
        $employee_id = get_post($conn, 'employee_id');
        $password = get_post($conn, 'password');

        $query  = "SELECT * FROM Users WHERE employee_id='" . $employee_id . "' and password='". $password ."'";
        $result = $conn->query($query);
        if (!$result) {
            die($conn->error);
            echo 'error';
        }
        $count = $result->num_rows;
        
    	if($count==0) {
    		$message = "Invalid Username or Password!";
    	}
    }

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
                        </div>
                    </header>
                    <main>
_END;

if($message) {
    echo '<div id="form-error">';
    echo '<p>' . $message . '</p>';
    echo '</div>';
}
    
echo <<<_END
                        <div id="full-width-box">
                            <form class="login" name="user_auth" method="post" action="index.php">
                                <table>
                                    <tr>
                                        <td>Emoployee ID:</td>
                                        <td><input type="text" name="employee_id" required></td>
                                    </tr>
                                    <tr>
                                        <td>Password:</td>
                                        <td><input type="text" name="password" required></td>
                                    </tr>
                                    <tr>
                                        <td><input id="button" type="submit"></input></td>
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
?>