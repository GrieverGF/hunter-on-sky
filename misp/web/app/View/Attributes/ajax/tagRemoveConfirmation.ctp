<div class="confirmation">
    <?php
    echo $this->Form->create($model, array('style' => 'margin:0px;', 'id' => 'PromptForm', 'url' => $baseurl . '/' . strtolower($model) . 's/removeTag/' . $id . '/' . $tag_id));
    $action = "removeObjectTag('" . $model . "', '" . h($id) . "', '" . h($tag_id) . "');";
    ?>
    <div style="padding-left:5px;padding-right:5px;padding-bottom:5px;">
    <p><?= __('Remove %s tag %s from %s %s?',
            isset($is_local) ? ($is_local ? __('local') : __('global')) : '',
            $this->element('tag', ['tag' => $tag]),
            str_replace('_', ' ', strtolower($model)),
            h($model_name))
        ?>
    </p>
        <table>
            <tr>
                <td style="vertical-align:top">
                    <span id="PromptYesButton" class="btn btn-primary" title="<?php echo __('Remove'); ?>" role="button" tabindex="0" aria-label="<?php echo __('Remove'); ?>" onClick="<?php echo $action; ?>"><?php echo __('Yes'); ?></span>
                </td>
                <td style="width:540px;">
                </td>
                <td style="vertical-align:top;">
                    <span class="btn btn-inverse" id="PromptNoButton" title="<?php echo __('Cancel'); ?>" role="button" tabindex="0" aria-label="<?php echo __('Cancel'); ?>" onClick="cancelPrompt();"><?php echo __('No'); ?></span>
                </td>
            </tr>
        </table>
    </div>
    <?php
        echo $this->Form->end();
    ?>
</div>
