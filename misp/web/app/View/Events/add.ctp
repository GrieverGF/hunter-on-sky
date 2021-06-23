<?php
    $modelForForm = 'Event';
    $action = $this->request->params['action'];
    echo $this->element('genericElements/Form/genericForm', array(
        'form' => $this->Form,
        'data' => array(
            'title' => $action === 'add' ? __('Add Event') : __('Edit Event'),
            'model' => $modelForForm,
            'fields' => array(
                array(
                    'field' => 'org_id',
                    'class' => 'org-id-picker-hidden-field',
                    'type' => 'text',
                    'hidden' => true
                ),
                array(
                    'field' => 'date',
                    'class' => 'datepicker',
                    'type' => 'text',
                    'stayInLine' => 1
                ),
                array(
                    'field' => 'distribution',
                    'class' => 'input',
                    'options' => $distributionLevels,
                    'default' => isset($event['Event']['distribution']) ? $event['Event']['distribution'] : $initialDistribution,
                    'stayInLine' => 1
                ),
                array(
                    'field' => 'sharing_group_id',
                    'class' => 'input',
                    'options' => $sharingGroups,
                    'label' => __("Sharing Group")
                ),
                array(
                    'field' => 'threat_level_id',
                    'class' => 'input',
                    'options' => $threatLevels,
                    'label' => __("Threat Level"),
                    'stayInLine' => 1
                ),
                array(
                    'field' => 'analysis',
                    'class' => 'input',
                    'options' => $analysisLevels
                ),
                array(
                    'field' => 'info',
                    'label' => __('Event Info'),
                    'class' => 'input span6',
                    'type' => 'text',
                    'placeholder' => __('Quick Event Description or Tracking Info')
                ),
                array(
                    'field' => 'extends_uuid',
                    'class' => 'input span6',
                    'placeholder' => __('Event UUID or ID. Leave blank if not applicable.'),
                    'label' => __("Extends Event"),
                    'default' => isset($extends_uuid) ? $extends_uuid : ''
                ),
                '<div id="event_preview" style="width:446px;"></div>'
            ),
            'submit' => array(
                'action' => $action
            )
        )
    ));
    echo $this->element('/genericElements/SideMenu/side_menu', array(
        'menuList' => $action === 'add' ? 'event-collection' : 'event',
        'menuItem' => $action === 'add' ? 'add' : 'editEvent',
        'event' => isset($event) ? $event : null,
    ));
?>

<script type="text/javascript">
    $('#EventDistribution').change(function() {
        checkSharingGroup('Event');
    });

    $(function() {
        checkSharingGroup('Event');
        $("#EventExtendsUuid").keyup(delay(function() {
            previewEventBasedOnUuids($(this).val());
        }, 100));
        previewEventBasedOnUuids($("#EventExtendsUuid").val());
    });
</script>
<?php echo $this->Js->writeBuffer();
