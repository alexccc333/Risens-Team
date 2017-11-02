<?php

class LoginForm {
    
    
    public function printForm() {
        echo '<form method="post" action="adminpanel.php">';
        echo '<input type="text" name="login"/>';
        echo '<input type="password" name="password" />';
        echo '<button>Login</button></form>';
    }
    
    public function printLogOut() {
        echo '<a href="?logout">Logout</a>';
    }
}
