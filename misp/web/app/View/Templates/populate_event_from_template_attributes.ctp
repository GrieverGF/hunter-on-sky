<div class="index">
    <h2><?php echo __('Populate From Template Results');?></h2>
    <p><?php echo __('Below you can see the attributes that are to be created based on the data that you have entered into the template. If you are satisfied with the result, click "Finalise". Otherwise, click "Modify".');?></p>
    <table class="table table-striped table-hover table-condensed">
    <tr>
            <th><?php echo __('Category');?></th>
            <th><?php echo __('Type');?></th>
            <th><?php echo __('Value');?></th>
            <th><?php echo __('Comment');?></th>
            <th><?php echo __('IDS');?></th>
            <th><?php echo __('Distribution');?></th>
    </tr><?php
foreach ($attributes as $item):?>
    <tr>
        <td><?php echo h($item['category']); ?></td>
        <td><?php echo h($item['type']); ?></td>
        <td><?php echo h($item['value']); ?></td>
        <td><?php echo h($item['comment']); ?></td>
        <td><?php echo ($item['to_ids'] ? 'Yes' : 'No'); ?></td>
        <td><?php echo $distributionLevels[$item['distribution']]; ?></td>
    </tr><?php
endforeach;?>
    </table>
    <div style="float:left;">
        <?php echo $this->Form->create('Template', array('url' => $baseurl . '/templates/submitEventPopulation/' . $template_id . '/' . $event_id));?>
            <fieldset>
                <?php
                    echo $this->Form->input('attributes', array(
                            'id' => 'attributes',
                            'label' => false,
                            'type' => 'hidden',
                            'value' => json_encode($attributes),
                    ));
                ?>
            </fieldset>
        <?php
        echo $this->Form->button(__('Finalise'), array('class' => 'btn btn-primary'));
        echo $this->Form->end();
        ?>
    </div>
    <div style="float:left;width:10px;">&nbsp;</div>
    <div>
        <?php echo $this->Form->create('Template');?>
            <fieldset>
                <?php
                    foreach ($template['Template'] as $k => $v) {
                        if (strpos($k, 'ile_')) $v = serialize($v);
                        echo $this->Form->input($k, array(
                            'label' => false,
                            'type' => 'hidden',
                            'value' => $v,
                        ));
                    }
                    echo $this->Form->input('modify', array(
                            'label' => false,
                            'type' => 'hidden',
                            'value' => true,
                    ));
                    echo $this->Form->input('errors', array(
                            'label' => false,
                            'type' => 'hidden',
                            'value' => serialize($errors),
                    ));
                    echo $this->Form->input('fileArray', array(
                            'label' => false,
                            'type' => 'hidden',
                            'value' => $fileArray,
                    ));
                ?>
            </fieldset>
        <?php
        echo $this->Form->button('Modify', array('class' => 'btn btn-inverse'));
        echo $this->Form->end();
        ?>
    </div>

</div>
<?php
    echo $this->element('/genericElements/SideMenu/side_menu', array('menuList' => 'event', 'menuItem' => 'template_populate_results'));
?>
