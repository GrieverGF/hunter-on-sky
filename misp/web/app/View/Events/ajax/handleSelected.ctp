<div class="confirmation">
    <?php
    echo $this->Form->create($model, array('style' => 'margin:0px;', 'id' => 'PromptForm', 'url' => $url));
    echo $this->Form->input($varName, array(
        'type' => 'text',
        'value' => 'test',
        'style' => 'display:none;',
        'label' => false,
    ));
    ?>
    <legend><?php echo h(Inflector::humanize($action)); ?></legend>
    <div style="padding-left:5px;padding-right:5px;padding-bottom:5px;">
        <p><?php echo h($message); ?></p>
        <table>
            <tr>
                <td style="vertical-align:top">
                    <span role="button" tabindex="0" aria-label="<?php echo __('Yes');?>" title="<?php echo __('Yes');?>" id="PromptYesButton" class="btn btn-primary" onClick="multiSelectAction('<?php echo h($id); ?>', '<?php echo h($action); ?>');"><?php echo __('Yes');?></span>
                </td>
                <td style="width:540px;">
                </td>
                <td style="vertical-align:top;">
                    <span class="btn btn-inverse" id="PromptNoButton" role="button" tabindex="0" aria-label="<?php echo __('No');?>" title="<?php echo __('No');?>" onClick="cancelPrompt();"><?php echo __('No');?></span>
                </td>
            </tr>
        </table>
    </div>
    <?php
        echo $this->Form->end();
    ?>
</div>
