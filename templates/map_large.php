<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8" />
    <title>Cluster Monitoring Suite</title>
    <link rel="stylesheet" href="<?php echo isset($base_url) ? $base_url : ''; ?>/static/css/screen.css" media="screen, projection" />
    <link rel="stylesheet" href="<?php echo isset($base_url) ? $base_url : ''; ?>/static/css/map_large.css" media="screen, projection" />
    <!--[if lte IE 7]><link rel="stylesheet" href="/static/css/ie.css" media="screen, projection" /><![endif]-->
    <script src="<?php echo isset($base_url) ? $base_url : ''; ?>/static/js/jquery-1.6.min.js"></script>
    <script src="<?php echo isset($base_url) ? $base_url : ''; ?>/static/js/underscore-min.js"></script>
    <script src="<?php echo isset($base_url) ? $base_url : ''; ?>/static/js/backbone-min.js"></script>
    <script src="<?php echo isset($base_url) ? $base_url : ''; ?>/static/js/json2.js"></script>
    <script src="<?php echo isset($base_url) ? $base_url : ''; ?>/static/js/application.js"></script>
  </head>
  <body>
    <div id="header">
      <div class="wrap group">
        <div class="hgroup">
          <h1 class="page-title"><?php echo isset($title) ? $title : 'Cluster Monitoring Suite'; ?></h1>
          <p class="subtitle">Last Updated: <span><?php echo date("D M d Y H:i:s \\G\\M\\TO (T)"); ?></span></p>
        </div>
        <ul class="nav global">
          <li><h3>Choose another map:</h3></li>
          <li><a href="<?php echo isset($base_url) ? $base_url : ''; ?>/maps/lwc?large=true" data-map-link="lwc">LWC</a></li>
          <li><a href="<?php echo isset($base_url) ? $base_url : ''; ?>/maps/lec?large=true" data-map-link="lec">LEC</a></li>
        </ul>
      </div>
    </div>
    <div id="content" data-basepath="<?php echo isset($base_url) ? $base_url : ''; ?>" data-should-cyle="false">
      <div class="wrap group">
        <?php if($computers): ?>
        <div id="display-map" data-location="<?php echo strtolower($map); ?>">
          <dl class="map_<?php echo strtolower($map); ?>">
            <?php foreach($computers as $computer): ?>
            <dt>
              <a class="<?php echo $computer['status']; ?>" id="<?php echo strtolower($computer['name']); ?>" title="<?php echo $computer['name']; ?> (<?php echo $computer['status']; ?>)">
                <?php echo $computer['name']; ?> (<?php echo $computer['status']; ?>)
              </a>
            </dt>
            <?php endforeach; ?>
          </dl>
        </div>
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
    <script type="text/template" id="computer-template">
      <dt>
        <a class="<%= status %>" id="<% print(name.toLowerCase()); %>" title="<%= name %> (<%= status %>)">
          <%= name %> (<%= status %>)
        </a>
      </dt>
    </script>
  </body>
</html>
