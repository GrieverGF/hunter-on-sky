<?php
  $tr_class = 'redRow';
  $linkClass = 'white';
  $currentType = 'denyForm';
  if (!empty($objectContainer)) {
    if (!empty($child)) {
      if ($child === 'last') {
        $tr_class .= ' tableInsetOrangeLast';
      } else {
        $tr_class .= ' tableInsetOrangeMiddle';
      }
    } else {
      $tr_class .= ' tableInsetOrange';
    }
    if ($child === 'last') {
      $tr_class .= ' tableHighlightBorderBottom borderBlue';
    } else {
      $tr_class .= ' tableHighlightBorderCenter borderBlue';
    }
  } else {
    if (!empty($child)) {
      if ($child === 'last') {
        $tr_class .= ' tableHighlightBorderBottom borderOrange';
      } else {
        $tr_class .= ' tableHighlightBorderCenter borderOrange';
      }
    } else {
      $tr_class .= ' tableHighlightBorder borderOrange';
    }
  }
  $identifier = (empty($k)) ? '' : ' id="row_' . h($k) . '" tabindex="0"';
?>
<tr id="<?php echo $currentType . '_' . $object['id'] . '_tr'; ?>" class="<?php echo $tr_class; ?>" <?php echo $identifier; ?>>
  <?php
    if ($mayModify):
  ?>
      <td style="width:10px;" data-position="<?php echo h($object['objectType']) . '_' . h($object['id']); ?>">
          <input id="select_proposal_<?php echo $object['id']; ?>" class="select_proposal row_checkbox" type="checkbox" data-id="<?php echo $object['id'];?>" />
      </td>
  <?php
    endif;
  ?>
  <td class="short context hidden">
    <?php echo h($object['id']); ?>
  </td>
  <td class="short context hidden">
    <?php echo h($object['uuid']); ?>
  </td>
  <td class="short context hidden">
      <?php echo $this->element('/Events/View/seen_field', array('object' => $object)); ?>
  </td>
  <td style="font-weight:bold;text-align:left;"><?= __('DELETE') ?></td>
  <?php
    if ($extended):
  ?>
    <td class="short">
      <?php echo '<a href="' . $baseurl . '/events/view/' . h($object['event_id']) . '" class="white">' . h($object['event_id']) . '</a>'; ?>
    </td>
  <?php
    endif;
  ?>
  <td class="short">
  <?php
    if (isset($object['Org']['name'])) {
      echo $this->OrgImg->getOrgImg(array('name' => $object['Org']['name'], 'id' => $object['Org']['id'], 'size' => 24));
    }
  ?>
  </td>
  <td colspan="<?php echo $fieldCount; ?>">&nbsp;</td>
  <td class="short action-links">
    <?php
        if (($event['Orgc']['id'] == $me['org_id'] && $mayModify) || $isSiteAdmin) {
          echo $this->Form->create('Shadow_Attribute', array('id' => 'ShadowAttribute_' . $object['id'] . '_accept', 'url' => $baseurl . '/shadow_attributes/accept/' . $object['id'], 'style' => 'display:none;'));
          echo $this->Form->end();
        ?>
          <span class="fas fa-check white useCursorPointer" title="<?php echo __('Accept Proposal');?>" role="button" tabindex="0" aria-label="<?php echo __('Accept proposal');?>" onClick="acceptObject('shadow_attributes', '<?php echo $object['id']; ?>', '<?php echo $event['Event']['id']; ?>');"></span>
        <?php
        }
        if (($event['Orgc']['id'] == $me['org_id'] && $mayModify) || $isSiteAdmin || ($object['org_id'] == $me['org_id'])) {
        ?>
          <span class="fa fa-trash white useCursorPointer" title="<?php echo __('Discard proposal');?>" role="button" tabindex="0" aria-label="<?php echo __('Discard proposal');?>" onClick="deleteObject('shadow_attributes', 'discard' ,'<?php echo $object['id']; ?>', '<?php echo $event['Event']['id']; ?>');"></span>
        <?php
        }
    ?>
  </td>
</tr>
