<?php
$vhost_enabled = false;

$contents = file_get_contents('D:\wamp\bin\apache\apache2.4.9\conf\httpd.conf');

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
  echo 'vhost_enabled';
  echo '<br><br>';
}

$hosts = [];

//viewing hosts file for windows machine
$contents = file_get_contents('C:\Windows\System32\drivers\etc\hosts');

$lines = explode("\n",$contents);

echo '<form action="config.php" method="post">';

for($z=0;$z<count($lines);$z++)
{
  $line = $lines[$z];

  if(strpos($line,'#') === false && !empty($line) && !ctype_space($line))
  {
    $line_parts = explode(' ',$line);

    if(isset($_POST['edithostfile'])&&!empty($_POST['edithostfile']))
    {

    }

    $temp_array = [];

    for($y=0;$y<count($line_parts);$y++)
    {
      $part = $line_parts[$y];

      //gets values seperated by white space
      if($part)
      {
        $name = $z.'-'.$y;

        if(isset($_POST['edithostfile'])&&!empty($_POST['edithostfile']))
        {
          array_push($temp_array,$_POST[$name]);

          $part = $_POST[$name];
        }

        echo '<input type="text" name="'.$name.'" value="'.$part.'">';
      }
    }

    $name = $z.'-delete';

    echo '<input type="text" name="'.$name.'" value="0">';

    if(isset($_POST['edithostfile'])&&!empty($_POST['edithostfile']))
    {
      if(isset($_POST[$name]) && !empty($_POST[$name]) && $_POST[$name])
      {
        $lines[$z] = '';
      }
      else
      {
        $lines[$z] = implode(" ",$temp_array);
      }
    }

    echo '<br>';
    echo '<br>';

    array_push($hosts,[$z,$line]);
  }
}

echo $contents = implode("\n",$lines);

echo '<br>';
echo '<br>';

echo '<input type="text" name="'.count($lines).'-0" placeholder="ip">';
echo '<input type="text" name="'.count($lines).'-1" placeholder="url">';

echo '<br>';
echo '<br>';

echo '<input type="submit" name="edithostfile" value="submit">';
echo '</form>';

echo '<br>';
?>
