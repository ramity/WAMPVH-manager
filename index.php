<?php
//setting up variables from config
if(file_exists('config.txt'))
{
  $file = file('config.txt');

  for($z=0;$z<count($file);$z++)
  {
    $file[$z] = trim($file[$z]);
  }

  $httd_url = str_replace('/','\\',$file[1]);
  $hosts_url = str_replace('/','\\',$file[3]);
  $vhosts_url = str_replace('/','\\',$file[5]);
}
else
{
  $file = fopen('config.txt','w');

  $default = "httd_url:\r\nC:/wamp/bin/apache/apache2.4.9/conf/httpd.conf\r\nhosts_url:\r\nC:/Windows/System32/drivers/etc/hosts\r\nvhosts_url:\r\nC:/wamp/bin/apache/apache2.4.9/conf/extra/httpd-vhosts.conf";

  fwrite($file, $default);

  $file = file('config.txt');

  $httd_url = str_replace('/','\\',$file[1]);
  $hosts_url = str_replace('/','\\',$file[3]);
  $vhosts_url = str_replace('/','\\',$file[5]);
}

//setting variables
$vhost_enabled = false;
$hosts = [];
$vhosts = [];
$modules = [];

$contents = file_get_contents($httd_url);

$searchfor = 'LoadModule';

$pattern = preg_quote($searchfor, '/');
$pattern = "/^.*$pattern.*\$/m";

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

if($vhost_enabled)
{
  $file = file_get_contents($hosts_url);

  $lines = explode("\n", $file);

  $hosts_count = count($lines);

  for($z=0;$z<count($lines);$z++)
  {
    if(strpos($lines[$z], '#') === false && !empty($lines[$z]) && !ctype_space($lines[$z]))
    {
      $temp_array = [];

      $segments = explode(' ', $lines[$z]);

      for($y=0;$y<count($segments);$y++)
      {
        if(!empty($segments[$y]) && !ctype_space($segments[$y]))
        {
          array_push($temp_array,[$z, $y, $segments[$y]]);
        }
      }
      array_push($hosts, $temp_array);
    }
  }

  if(isset($_POST['hosts-update']) && !empty($_POST['hosts-update']))
  {
    for($z=0;$z<count($lines);$z++)
    {
      $name_a = $z . '-0';
      $name_b = $z . '-1';

      if(isset($_POST[$name_a]) && !empty($_POST[$name_a]) && isset($_POST[$name_b]) && !empty($_POST[$name_b]))
      {
        $lines[$z] = $_POST[$name_a] . ' ' . $_POST[$name_b];
      }
    }

    $file = implode("\n", $lines);

    file_put_contents($hosts_url, $file);

    header('Location: ' . $_SERVER['REQUEST_URI']);
  }

  if(isset($_POST['hosts-create']) && !empty($_POST['hosts-create']))
  {
    $name_a = $hosts_count . '-0';
    $name_b = $hosts_count . '-1';

    if(isset($_POST[$name_a]) && !empty($_POST[$name_a]) && isset($_POST[$name_b]) && !empty($_POST[$name_b]))
    {
      array_push($lines, $_POST[$name_a] . ' ' . $_POST[$name_b]);
    }

    $file = implode("\n", $lines);

    file_put_contents($hosts_url, $file);

    header('Location: ' . $_SERVER['REQUEST_URI']);
  }

  if(isset($_POST['hosts-delete']) && !empty($_POST['hosts-delete']))
  {
    for($z=0;$z<count($lines);$z++)
    {
      $name_a = $z . '-0';
      $name_b = $z . '-1';

      if(isset($_POST[$name_a]) && !empty($_POST[$name_a]) && isset($_POST[$name_b]) && !empty($_POST[$name_b]))
      {
        array_splice($lines, $z, 1);
      }
    }

    $file = implode("\n", $lines);

    file_put_contents($hosts_url, $file);

    header('Location: ' . $_SERVER['REQUEST_URI']);
  }

  $file = file_get_contents($vhosts_url);

  $lines = explode("\n", $file);

  $vhosts_count = count($lines);

  $temp_array = [];

  for($z=0;$z<count($lines);$z++)
  {
    if(strpos($lines[$z], '#') === false && !empty($lines[$z]) && !ctype_space($lines[$z]))
    {
      if(strpos($lines[$z], '<VirtualHost') === false)
      {
        if(strpos($lines[$z], 'DocumentRoot') !== false)
        {
          $temp = explode('DocumentRoot', $lines[$z]);
          $temp = trim($temp[1]);

          array_push($temp_array, [$z, 'DocumentRoot', $temp]);
        }
        elseif(strpos($lines[$z], 'ServerName') !== false)
        {
          $temp = explode('ServerName', $lines[$z]);
          $temp = trim($temp[1]);

          array_push($temp_array, [$z, 'ServerName', $temp]);
        }
        elseif(strpos($lines[$z], '</VirtualHost>') !== false)
        {
          array_push($vhosts, $temp_array);
        }
      }
      else
      {
        $temp_array = [];
      }
    }
  }

  if(isset($_POST['vhosts-update']) && !empty($_POST['vhosts-update']))
  {
    for($z=0;$z<count($lines);$z++)
    {
      $name_a = $z . '-ServerName';
      $name_b = $z . '-DocumentRoot';

      if(isset($_POST[$name_a]) && !empty($_POST[$name_a]))
      {
        $lines[$z] = '  ServerName ' . $_POST[$name_a];
      }

      if(isset($_POST[$name_b]) && !empty($_POST[$name_b]))
      {
        $lines[$z] = '  DocumentRoot ' . $_POST[$name_b];
      }
    }

    $file = implode("\n", $lines);

    file_put_contents($vhosts_url, $file);

    header('Location: ' . $_SERVER['REQUEST_URI']);
  }

  if(isset($_POST['vhosts-create']) && !empty($_POST['vhosts-create']))
  {
    $name_a = ($vhosts_count + 2) . '-ServerName';
    $name_b = ($vhosts_count + 3) . '-DocumentRoot';

    array_push($lines, '<VirtualHost *:80>');
    array_push($lines, '  ServerName ' .  $_POST[$name_a]);
    array_push($lines, '  DocumentRoot ' .  $_POST[$name_b]);
    array_push($lines, '</VirtualHost>');
    array_push($lines, '');

    $file = implode("\n", $lines);

    file_put_contents($vhosts_url, $file);

    header('Location: ' . $_SERVER['REQUEST_URI']);
  }

  if(isset($_POST['vhosts-delete']) && !empty($_POST['vhosts-delete']))
  {
    for($z=0;$z<count($lines);$z++)
    {
      $name_a = $z . '-ServerName';
      //$name_b = $z . '-DocumentRoot';

      if(isset($_POST[$name_a]) && !empty($_POST[$name_a]))
      {
        array_splice($lines, ($z - 1), 5);
      }
    }

    $file = implode("\n", $lines);

    file_put_contents($vhosts_url, $file);

    header('Location: ' . $_SERVER['REQUEST_URI']);
  }
}
?>
<!DOCTYPE html>
  <head>
    <link href='https://fonts.googleapis.com/css?family=Roboto' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" type="text/css" href="main.css">
  </head>
  <body>
    <div id="topbar">
      <div id="topbarname">Virtual Host Configuration Manager</div>
    </div>
    <div id="content">
      <div id="contentinr">
        <?php
        if($vhost_enabled)
        {
          echo '<div class="notice">Success! Module vHost is enabled</div>';

          echo '<div class="inputheader">Hosts editor</div>';

          foreach($hosts as $items)
          {
            echo '<form class="inputholder" method="POST">';

            foreach($items as $item)
            {
              echo '<input type="text" class="full" name="'.$item[0].'-'.$item[1].'" value="'.$item[2].'">';
            }

            echo '<input type="submit" class="submit" name="hosts-update" value="Update">';
            echo '<input type="submit" class="delete" name="hosts-delete" value="Delete">';

            echo '</form>';
          }

          echo '<form class="inputholder" method="POST">';
            echo '<input type="text" class="full" name="'.$hosts_count.'-0" placeholder="URL">';
            echo '<input type="text" class="full" name="'.$hosts_count.'-1" placeholder="Domain">';
            echo '<input type="submit" class="submitbig" name="hosts-create" value="Create">';
          echo '</form>';

          echo '<div class="inputheader">vHosts editor</div>';

          foreach($vhosts as $items)
          {
            echo '<form class="inputholder" method="POST">';

            foreach($items as $item)
            {
              echo '<input type="text" class="full" name="'.$item[0].'-'.$item[1].'" value="'.htmlentities($item[2]).'">';
            }
            echo '<input type="submit" class="submit" name="vhosts-update" value="Update">';
            echo '<input type="submit" class="delete" name="vhosts-delete" value="Delete">';

            echo '</form>';
          }

          echo '<form class="inputholder" method="POST">';
            echo '<input type="text" class="full" name="'.($vhosts_count + 2).'-ServerName" placeholder="ServerName">';
            echo '<input type="text" class="full" name="'.($vhosts_count + 3).'-DocumentRoot" placeholder="DocumentRoot">';
            echo '<input type="submit" class="submitbig" name="vhosts-create" value="Create">';
          echo '</form>';
        }
        else
        {
          echo '<div class="error">Error! Module vHost is not enabled</div>';
        }
        ?>
      </div>
    </div>
  </body>
</html>
