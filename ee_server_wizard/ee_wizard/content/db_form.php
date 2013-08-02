<?php if ( ! defined('SERVER_WIZ')) exit('No direct script access allowed');?>

<form method='post' action='./index.php?wizard=run'>

<div class="shade">
<h2>Enter Your Database Settings for MySQL</h2>

<h5>MySQL Server Address</h5>
<p>Usually you will use 'localhost', but your hosting provider may require something else</p>
<p><input type='text' name='db_hostname' value='<?php echo $db_hostname; ?>' size='40' class='input' /></p>


<h5>MySQL Username</h5>
<p>The username you use to access your MySQL database</p>
<p><input type='text' name='db_username' value='<?php echo $db_username; ?>' size='40' class='input' /></p>


<h5>MySQL Password</h5>
<p>The password you use to access your MySQL database</p>
<p><input type='password' name='db_password' value='<?php echo $db_password; ?>' size='40' class='input' /></p>


<h5>MySQL Database Name</h5>
<p>The name of the database where you want ExpressionEngine installed.</p>
<p class="red">Note: ExpressionEngine's Server Wizard will not create the database for you so you must specify the name of a database that exists.</p>
<p><input type='text' name='db_name' value='<?php echo $db_name; ?>' size='40' class='input' /></p>

</div>
<input type="submit" name="submit" value="Check my server!" />
</form>