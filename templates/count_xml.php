<?xml version='1.0'?>
<data>
<?php foreach($locations as $location => $statuses): ?>
  <cluster>
    <name><?php echo strtoupper($location); ?></name>
    <?php foreach($statuses as $status => $count): ?>
    <status>
        <name><?php echo ucwords($status); ?></name>
        <count><?php echo $count; ?></count>
      </status>
    <?php endforeach; ?>
  </cluster>
<?php endforeach; ?>
</data>