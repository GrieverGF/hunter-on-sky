<?php
    $headersHtml = '';
    foreach ($fields as $k => $header) {
        if (!isset($header['requirement']) || $header['requirement']) {
            $header_data = '';
            if (!empty($header['sort'])) {
                if (!empty($header['name'])) {
                    $header_data = $paginator->sort($header['sort'], $header['name']);
                } else {
                    $header_data = $paginator->sort($header['sort']);
                }
            } else {
                if (!empty($header['element']) && $header['element'] === 'selector') {
                    $header_data = sprintf(
                        '<input id="select_all" class="%s" type="checkbox" %s>',
                        empty($header['select_all_class']) ? 'select_all' : $header['select_all_class'],
                        empty($header['select_all_function']) ? 'onclick="toggleAllAttributeCheckboxes();"' : 'onclick="' . $header['select_all_function'] . '"'
                    );
                } else {
                    $header_data = h($header['name']);
                }
            }
            $classes = [];
            if (!empty($header['sort'])) {
                $classes[] = 'pagination_link';
            }
            if (!empty($header['rotate_header'])) {
                $classes[] = 'rotate';
                $header_data = "<div><span>$header_data</span></div>";
            }

            $headersHtml .= sprintf(
                '<th%s%s>%s</th>',
                !empty($classes) ? ' class="' . implode(' ', $classes) .'"' : '',
                !empty($header['header_title']) ? ' title="' . h($header['header_title']) . '"' : '',
                $header_data
            );
        }
    }
    if ($actions) {
        $headersHtml .= sprintf(
            '<th class="actions">%s</th>',
            __('Actions')
        );
    }
    $thead = '<thead>';
    $thead .= $headersHtml;
    $thead .= '</thead>';
    echo $thead;
?>
<script type="text/javascript">
    $(document).ready(function() {
        $('.select_attribute').add('#select_all').on('change', function() {
            if ($('.select_attribute:checked').length > 0) {
                $('.mass-select').show();
            } else {
                $('.mass-select').hide();
            }
        });
    });
</script>
