<div id="top">
    <h3><?= __('Discussion') ?></h3>
    <div class="pagination">
        <?php
        if (!empty($posts)):
        ?>
            <ul>
        <?php
            $this->Paginator->options(array(
                'update' => '#top',
                'evalScripts' => true,
                'before' => '$(".loading").show()',
                'complete' => '$(".loading").hide()',
            ));

                echo $this->Paginator->prev('&laquo; ' . __('previous'), array('tag' => 'li', 'escape' => false), null, array('tag' => 'li', 'class' => 'prev disabled', 'escape' => false, 'disabledTag' => 'span'));
                echo $this->Paginator->numbers(array('modulus' => 10, 'separator' => '', 'tag' => 'li', 'currentClass' => 'red', 'currentTag' => 'span'));
                echo $this->Paginator->next(__('next') . ' &raquo;', array('tag' => 'li', 'escape' => false), null, array('tag' => 'li', 'class' => 'next disabled', 'escape' => false, 'disabledTag' => 'span'));
            ?>
            </ul>
        </div>
        <div id="posts">
            <?php
                foreach ($posts as $post) {
            ?>
                    <table class="discussionBox" id="message_<?= h($post['id']) ?>">
                        <tr>
                            <td class="discussionBoxTD discussionBoxTDtop" colspan="2">
                            <div>
                                <table style="width:100%">
                                    <tr>
                                        <td><?= __('Date: ') . h($post['date_created']) ?></td>
                                        <td style="text-align:right">
                                            <a href="#top" class="whitelink"><?= __('Top') ?></a> |
                                            <a href="#message_<?php echo h($post['id']); ?>" class="whitelink">#<?php echo h($post['id'])?></a>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="discussionBoxTD discussionBoxTDMid discussionBoxTDMidLeft">
                                <?php
                                if (isset($post['org_id'])) {
                                    echo $this->OrgImg->getOrgLogo(['id' => $post['org_id'], 'name' => $post['org_name'], 'uuid' => $post['org_uuid']], 48);
                                } else {
                                    echo __('Deactivated user');
                                }
                                ?>
                            </td>
                            <td class="discussionBoxTD discussionBoxTDMid discussionBoxTDMidRight">
            <?php
                                    echo $this->Command->convertQuotes(nl2br(h($post['contents'])));
                                    if ($post['post_id'] !=0 || ($post['date_created'] != $post['date_modified'])) {
            ?>
                                        <br><br>
            <?php
                                    }
                                    if ($post['post_id'] != 0) {
            ?>
                                        <span style="font-style:italic">
                                            In reply to post
                                            <a href="#message_<?php echo h($post['post_id']); ?>">#<?php echo h($post['post_id'])?></a>
                                        </span>
            <?php
                                    }
                                    if ($post['date_created'] != $post['date_modified']) {
                                        echo '<span style="font-style:italic">' . __('Message edited at %s', h($post['date_modified'])) . '<span>';
                                    }
            ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="discussionBoxTD discussionBoxTDbottom" colspan="2">
                                <table style="width:100%">
                                    <tr>
                                        <td>
                                            <?php echo !empty($post['user_email']) ? h($post['user_email']) : __('User ') . h($post['user_id']) . ' (' . (h($post['org_name'])) . ')'; ?>
                                        </td>
                                        <td style="text-align:right">
            <?php
                                        if (!$isSiteAdmin) {
                                            if ($post['user_id'] == $myuserid) {
                                                echo $this->Html->link('', array('controller' => 'posts', 'action' => 'edit', h($post['id']), h($context)), array('class' => 'fa fa-edit', 'title' => __('Edit'), 'aria-label' => __('Edit')));
                                                echo $this->Form->postLink('', array('controller' => 'posts', 'action' => 'delete', h($post['id']), h($context)), array('class' => 'fa fa-trash', 'title' => __('Delete'), 'aria-label' => __('Delete')), __('Are you sure you want to delete this post?'));
                                            } else {
            ?>
                                                <a href="<?php echo $baseurl.'/posts/add/post/'.h($post['id']); ?>" class="fas fa-comment" title="<?php echo __('Reply');?>" aria-label="<?php echo __('Reply');?>"></a>
            <?php
                                            }
                                        } else {
                                            echo $this->Html->link('', array('controller' => 'posts', 'action' => 'edit', h($post['id']), h($context)), array('class' => 'fa fa-edit', 'title' => __('Edit'), 'aria-label' => __('Edit')));
                                            echo $this->Form->postLink('', array('controller' => 'posts', 'action' => 'delete', h($post['id']), h($context)), array('class' => 'fa fa-trash', 'title' => __('Delete'), 'aria-label' => __('Delete')), __('Are you sure you want to delete this post?'));
            ?>
                                                <a href="<?php echo $baseurl.'/posts/add/post/'.h($post['id']); ?>" class="fas fa-comment" title="<?php echo __('Reply');?>" aria-label="<?php echo __('Reply');?>"></a>
            <?php

                                        }
            ?>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                    <br>
            <?php
                }
            ?>
            </div>
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
                echo $this->Paginator->numbers(array('modulus' => 20, 'separator' => '', 'tag' => 'li', 'currentClass' => 'red', 'currentTag' => 'span'));
                echo $this->Paginator->next(__('next') . ' &raquo;', array('tag' => 'li', 'escape' => false), null, array('tag' => 'li', 'class' => 'next disabled', 'escape' => false, 'disabledTag' => 'span'));
            ?>
            </ul>
        </div>
    <?php endif; ?>
    <div class="comment">
    <?php
        if (isset($currentEvent)) $url = $baseurl . '/posts/add/event/' . $currentEvent;
        else $url = $baseurl . '/posts/add/thread/' . $thread['Thread']['id'];
        echo $this->Form->create('Post', array('url' => $url));
    ?>
        <fieldset>
        <div class="input clear">
            <button type="button" title="<?php echo __('Insert a quote - just paste your quote between the [quote][/quote] tags.');?>" class="toggle-left btn btn-inverse qet" id="quote"  onclick="insertQuote()"><?php echo __('Quote');?></button>
            <button type="button" title="<?php echo __('Insert a link to an event - just enter the event ID between the [event][/event] tags.');?>" class="toggle btn btn-inverse qet" id="event"  onclick="insertEvent()"><?php echo __('Event');?></button>
            <button type="button" title="<?php echo __('Insert a link to a discussion thread - enter the thread\'s ID between the [thread][/thread] tags.');?>" class="toggle btn btn-inverse qet" id="thread"  onclick="insertThread()"><?php echo __('Thread');?></button>
            <button type="button" title="<?php echo __('Insert a link [link][/link] tags.');?>" class="toggle btn btn-inverse qet" id="link"  onclick="insertLink()"><?php echo __('Link');?></button>
            <button type="button" title="<?php echo __('Insert a code [code][/code] tags.');?>" class="toggle-right btn btn-inverse qet" id="code"  onclick="insertCode()"><?php echo __('Code');?></button>
        </div>
        <?php
            echo $this->Form->input('message', array(
                    'label' => false,
                    'type' => 'textarea',
                    'div' => 'input clear',
                    'class' => 'input-xxlarge',
            ));
        ?>
        </fieldset>
        <button class="btn btn-primary" onclick="submitMessageForm('<?php echo $url;?>', 'PostViewForm', 'top'); return false;"><?php echo __('Send comment');?></button>
    <?php
        echo $this->Form->end();
    ?>
    </div>
</div>
<script type="text/javascript">
    function insertQuote() {
        document.getElementById("PostMessage").value+="[Quote][/Quote]";
    }
    function insertEvent() {
        document.getElementById("PostMessage").value+="[Event][/Event]";
    }
    function insertThread() {
        document.getElementById("PostMessage").value+="[Thread][/Thread]";
    }
    function insertLink() {
        document.getElementById("PostMessage").value+="[Link][/Link]";
    }
    function insertCode() {
        document.getElementById("PostMessage").value+="[Code][/Code]";
    }
    <?php if (isset($post_id) && $post_id): ?>
        $(function() {
            location.hash = "#message_<?php echo h($post_id); ?>";
        });
    <?php endif; ?>
</script>
<?php echo $this->Js->writeBuffer();
