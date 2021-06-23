<?php
  $tr_class = '';
  $linkClass = 'blue';
  $otherColour = 'blue';
  if (!empty($child)) {
    if ($child === 'last') {
      $tr_class .= ' tableHighlightBorderBottom borderBlue';
    } else {
      $tr_class .= ' tableHighlightBorderCenter borderBlue';
    }
  } else {
    $child = false;
  }
  if (!empty($object['deleted'])) {
    $tr_class .= ' deleted-attribute';
  }
  if (!empty($k)) {
    $tr_class .= ' row_' . h($k);
  }
?>
<tr id = "Attribute_<?php echo h($object['id']); ?>_tr" class="<?php echo $tr_class; ?>" tabindex="0">
    <td class="short">
      <?php echo date('Y-m-d', $object['timestamp']); ?>
    </td>
    <td class="short">
      <?php echo $this->element('/Servers/View/seen_field', array('object' => $object)); ?>
    </td>
    <td class="short">
      <div id = "Attribute_<?php echo $object['id']; ?>_category_placeholder" class = "inline-field-placeholder"></div>
      <div id = "Attribute_<?php echo $object['id']; ?>_category_solid" class="inline-field-solid">
        <?php echo h($object['category']); ?>
      </div>
    </td>
    <td class="short">
      <?php
        if (isset($object['object_relation'])):
      ?>
          <div class="bold"><?php echo h($object['object_relation']); ?>:</div>
      <?php
        endif;
      ?>
      <div></div>
      <div id = "Attribute_<?php echo $object['id']; ?>_type_placeholder" class = "inline-field-placeholder"></div>
      <div id = "Attribute_<?php echo $object['id']; ?>_type_solid" class="inline-field-solid">
        <?php echo h($object['type']); ?>
      </div>
    </td>
    <td id="Attribute_<?php echo h($object['id']); ?>_container" class="showspaces limitedWidth shortish">
      <div id="Attribute_<?php echo $object['id']; ?>_value_placeholder" class="inline-field-placeholder"></div>
      <div id = "Attribute_<?php echo $object['id']; ?>_value_solid" class="inline-field-solid">
        <span <?php if (Configure::read('Plugin.Enrichment_hover_enable') && isset($modules) && isset($modules['hover_type'][$object['type']])) echo 'class="eventViewAttributeHover" data-object-type="Attribute" data-object-id="' . h($object['id']) . '"'?>>
          <?= $this->element('/Events/View/value_field', array('object' => $object, 'linkClass' => $linkClass)); ?>
        </span>
      </div>
    </td>
    <td class="shortish">
      <div class="attributeTagContainer">
        <?php
          if (empty($object['Tag'])) echo "&nbsp;";
          else echo $this->element('ajaxAttributeTags', array('attributeId' => $object['id'], 'attributeTags' => $object['Tag'], 'tagAccess' => false));
        ?>
      </div>
    </td>
    <td class="showspaces bitwider">
      <div id = "Attribute_<?php echo $object['id']; ?>_comment_placeholder" class = "inline-field-placeholder"></div>
      <div id = "Attribute_<?php echo $object['id']; ?>_comment_solid" class="inline-field-solid">
        <?php echo nl2br(h($object['comment'])); ?>&nbsp;
      </div>
    </td>
    <td class="shortish">
      <ul class="inline" style="margin:0px;">
        <?php
          $relatedObject = 'Attribute';
          if (!empty($event['Related' . $relatedObject][$object['id']])) {
            foreach ($event['Related' . $relatedObject][$object['id']] as $relatedAttribute) {
              $relatedData = array('Event info' => $relatedAttribute['info'], 'Correlating Value' => $relatedAttribute['value'], 'date' => isset($relatedAttribute['date']) ? $relatedAttribute['date'] : __('N/A'));
              $popover = '';
              foreach ($relatedData as $k => $v) {
                $popover .= '<span class=\'bold black\'>' . h($k) . '</span>: <span class="blue">' . h($v) . '</span><br />';
              }
              echo '<li style="padding-right: 0px; padding-left:0px;" data-toggle="popover" data-content="' . h($popover) . '" data-trigger="hover"><span>';
              if ($relatedAttribute['org_id'] == $me['org_id']) {
                echo $this->Html->link($relatedAttribute['id'], array('controller' => 'events', 'action' => 'view', $relatedAttribute['id'], true, $event['Event']['id']), array('class' => 'red'));
              } else {
                echo $this->Html->link($relatedAttribute['id'], array('controller' => 'events', 'action' => 'view', $relatedAttribute['id'], true, $event['Event']['id']), array('class' => $otherColour));
              }
              echo "</span></li>";
              echo ' ';
            }
          }
        ?>
      </ul>
    </td>
    <td class="shortish">
      <ul class="inline" style="margin:0px;">
        <?php
          if (!empty($object['Feed'])):
            foreach ($object['Feed'] as $feed):
              $popover = '';
              foreach ($feed as $k => $v):
                if ($k == 'id') continue;
                $popover .= '<span class=\'bold black\'>' . Inflector::humanize(h($k)) . '</span>: <span class="blue">' . h($v) . '</span><br />';
              endforeach;
            ?>
              <li style="padding-right: 0px; padding-left:0px;"  data-toggle="popover" data-content="<?php echo h($popover);?>" data-trigger="hover"><span>
                <?php
                  if ($isSiteAdmin):
                    echo $this->Html->link($feed['id'], array('controller' => 'feeds', 'action' => 'previewIndex', $feed['id']), array('style' => 'margin-right:3px;'));
                  else:
                ?>
                  <span style="margin-right:3px;"><?php echo h($feed['id']);?></span>
                <?php
                  endif;
                endforeach;
                ?>
              </li>
        <?php
          endif;
        ?>
      </ul>
    </td>
    <td class="short">
      <div id = "Attribute_<?php echo $object['id']; ?>_to_ids_placeholder" class = "inline-field-placeholder"></div>
      <div id = "Attribute_<?php echo $object['id']; ?>_to_ids_solid" class="inline-field-solid">
        <?php echo $object['to_ids'] ? __('Yes') : __('No'); ?>
      </div>
    </td>
  </td>
</tr>
