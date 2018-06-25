<?php

/**
 * @file
 * Template file for swagger ui formatted field.
 *
 * Available variables:
 *   - $field_name: Name of the file field.
 *   - $delta: Delta value for the field.
 */
?>

<?php /** @var string $field_name */ ?>
<?php /** @var int $delta */ ?>

<div class="swagger-section">
    <div id="swagger-ui-<?php print $field_name ?>-<?php print $delta ?>"></div>
</div>
