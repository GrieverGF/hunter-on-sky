<div class="templates index">
    <h2><?php echo __('Templates');?></h2>
    <div class="pagination">
        <ul>
        <?php
            echo $this->Paginator->prev('&laquo; ' . __('previous'), array('tag' => 'li', 'escape' => false), null, array('tag' => 'li', 'class' => 'prev disabled', 'escape' => false, 'disabledTag' => 'span'));
            echo $this->Paginator->numbers(array('modulus' => 20, 'separator' => '', 'tag' => 'li', 'currentClass' => 'active', 'currentTag' => 'span'));
            echo $this->Paginator->next(__('next') . ' &raquo;', array('tag' => 'li', 'escape' => false), null, array('tag' => 'li', 'class' => 'next disabled', 'escape' => false, 'disabledTag' => 'span'));
        ?>
        </ul>
    </div>
    <table class="table table-striped table-hover table-condensed">
    <tr>
            <th><?php echo $this->Paginator->sort('id');?></th>
            <th><?php echo $this->Paginator->sort('org');?></th>
            <th><?php echo $this->Paginator->sort('share');?></th>
            <th><?php echo $this->Paginator->sort('name');?></th>
            <th><?php echo $this->Paginator->sort('description');?></th>
            <?php if ($isAclTemplate): ?>
            <th class="actions"><?php echo __('Actions');?></th>
            <?php endif; ?>
    </tr><?php
foreach ($list as $item): ?>
    <tr>
        <td class="short" onclick="document.location.href ='<?php echo $baseurl."/templates/view/".$item['Template']['id']; ?>'"><?php echo h($item['Template']['id']); ?>&nbsp;</td>
        <td class="short" onclick="document.location.href ='<?php echo $baseurl."/templates/view/".$item['Template']['id']; ?>'">
            <?php
                echo $this->OrgImg->getOrgImg(array('name' => $item['Template']['org'], 'size' => 24));
            ?>
            &nbsp;
        </td>
        <td class="short" onclick="document.location.href ='<?php echo $baseurl."/templates/view/".$item['Template']['id']; ?>'"><?php if ($item['Template']['share']) echo 'Yes'; else echo 'No'; ?>&nbsp;</td>
        <td onclick="document.location.href ='<?php echo $baseurl."/templates/view/".$item['Template']['id']; ?>'"><?php echo h($item['Template']['name']); ?>&nbsp;</td>
        <td onclick="document.location.href ='<?php echo $baseurl."/templates/view/".$item['Template']['id']; ?>'"><?php echo h($item['Template']['description']); ?>&nbsp;</td>
        <?php if ($isAclTemplate): ?>
        <td class="short action-links">
            <?php echo $this->Html->link('', array('action' => 'edit', $item['Template']['id']), array('class' => 'fa fa-edit', 'title' => 'Edit', 'aria-label' => 'Edit'));?>
            <?php echo $this->Form->postLink('', array('action' => 'delete', $item['Template']['id']), array('class' => 'fa fa-trash', 'title' => 'Delete', 'aria-label' => 'Delete'), __('Are you sure you want to delete Template #' . $item['Template']['id'] . '?'));?>
        </td>
        <?php endif; ?>
    </tr><?php
endforeach; ?>
    </table>
    <p>
    <?php
    echo $this->Paginator->counter(array(
    'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
    ));
    ?>
    </p>
    <div class="pagination">
        <ul>
        <?php
            echo $this->Paginator->prev('&laquo; ' . __('previous'), array('tag' => 'li', 'escape' => false), null, array('tag' => 'li', 'class' => 'prev disabled', 'escape' => false, 'disabledTag' => 'span'));
            echo $this->Paginator->numbers(array('modulus' => 20, 'separator' => '', 'tag' => 'li', 'currentClass' => 'active', 'currentTag' => 'span'));
            echo $this->Paginator->next(__('next') . ' &raquo;', array('tag' => 'li', 'escape' => false), null, array('tag' => 'li', 'class' => 'next disabled', 'escape' => false, 'disabledTag' => 'span'));
        ?>
        </ul>
    </div>

</div>
<?php
    echo $this->element('/genericElements/SideMenu/side_menu', array('menuList' => 'templates', 'menuItem' => 'index'));
