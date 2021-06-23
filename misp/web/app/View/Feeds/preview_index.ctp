<div class="events index">
<h4 class="visibleDL notPublished" ><?php echo __('You are currently viewing the event index of a feed (%s by %s).', h($feed['Feed']['name']),h($feed['Feed']['provider']));?></h4>
    <div class="pagination">
        <ul>
        <?php
            $eventViewURL = $baseurl . '/feeds/previewEvent/' . h($id) . '/';
            $this->Paginator->options(array(
                'url' => $id,
            ));
            echo $this->Paginator->prev('&laquo; ' . __('previous'), array('tag' => 'li', 'escape' => false), null, array('tag' => 'li', 'class' => 'prev disabled', 'escape' => false, 'disabledTag' => 'span'));
            echo $this->Paginator->numbers(array('modulus' => 20, 'separator' => '', 'tag' => 'li', 'currentClass' => 'red', 'currentTag' => 'span'));
            echo $this->Paginator->next(__('next') . ' &raquo;', array('tag' => 'li', 'escape' => false), null, array('tag' => 'li', 'class' => 'next disabled', 'escape' => false, 'disabledTag' => 'span'));
        ?>
        </ul>
    </div>
    <?php
        $data = array(
            'children' => array(
                array(
                    'type' => 'search',
                    'button' => __('Filter'),
                    'placeholder' => __('Enter value to search'),
                    'data' => '',
                )
            )
        );
        if (!$ajax) {
            echo $this->element('/genericElements/ListTopBar/scaffold', array('data' => $data));
        }
    ?>

    <table class="table table-striped table-hover table-condensed">
        <tr>
            <th class="filter"><?php echo $this->Paginator->sort('Org', __('Org')); ?></th>
            <th class="filter"><?php echo __('Tags');?></th>
            <th class="filter"><?php echo $this->Paginator->sort('date', null, array('direction' => 'desc'));?></th>
            <th class="filter" title="<?php echo $eventDescriptions['threat_level_id']['desc'];?>"><?php echo $this->Paginator->sort('threat_level_id');?></th>
            <th class="filter" title="<?php echo $eventDescriptions['analysis']['desc']; ?>"><?php echo $this->Paginator->sort('analysis');?></th>
            <th class="filter"><?php echo $this->Paginator->sort('info');?></th>
            <th class="filter"><?php echo $this->Paginator->sort('timestamp', __('Timestamp'), array('direction' => 'desc'));?></th>
            <th class="actions"><?php echo __('Actions');?></th>

        </tr>
        <?php if (!empty($events)) foreach ($events as $uuid => $event): ?>
        <tr>
            <td class="short" ondblclick="document.location.href ='<?php echo $eventViewURL . h($uuid);?>'"><?php echo h($event['Orgc']['name']); ?></td>
            <td style = "max-width: 200px;width:10px;">
                <?php foreach ($event['Tag'] as $tag): ?>
                    <span class=tag style="margin-bottom:3px;background-color:<?php echo isset($tag['colour']) ? h($tag['colour']) : 'red';?>;color:<?php echo $this->TextColour->getTextColour(isset($tag['colour']) ? h($tag['colour']) : 'red');?>;" title="<?php echo h($tag['name']); ?>"><?php echo h($tag['name']); ?></span>
                <?php endforeach; ?>
            </td>
            <td class="short" ondblclick="document.location.href ='<?php echo $eventViewURL . h($uuid);?>'">
                <?php echo h($event['date']); ?>&nbsp;
            </td>
            <td class="short" ondblclick="document.location.href ='<?php echo $eventViewURL . h($uuid);?>'">
                <?php
                    echo h($threatLevels[isset($event['threat_level_id']) ? $event['threat_level_id'] : (Configure::read('MISP.default_event_threat_level') ? Configure::read('MISP.default_event_threat_level') : 4)]);
                ?>
            </td>
            <td class="short" ondblclick="document.location.href ='<?php echo $eventViewURL . h($uuid);?>'">
                <?php echo $analysisLevels[$event['analysis']]; ?>&nbsp;
            </td>
            <td ondblclick="document.location.href ='<?php echo $eventViewURL . h($uuid);?>'">
                <?php echo nl2br(h($event['info'])); ?>&nbsp;
            </td>
            <td ondblclick="document.location.href ='<?php echo $eventViewURL . h($uuid);?>'" class="short"><?php echo h($event['timestamp']); ?></td>
            <td class="short action-links">
                <?php if ($feed['Feed']['enabled'] && $isSiteAdmin) echo $this->Form->postLink('', $baseurl . '/feeds/getEvent/' . $id . '/' . $uuid, array('class' => 'fa fa-arrow-circle-down', 'title' => __('Fetch the event')), __('Are you sure you want to fetch and save this event on your instance?', $this->Form->value('Feed.id'))); ?>
                <a href='<?php echo $eventViewURL . h($uuid);?>' class = "fa fa-eye" title = "<?php echo __('View');?>" aria-label = "<?php echo __('View');?>"></a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <p>
    <?php
    echo $this->Paginator->counter(array(
    'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}'),
    'model' => 'Feed',
    ));
    ?>
    </p>
    <div class="pagination">
        <ul>
        <?php
            echo $this->Paginator->prev('&laquo; ' . __('previous'), array('tag' => 'li', 'escape' => false), null, array('tag' => 'li', 'class' => 'prev disabled', 'escape' => false, 'disabledTag' => 'span'));
            echo $this->Paginator->numbers(array('modulus' => 20, 'separator' => '', 'tag' => 'li', 'currentClass' => 'red', 'currentTag' => 'span'));
            echo $this->Paginator->next(__('next') . ' &raquo;', array('tag' => 'li', 'escape' => false), null, array('tag' => 'li', 'class' => 'next disabled', 'escape' => false, 'disabledTag' => 'span'));
        ?>
        </ul>
    </div>
</div>
<script type="text/javascript">
    var passedArgsArray = <?php echo $passedArgs; ?>;
    $(document).ready(function() {
        $('#quickFilterButton').click(function() {
            runIndexQuickFilter('<?php echo '/' . h($feed['Feed']['id']);?>');
        });
    });
</script>
<?php
    echo $this->element('/genericElements/SideMenu/side_menu', array('menuList' => 'feeds', 'menuItem' => 'previewIndex', 'id' => $id));
