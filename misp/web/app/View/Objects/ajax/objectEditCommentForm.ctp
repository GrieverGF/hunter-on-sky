<?php
    echo $this->Form->create('Object', array('class' => 'inline-form inline-field-form', 'id' => 'Object_' . $object['id'] . '_comment_form', 'url' => $baseurl . '/objects/editField/' . $object['id']));
?>
    <div class="inline-input inline-input-container">
    <div class="inline-input-accept inline-input-button inline-input-passive"><span class="fas fa-check" role="button" tabindex="0" aria-label="<?php echo __('Accept change'); ?>"></span></div>
    <div class="inline-input-decline inline-input-button inline-input-passive"><span class="fas fa-times" role="button" tabindex="0" aria-label="<?php echo __('Discard change'); ?>"></span></div>
        <?php
            echo $this->Form->input('comment', array(
                    'type' => 'textarea',
                    'label' => false,
                    'value' => $object['comment'],
                    'error' => array('escape' => false),
                    'class' => 'inline-input',
                    'id' => 'Object' . '_' . $object['id'] . '_comment_field',
                    'div' => false
            ));
            echo $this->Form->end();
?>
    </div>
