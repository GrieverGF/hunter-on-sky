<?php
    /*
     *  echo $this->element('/genericElements/IndexTable/index_table', array(
     *      'top_bar' => (
     *          // search/filter bar information compliant with ListTopBar
     *      ),
     *      'data' => array(
                // the actual data to be used
     *      ),
     *      'fields' => array(
     *          // field list with information for the paginator, the elements used for the individual cells, etc
     *      ),
     *      'title' => optional title,
     *      'description' => optional description,
     *      'primary_id_path' => path to each primary ID (extracted and passed as $primary to fields)
     *  ));
     *
     */
    if (!empty($data['title'])) {
        echo sprintf('<h2>%s</h2>', h($data['title']));
    }
    if (!empty($data['description'])) {
        echo sprintf(
            '<div>%s</div>',
            empty($data['description']) ? '' : h($data['description'])
        );
    }
    if (!empty($data['html'])) {
        echo sprintf('<div>%s</div>', $data['html']);
    }
    if (!empty($data['persistUrlParams'])) {
        foreach ($data['persistUrlParams'] as $persistedParam) {
            if (!empty($passedArgs[$persistedParam])) {
                $data['paginatorOptions']['url'][] = $passedArgs[$persistedParam];
            }
        }
    }
    $paginationData = !empty($data['paginatorOptions']) ? $data['paginatorOptions'] : [];
    if ($ajax && isset($containerId)) {
        $paginationData['data-paginator'] = "#{$containerId}_content";
    }
    $this->Paginator->options($paginationData);
    $skipPagination = (!empty($data['skip_pagination']) || !empty($data['stupid_pagination'])) ? 1 : 0;
    if (!$skipPagination) {
        $paginatonLinks = $this->element('/genericElements/IndexTable/pagination_links');
        echo $paginatonLinks;
    }

    if (!empty($data['stupid_pagination'])) {
        $paginatonLinks = $this->element('/genericElements/IndexTable/stupid_pagination_links');
        echo $paginatonLinks;
    }
    $hasSearch = false;
    if (!empty($data['top_bar'])) {
        foreach ($data['top_bar']['children'] as $child) {
            if (isset($child['type']) && $child['type'] === 'search') {
                $hasSearch = true;
                break;
            }
        }
        echo $this->element('/genericElements/ListTopBar/scaffold', array('data' => $data['top_bar']));
    }
    $rows = '';
    $row_element = isset($data['row_element']) ? $data['row_element'] : 'row';
    $options = isset($data['options']) ? $data['options'] : array();
    $actions = isset($data['actions']) ? $data['actions'] : array();
    $dblclickActionArray = isset($data['actions']) ? Hash::extract($data['actions'], '{n}[dbclickAction]') : array();
    foreach ($data['data'] as $k => $data_row) {
        $primary = null;
        if (!empty($data['primary_id_path'])) {
            $primary = Hash::extract($data_row, $data['primary_id_path'])[0];
        }
        $rows .= sprintf(
            '<tr data-row-id="%s" %s %s>%s</tr>',
            h($k),
            empty($dblclickActionArray) ? '' : 'class="dblclickElement"',
            empty($primary) ? '' : 'data-primary-id="' . $primary . '"',
            $this->element(
                '/genericElements/IndexTable/' . $row_element,
                array(
                    'k' => $k,
                    'row' => $data_row,
                    'fields' => $data['fields'],
                    'options' => $options,
                    'actions' => $actions,
                    'primary' => $primary
                )
            )
        );
    }
    $tbody = '<tbody>' . $rows . '</tbody>';
    echo sprintf(
        '<div style="%s">',
        isset($data['max_height']) ? sprintf('max-height: %s; overflow-y: auto; resize: both', $data['max_height']) : ''
    );
    echo sprintf(
        '<table class="table table-striped table-hover table-condensed">%s%s</table>',
        $this->element('/genericElements/IndexTable/headers', array('fields' => $data['fields'], 'paginator' => $this->Paginator, 'actions' => empty($data['actions']) ? false : true)),
        $tbody
    );
    echo '</div>';
    if (!$skipPagination) {
        echo $this->element('/genericElements/IndexTable/pagination_counter', $paginationData);
        echo $paginatonLinks;
    }
    $url = $baseurl . '/' . $this->params['controller'] . '/' . $this->params['action'];
?>
<script type="text/javascript">
    var passedArgsArray = <?= isset($passedArgs) ? $passedArgs : '{}'; ?>;
    var url = "<?= $url ?>";
    <?php if ($hasSearch): ?>
    $(function() {
        <?php
        if (isset($containerId)) {
            echo 'var target = "#' . $containerId . '_content";';
        }
        ?>
        $('#quickFilterButton').click(function() {
            if (typeof(target) !== 'undefined') {
                runIndexQuickFilterFixed(passedArgsArray, url, target);
            } else {
                runIndexQuickFilterFixed(passedArgsArray, url);
            }
        });
    });
    <?php endif; ?>
</script>
