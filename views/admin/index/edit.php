<?php

echo head($head);
echo flash();
?>

<section id="redirect-form" class="seven columns alpha">
    <div>
        <?php echo $form; ?>
    </div>
</section>
<section class="three columns omega">
    <div id="save" class="panel">
        <input type="submit" class="big green button" name="submit" value="<?php echo __('Save'); ?>">
        <?php if (!empty($element->id)): ?>
          <input type="submit" class="big red button" name="delete" value="<?php echo __('Delete'); ?>">
        <?php endif; ?>
        <a class="big blue button" href="<?php echo url('redirect'); ?>">Cancel</a>
    </div>

    <script type="text/javascript">
      (function($) {
        $(document).ready(function() {
          $('input[type="submit"]').on('click', function() {
            if ($(this).attr('name') == 'delete') {
              if (!confirm('Are you sure you want to delete this redirect?')) {
                return false;
              }
              $('input[type="hidden"][name="delete"]').val(1);
            }
            $('#redirect-form form').submit();
          });
        });

      })(jQuery)
    </script>
    <style>
      input.error {
        border: 2px solid red;
      }
    </style>
</section>

<?php echo foot(); ?>
