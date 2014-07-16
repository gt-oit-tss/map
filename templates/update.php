<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8" />
    <title>Cluster Monitoring Suite</title>
    <link rel="stylesheet" href="<?php echo isset($base_url) ? $base_url : ''; ?>/static/css/screen.css" media="screen, projection" />
    <link rel="stylesheet" href="<?php echo isset($base_url) ? $base_url : ''; ?>/static/css/map.css" media="screen, projection" />
    <!--[if lte IE 7]><link rel="stylesheet" href="/static/css/ie.css" media="screen, projection" /><![endif]-->
    <script src="<?php echo isset($base_url) ? $base_url : ''; ?>/static/js/jquery.min.js"></script>
    <!--
    <script src="http://documentcloud.github.com/underscore/underscore-min.js"></script>
    <script src="http://documentcloud.github.com/backbone/backbone-min.js"></script>
    <script src="https://github.com/douglascrockford/JSON-js/raw/master/json2.js"></script>
    -->
  </head>
  <body>
    <div id="header">
      <div class="wrap group">
        <div class="hgroup">
          <h1 class="page-title"><?php echo isset($title) ? $title : 'Cluster Monitoring Suite'; ?></h1>
          <p class="subtitle">Application Updates</span></p>
        </div>
        <ul class="nav global">
        </ul>
      </div>
    </div>
    <div id="content">
      <div class="wrap group">
        <?php if (isset($updates_available) && $updates_available && isset($descriptions)): ?>
        
        <p>The following updates need to be applied:</p>
        <ul>
          <?php foreach($descriptions as $message): ?>
          <li><?php echo $message; ?></li>
          <?php endforeach; ?>
        </ul>
        
        <form method="post" action="">
          <input type="submit" value="Start update" />
        </form>
        <?php else: ?>
        <p>No updates are available. <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/">Continue to homepage</a>.</p>
        <?php endif; ?>
      </div>
    </div>
    <div id="footer">
      <div class="wrap">
        <p class="copyright">
          2008-2011 &copy; <a href="http://www.oit.gatech.edu" target="_blank">Office of Information Technology</a> &mdash; Georgia Institute of Technology
        </p>
      </div>
    </div>
  </body>
</html>