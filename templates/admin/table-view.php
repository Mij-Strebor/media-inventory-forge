<?php

/**
 * Table View Template
 *
 * Displays media inventory in a sortable, paginated table format.
 *
 * @package MediaInventoryForge
 * @subpackage Templates
 * @since 4.0.0
 */

// Prevent direct access
defined('ABSPATH') || exit;

// Create and prepare table
$list_table = new MIF_Media_List_Table();
$list_table->prepare_items();
?>

<div class="wrap mif-table-view-wrapper">
    <form id="mif-table-form" method="get">
        <input type="hidden" name="page" value="media-inventory-forge" />
        <?php $list_table->display(); ?>
    </form>
</div>
