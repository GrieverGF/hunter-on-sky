<?php
    echo $this->Form->create('Attribute', array('class' => 'inline-form inline-field-form', 'id' => 'Attribute_' . $object['id'] . '_category_form', 'url' => $baseurl . '/attributes/editField/' . $object['id']));
?>
<div class='inline-input inline-input-container'>
<div class="inline-input-accept inline-input-button inline-input-passive"><span class="fas fa-check" role="button" tabindex="0" aria-label="<?php echo __('Accept change'); ?>"></span></div>
<div class="inline-input-decline inline-input-button inline-input-passive"><span class="fas fa-times" role="button" tabindex="0" aria-label="<?php echo __('Discard change'); ?>"></span></div>
<?php
    echo $this->Form->input('category', array(
        'options' => array(array_combine($typeCategory[$object['type']], $typeCategory[$object['type']])),
        'label' => false,
        'selected' => $object['category'],
        'error' => array('escape' => false),
        'class' => 'inline-input',
        'id' => 'Attribute_' . $object['id'] . '_category_field',
        'div' => false
    ));
    echo $this->Form->end();
?>
</div>
