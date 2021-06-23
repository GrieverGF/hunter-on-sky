<?php
    $canViewFeedData = $isSiteAdmin || intval(Configure::read('MISP.host_org_id')) === $me['org_id'];
    $feedTemplate = array(
        'id', 'name', 'provider', 'url'
    );
?>
<div class="feed index">
    <h2><?php echo __('Feed overlap analysis matrix');?></h2>
    <?php
        if (count($feeds) >= 2):
    ?>
        <div>
            <table class="table table-striped table-hover table-condensed" style="width:100px;">
                <tr>
                        <th>&nbsp;</th>
                    <?php
                    foreach ($feeds as $item):
                            $popover = '';
                            foreach ($feedTemplate as $element):
                                $popover .= '<span class=\'bold\'>' . Inflector::humanize($element) . '</span>: <span class=\'bold blue\'>' . h($item['Feed'][$element]) . '</span><br />';
                            endforeach;
                  ?>
                    <th>
                            <div data-toggle="popover" data-content="<?php echo $popover; ?>" data-trigger="hover">
                            <?php echo (empty($item['Feed']['is_misp_server']) ? 'F' : 'S') . h($item['Feed']['id']); ?>
                            </div>
                    </th>
                  <?php
                    endforeach;
                  ?>
                </tr>
              <?php
                foreach ($feeds as $item):
                        $popover = '';
                        foreach ($feedTemplate as $element):
                            $popover .= '<span class=\'bold\'>' . Inflector::humanize($element) . '</span>: <span class=\'bold blue\'>' . h($item['Feed'][$element]) . '</span><br />';
                        endforeach;
              ?>
                <tr>
                    <td class="short">
                            <div data-toggle="popover" data-content="<?php echo $popover;?>" data-trigger="hover">
                                <?php
                                    echo sprintf(
                                        '%s%s %s%s',
                                        empty($item['Feed']['is_misp_server']) ? 'Feed #' : 'Server #',
                                        h($item['Feed']['id']),
                                        empty($item['Feed']['is_misp_server']) ? '' : '(<span class="blue bold">MISP</span>) ',
                                        (!$canViewFeedData || !empty($item['Feed']['is_misp_server'])) ? h($item['Feed']['name']) : sprintf(
                                            '<a href="%s/feeds/view/%s" title="View feed #%s">%s</a>',
                                            $baseurl,
                                            h($item['Feed']['id']),
                                            h($item['Feed']['id']),
                                            h($item['Feed']['name'])
                                        )
                                    );
                                ?>
                            </div>
                        </td>
                        <?php
                        foreach ($feeds as $item2):
                                    $percentage = -1;
                                    $class = 'bold';
                                    foreach ($item['Feed']['ComparedFeed'] as $k => $v):
                                        if ($item2['Feed']['id'] == $v['id']):
                                            $percentage = $v['overlap_percentage'];
                                            if ($percentage <= 5) $class .= ' green';
                                            else if ($percentage <= 50) $class .= ' orange';
                                            else $class .= ' red';
                                            break;
                                        endif;
                                    endforeach;
                                    $title = '';
                                    if ($percentage == 0) $popover = __('None or less than 1% of the data of %s is contained in %s (%s matching values)', $item['Feed']['name'], $item2['Feed']['name'], $v['overlap_count']);
                                    else if ($percentage > 0) $popover = __('%s% of the data of %s is contained in %s (%s matching values)',$percentage, $item['Feed']['name'], $item2['Feed']['name'], $v['overlap_count'])
                            ?>
                                <td class="<?php echo h($class); ?>">
                                    <div data-toggle="popover" data-content="<?php echo h($popover);?>" data-trigger="hover">
                                        <?php echo (($percentage == -1) ? '-' : h($percentage) . '%');?>
                                    </div>
                                </td>
                            <?php
                        endforeach;
                      ?>
                </tr>
              <?php
                endforeach;
              ?>
            </table>
        </div>
    <?php
        else:
            echo '<p class="red bold">Not enough feeds cached. Make sure you have at least 2 feeds that are cached and available.</p>';
        endif;
    ?>
</div>
<script type="text/javascript">
    $(document).ready(function(){
        popoverStartup();
    });
</script>
<?php
    echo $this->element('/genericElements/SideMenu/side_menu', array('menuList' => 'feeds', 'menuItem' => 'compare'));
?>
