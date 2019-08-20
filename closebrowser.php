<?php

    include('../../config.php');

    $mac_de = $mac_fr = $red = '';

    if ((strpos($_SERVER['HTTP_USER_AGENT'], 'Macintosh') !== false) || isset($_GET['mac'])) {
        $mac_img = '<img src="pix/command-q.png" alt="command-Q" />';
        $mac_fr = '<p>Sur un ordinateur Macintosh, fermer la fenêtre n\'est pas suffisant : il faut complètement quitter le navigateur. Pour cela, maintenez la touche &quot;Commande&quot; (Pomme) enfoncée, puis tapez &quot;Q&quot; :</p><p style="text-align:center;">'.$mac_img.'</p>';
	    $mac_de = ' <p>Auf einem Macintosh-Computer ist es nicht ausreichend, das Fenster zu schliessen: Sie m&uuml;ssen den Web-Browser schliessen (beenden). Um dies zu erreichen, halten Sie die "Command"-Taste (Apple) gedr&uuml;ckt, und tippen Sie dann einen "Q" :</p><p style="text-align:center;">'.$mac_img.'</p>';
        $red = ' style="color:red;"';
    }


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="fr" xml:lang="fr">
    <head>
      <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
      <meta name="keywords" content="moodle, UniFR" />
      <title>Université de Fribourg - Moodle - Universität Freiburg</title>
      <link rel="shortcut icon" href="<?php echo $CFG->wwwroot; ?>/theme/clean_unifr/pix/favicon.ico" />
    </head>
    <body>
      <div style="margin:0px auto auto auto; width:400px; padding: 20px 50px 10px 50px; background-color:#eee; border:5px #666 solid; font-size:12pt;">
        <p style="font-size:16pt;"><strong>Veuillez <span<?php echo $red; ?>>quitter le navigateur</span> pour terminer complètement votre session.</strong></p>
        <?php echo $mac_fr; ?>
        <hr style="margin:30px;">
        <p style="font-size:16pt;"><strong>Bitte <span<?php echo $red; ?>>schliessen Sie Ihren Web-Browser</span> um Ihre Sitzung komplett zu beenden.</strong></p>
        <?php echo $mac_de; ?>
      </div>
    </body>
</html>
