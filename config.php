<?php
//USER INPUT VARIABLES
$httd_url = 'C:\wamp\bin\apache\apache2.4.9\conf\httpd.conf';
$hosts_url = 'C:\Windows\System32\drivers\etc\hosts';
$vhosts_url = 'C:\wamp\bin\apache\apache2.4.9\conf\extra\httpd-vhosts.conf';

$vhost_enabled = false;

$contents = file_get_contents($httd_url);

$searchfor = 'LoadModule';

$pattern = preg_quote($searchfor, '/');
$pattern = "/^.*$pattern.*\$/m";

$modules = [];

//compiling list of all enabled modules
if(preg_match_all($pattern, $contents, $matches))
{
  foreach($matches[0] as $line)
  {
    if(strpos($line,'#') === false && !empty($line))
    {
      array_push($modules,$line);
    }
  }
}
else
{
  die('An error has occured');
}

//searching to check if vhost_alias_module is enabled
foreach($modules as $module)
{
  $bits = explode(' ',$module);
  if($bits[1] === 'vhost_alias_module')
  {
    $vhost_enabled = true;
  }
}

//do stuff if vhost_alias_module is enabled
if($vhost_enabled)
{
  if(isset($_POST['file_input_a']) && !empty($_POST['file_input_a']))
  {
    file_put_contents($hosts_url,$_POST['file_input_a']);

    $contents = $_POST['file_input_a'];
  }
  else
  {
    $contents = file_get_contents($hosts_url);
  }

  echo '<body>';

    echo 'vhost_enabled';

    echo '<br>';
    echo '<br>';

    //viewing hosts file for windows machine
    echo '<form action="config.php" method="post" style="width:auto;">';

      echo '<textarea name="file_input_a" cols="100" rows="30">'.$contents.'</textarea>';

      echo '<br>';
      echo '<br>';

      echo '<input type="submit" name="edithostfile_a" value="submit">';
    echo '</form>';

  if(isset($_POST['file_input_b']) && !empty($_POST['file_input_b']))
  {
    file_put_contents($vhosts_url,$_POST['file_input_b']);

    $contents = $_POST['file_input_b'];
  }
  else
  {
    $contents = file_get_contents($vhosts_url);
  }

    //viewing hosts file for windows machine
    echo '<form action="config.php" method="post" style="width:auto;">';

      echo '<textarea name="file_input_b" cols="100" rows="30">'.$contents.'</textarea>';

      echo '<br>';
      echo '<br>';

      echo '<input type="submit" name="edithostfile_b" value="submit">';
    echo '</form>';

  echo '</body>';
}
?>
