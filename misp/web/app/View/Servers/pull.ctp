<div class="servers index">
    <h2><?php echo __('Failed pulls');?></h2>
    <?php
if (0 == count($fails)):?>
    <p><?php echo __('No failed pulls');?></p>
    <?php
else:?>
    <ul>
    <?php foreach ($fails as $key => $value) echo '<li>' . $key . ' : ' . h($value) . '</li>'; ?>
    </ul>
    <?php
endif;?>
    <h2><?php echo __('Succeeded pulls');?></h2>
    <?php
if (0 == count($successes)):?>
    <p><?php echo __('No succeeded pulls');?></p>
    <?php
else:?>
    <ul>
    <?php foreach ($successes as $success) echo '<li>' . $success . '</li>'; ?>
    </ul>
    <?php
endif;?>
    <h2><?php echo __('Proposals pulled');?></h2>
    <?php
if (0 == count($pulledProposals)):?>
    <p><?php echo __('No proposals pulled');?></p>
    <?php
else:?>
    <ul>
    <?php foreach ($pulledProposals as $e => $p) echo '<li>Event ' . $e . ' : ' . $p . ' proposal(s).</li>'; ?>
    </ul>
    <?php
endif;?>
    <h2><?php echo __('Sightings pulled');?></h2>
    <?php
if (0 == count($pulledSightings)):?>
    <p><?php echo __('No sightings pulled');?></p>
    <?php
else:?>
    <ul>
    <?php foreach ($pulledSightins as $e => $p) echo '<li>Event ' . $e . ' : ' . $p . ' sighting(s).</li>'; ?>
    </ul>
    <?php
endif;?>

</div>
<?php
    echo $this->element('/genericElements/SideMenu/side_menu', array('menuList' => 'sync', 'menuItem' => 'pull'));
?>
