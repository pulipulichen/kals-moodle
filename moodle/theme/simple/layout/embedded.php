<?php echo $OUTPUT->doctype() ?>
<html <?php echo $OUTPUT->htmlattributes() ?>>
<head>
    <title><?php echo $PAGE->title ?></title>
    <link rel="shortcut icon" href="<?php echo $OUTPUT->pix_url('favicon', 'theme')?>" />
    <meta name="viewport" content="width=device-width" />
    <?php echo $OUTPUT->standard_head_html() ?>
    
    <?php 
    /**
     * 加上Sementic UI
     * @author Pulipuli Chen <pulipuli.chen@gmail.com> 20151016
     */
    ?>
    <link rel="stylesheet" type="text/css" href="<?php echo $CFG->wwwroot; ?>/theme/simple/semantic-ui/semantic.min.css">
    <script src="<?php echo $CFG->wwwroot; ?>/theme/simple/semantic-ui/semantic.min.js"></script>
</head>
<body id="<?php p($PAGE->bodyid) ?>" class="<?php p($PAGE->bodyclasses) ?> notfrontpage">
<?php echo $OUTPUT->standard_top_of_body_html() ?>

<div id="page">

<!-- END OF HEADER -->

    <div id="content" class="clearfix">
        <?php echo $OUTPUT->main_content() ?>
    </div>

<!-- START OF FOOTER -->
</div>
<?php echo $OUTPUT->standard_end_of_body_html() ?>
</body>
</html>