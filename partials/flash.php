<?php
/*put this at the bottom of the page so any templates
 populate the flash variable and then display at the proper timing*/
?>
<div class="container" id="flash">
    <!-- can be blank or can have messages within it -->
    <?php $messages = getMessages(); ?>
    <?php if ($messages) : ?>
        <?php foreach ($messages as $msg) : ?>
            <!-- integrated with bootstrap -->
            <div class="row justify-content-center">
                <!-- color and text, color dictates the type of message via main colors -->
                <!-- put at bottom, last thing that runs to make sure all relevant messages exist -->
                <div class="alert alert-<?php se($msg, 'color', 'info'); ?>" role="alert"><?php se($msg, "text", ""); ?></div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<script>
    //used to pretend the flash messages are below the first nav element
    function moveMeUp(ele) {
        let target = document.getElementsByTagName("nav")[0];
        if (target) {
            target.after(ele);
        }
    }

    moveMeUp(document.getElementById("flash"));
</script>

<!-- 
    green - good, success
    yellow orange - warning
    red - danger
    teal - info
    light - off white
    dark - off black
    blue - primary  
    grey - secondary
-->