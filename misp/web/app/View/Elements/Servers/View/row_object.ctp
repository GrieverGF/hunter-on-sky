<?php
  $tr_class = '';
  $linkClass = 'white';
  $currentType = 'denyForm';
  $tr_class = 'tableHighlightBorderTop borderBlue';
  $tr_class .= ' blueRow';
  if (!empty($k)) {
    $tr_class .= ' row_' . h($k);
  }
?>
<tr id = "Object_<?php echo $object['id']; ?>_tr" class="<?php echo $tr_class; ?>" tabindex="0">
  <td class="short">
    <?php echo date('Y-m-d', $object['timestamp']); ?>
  </td>
  <td class="short">
    <?php echo $this->element('/Servers/View/seen_field', array('object' => $object)); ?>
  </td>
  <td colspan="<?php echo $fieldCount -2;?>">
    <span class="bold"><?php echo __('Name');?>: </span><?php echo h($object['name']);?>
    <span class="fa fa-expand useCursorPointer" title="<?php echo __('Expand or Collapse');?>" role="button" tabindex="0" aria-label="<?php echo __('Expand or Collapse');?>" data-toggle="collapse" data-target="#Object_<?php echo h($object['id']); ?>_collapsible"></span>
    <br />
    <div id="Object_<?php echo $object['id']; ?>_collapsible" class="collapse">
      <span class="bold"><?php echo __('Meta-category');?>: </span><?php echo h($object['meta-category']);?><br />
      <span class="bold"><?php echo __('Description');?>: </span><?php echo h($object['description']);?><br />
      <span class="bold"><?php echo __('Template');?>: </span><?php echo h($object['name']) . ' v' . h($object['template_version']) . ' (' . h($object['template_uuid']) . ')'; ?>
    </div>
    <?php
      echo $this->element('/Servers/View/row_object_reference', array(
        'object' => $object
      ));
      if (!empty($object['referenced_by'])) {
        echo $this->element('/Servers/View/row_object_referenced_by', array(
          'object' => $object
        ));
      }
    ?>
  </td>
  <td>&nbsp;</td>
  <td>&nbsp;</td>
</tr>
<?php
  if (!empty($object['Attribute'])) {
    end($object['Attribute']);
    $lastElement = key($object['Attribute']);
    foreach ($object['Attribute'] as $attrKey => $attribute) {
      echo $this->element('/Servers/View/row_' . $attribute['objectType'], array(
        'object' => $attribute,
        'page' => $page,
        'fieldCount' => $fieldCount,
        'child' => $attrKey == $lastElement ? 'last' : true
      ));
    }
  }
?>
