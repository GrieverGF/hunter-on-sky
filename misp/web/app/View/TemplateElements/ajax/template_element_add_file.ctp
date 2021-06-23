<div class="template_element_add_file">
<?php
    echo $this->Form->create('TemplateElementFile', array('id', 'url' => $baseurl . '/templateElements/add/file/' . $id));
?>
    <legend><?php echo __('Add File Element To Template'); ?></legend>
    <fieldset>
        <div id="formWarning" class="message ajaxMessage"></div>
        <div class="add_attribute_fields">
            <?php
                echo $this->Form->input('name', array(
                        'type' => 'text',
                        'error' => array('escape' => false),
                        'div' => 'input clear',
                        'class' => 'input-xxlarge'
                ));

                echo $this->Form->input('description', array(
                        'type' => 'textarea',
                        'error' => array('escape' => false),
                        'div' => 'input clear',
                                'class' => 'input-xxlarge'
                ));
            ?>
                <div class="input clear"></div>
            <?php
                echo $this->Form->input('category', array(
                        'options' => array($categories),
                        'label' => __('Category'),
                        'empty' => __('Select Category')
                ));
            ?>

            <div class="input clear"></div>
            <div id='malwareToggle' title="<?php echo __('If a file is flagged as malicious then it will automatically be encrypted.');?>">
                <?php
                    echo $this->Form->input('malware', array(
                            'checked' => false,
                            'label' => __('Malware'),
                    ));

                ?>
            </div>
            <div class="input clear"></div>
            <div title="<?php echo __('This setting will make this element mandatory.');?>">
                <?php
                    echo $this->Form->input('mandatory', array(
                            'checked' => false,
                            'label' => __('Mandatory element'),
                    ));
                ?>
            <div>
            <div class="input clear"></div>
            <div title="<?php echo __('If this checkbox is checked, then the resulting field in the form will allow several files to be uploaded.');?>">
                <?php
                    echo $this->Form->input('batch', array(
                            'checked' => false,
                            'label' => __('Batch import element'),
                    ));
                ?>
            </div>
        </div>
    </fieldset>
    <div class="overlay_spacing">
        <table>
            <tr>
            <td style="vertical-align:top">
                <span title="<?php echo __('Submit the file element');?>" id="submitButton" class="btn btn-primary" onClick="return submitPopoverForm('<?php echo $id;?>', 'addFileElement');"><?php echo __('Submit');?></span>
            </td>
            <td style="width:540px;">
                <p style="color:red;font-weight:bold;display:none;text-align:center" id="warning-message"><?php echo __('Warning: You are about to share data that is of a classified nature (Attribution / targeting data). Make sure that you are authorised to share this.');?></p>
            </td>
            <td style="vertical-align:top;">
                <span class="btn btn-inverse" id="cancel_attribute_add" onClick="cancelPopoverForm();"><?php echo __('Cancel');?></span>
            </td>
            </tr>
        </table>
    </div>
    <?php
        echo $this->Form->end();
    ?>
</div>
<script type="text/javascript">

    var fieldsArray = new Array('TemplateElementFileName', 'TemplateElementFileDescription', 'TemplateElementFileCategory', 'TemplateElementFileMalware', 'TemplateElementFileMandatory', 'TemplateElementFileBatch');
    var categoryArray = new Array();
    $(document).ready(function() {
        <?php
            foreach ($categoryArray as $k => $cat) {
                echo 'categoryArray[\'' . $k . '\'] = [';
                    foreach ($cat as $l => $type) {
                        if ($l != 0) echo ', ';
                        echo '"' . $type . '"';
                    }
                echo '];';
            }
        ?>
        templateElementFileCategoryChange($("#TemplateElementFileCategory").val());
    });

    $("#TemplateElementFileCategory").change(function() {
        var category = $("#TemplateElementFileCategory").val();
        templateElementFileCategoryChange(category);
    });
</script>
