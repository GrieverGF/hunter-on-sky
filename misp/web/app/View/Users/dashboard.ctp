<div class="Dashboard index">
    <h2><?php echo __('Dashboard');?></h2>
        <div class="row">
            <div class="span3 dashboard_container">
            <?php
                echo $this->element('dashboard/dashboard_notifications');
            ?>
            </div>
            <div class="span3 dashboard_container">
            <?php
                echo $this->element('dashboard/dashboard_events');
            ?>
            </div>
        </div>
</div>
<?php
    echo $this->element('/genericElements/SideMenu/side_menu', array('menuList' => 'globalActions', 'menuItem' => 'dashboard'));
?>
