/**
 * Table View JavaScript
 *
 * Handles view switching between card and table views,
 * AJAX table loading, sorting, and pagination.
 *
 * @package MediaInventoryForge
 * @since 4.0.0
 */

(function ($) {
    'use strict';

    /**
     * LocalStorage Helper Functions for Collapse State Persistence
     */
    function saveCollapseState(sectionId, isExpanded) {
        try {
            localStorage.setItem('mif_table_collapse_' + sectionId, isExpanded ? '1' : '0');
        } catch (e) {
            console.warn('Failed to save collapse state:', e);
        }
    }

    function getCollapseState(sectionId) {
        try {
            const state = localStorage.getItem('mif_table_collapse_' + sectionId);
            return state === '1'; // Returns true if expanded, false if collapsed
        } catch (e) {
            return true; // Default to expanded
        }
    }

    function restoreTableCollapseStates() {
        $('.mif-category-header').each(function() {
            const $header = $(this);
            const targetId = $header.data('target');

            if (!targetId) return;

            const savedState = localStorage.getItem('mif_table_collapse_' + targetId);
            if (savedState === null) return; // No saved state, keep default

            const shouldExpand = savedState === '1';
            const $content = $('#' + targetId);
            const $icon = $header.find('.mif-category-toggle-icon');

            if (shouldExpand) {
                $content.show();
                $icon.removeClass('dashicons-arrow-up-alt2').addClass('dashicons-arrow-down-alt2');
            } else {
                $content.hide();
                $icon.removeClass('dashicons-arrow-down-alt2').addClass('dashicons-arrow-up-alt2');
            }
        });
    }

    /**
     * Table View Manager
     */
    var MIF_TableView = {

        /**
         * Initialize table view functionality
         */
        init: function () {
            this.bindEvents();
            this.loadUserPreference();
        },

        /**
         * Bind event handlers
         */
        bindEvents: function () {
            var self = this;

            // Radio button changes
            $(document).on('change', 'input[name="mif-display-mode"]', function () {
                self.handleViewChange($(this).val());
            });

            // Apply view when scan completes
            $(document).on('mif_scan_complete', function () {
                self.applyCurrentView();
            });
        },

        /**
         * Handle view change from radio buttons
         */
        handleViewChange: function (view) {
            if (view === 'card') {
                this.showCardView();
            } else {
                this.showTableView();
            }

            // Save preference
            this.saveViewPreference(view);
        },

        /**
         * Show card view
         */
        showCardView: function () {
            $('#mif-card-view').show();
            $('#mif-table-view').hide();
        },

        /**
         * Show table view
         */
        showTableView: function () {
            console.log('showTableView called');
            $('#mif-card-view').hide();
            $('#mif-table-view').show();

            var $tableView = $('#mif-table-view');
            var isEmpty = $tableView.is(':empty');
            var hasContent = $.trim($tableView.text()).length > 0;
            var hasTable = $tableView.find('table').length > 0;

            console.log('Table view state:', {
                isEmpty: isEmpty,
                hasContent: hasContent,
                hasTable: hasTable,
                html: $tableView.html()
            });

            // Load table if not already loaded (check for actual table element)
            if (!hasTable) {
                console.log('Loading table data...');
                this.loadTableData();
            } else {
                console.log('Table already loaded, skipping AJAX call');
            }
        },

        /**
         * Apply current view based on radio selection
         */
        applyCurrentView: function () {
            var selectedView = $('input[name="mif-display-mode"]:checked').val() || 'card';
            this.handleViewChange(selectedView);
        },

        /**
         * Load table data via AJAX
         */
        loadTableData: function (params) {
            var self = this;
            params = params || {};

            console.log('loadTableData called with params:', params);
            console.log('AJAX URL:', ajaxurl);
            console.log('Nonce:', mifData.nonce);

            // Show loading state
            $('#mif-table-view').html('<div class="mif-loading" style="text-align: center; padding: 40px;"><span class="dashicons dashicons-update spin" style="font-size: 40px; color: #2271b1;"></span><p>Loading table view...</p></div>');

            var ajaxData = {
                action: 'mif_get_table_view',
                nonce: mifData.nonce,
                page: params.page || 1,
                orderby: params.orderby || 'title',
                order: params.order || 'asc',
                per_page: params.per_page || 50
            };

            console.log('Sending AJAX request with data:', ajaxData);

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: ajaxData,
                success: function (response) {
                    console.log('AJAX response received:', response);
                    if (response.success) {
                        console.log('Success! HTML length:', response.data.html.length);
                        $('#mif-table-view').html(response.data.html);
                        self.attachTableHandlers();
                    } else {
                        console.error('Response indicates failure:', response.data);
                        $('#mif-table-view').html('<div class="error" style="padding: 20px; text-align: center;"><p>Error loading table view: ' + (response.data || 'Unknown error') + '</p></div>');
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Table view AJAX error:', {xhr: xhr, status: status, error: error, responseText: xhr.responseText});
                    $('#mif-table-view').html('<div class="error" style="padding: 20px; text-align: center;"><p>Error loading table view: ' + error + '</p><p>Check browser console for details.</p></div>');
                }
            });
        },

        /**
         * Attach event handlers to table elements
         */
        attachTableHandlers: function () {
            var self = this;

            console.log('Attaching table handlers...');

            // Expandable row click handlers
            $('#mif-table-view').on('click', '.mif-expandable-row', function (e) {
                e.preventDefault();
                var $row = $(this);
                var targetId = $row.data('target');
                // IMPORTANT: Scope selector to #mif-table-view to avoid duplicate IDs in card view
                var $details = $('#mif-table-view').find('#' + targetId);
                var $icon = $row.find('.mif-expand-icon');

                console.log('Row clicked:', targetId);
                console.log('Details row found:', $details.length);
                console.log('Details row visible:', $details.is(':visible'));

                if ($details.length === 0) {
                    console.error('Details row not found! ID:', targetId);
                    return;
                }

                if ($details.is(':visible')) {
                    // Collapse
                    console.log('Collapsing row');
                    $details.css('display', 'none');
                    $icon.removeClass('dashicons-minus').addClass('dashicons-plus-alt2');
                } else {
                    // Expand
                    console.log('Expanding row');
                    $details.css('display', 'table-row');
                    $icon.removeClass('dashicons-plus-alt2').addClass('dashicons-minus');
                }
            });

            // Category header collapse handlers
            $('#mif-table-view').on('click', '.mif-category-header', function (e) {
                e.preventDefault();
                var $header = $(this);
                var targetId = $header.data('target');
                var $content = $('#' + targetId);
                var $icon = $header.find('.mif-category-toggle-icon');

                console.log('Category header clicked:', targetId);

                if ($content.is(':visible')) {
                    // Collapse
                    $content.slideUp(300);
                    $icon.removeClass('dashicons-arrow-down-alt2').addClass('dashicons-arrow-up-alt2');
                    saveCollapseState(targetId, false);
                } else {
                    // Expand
                    $content.slideDown(300);
                    $icon.removeClass('dashicons-arrow-up-alt2').addClass('dashicons-arrow-down-alt2');
                    saveCollapseState(targetId, true);
                }
            });

            // Sortable column headers
            $('#mif-table-view').on('click', '.mif-sortable', function (e) {
                e.preventDefault();
                var $header = $(this);
                var column = $header.data('column');
                var currentSort = $header.attr('data-sort');
                var newSort = currentSort === 'asc' ? 'desc' : 'asc';
                var $table = $header.closest('table');
                var $tbody = $table.find('tbody');

                console.log('Sorting column:', column, 'direction:', newSort);

                // Clear all sort indicators in this table
                $table.find('.mif-sortable').removeAttr('data-sort');

                // Set new sort direction
                $header.attr('data-sort', newSort);

                // Get column index
                var columnIndex = $header.index();

                // Get all main rows (not expanded details)
                var $rows = $tbody.find('tr.mif-expandable-row');

                // Sort rows
                $rows.sort(function (a, b) {
                    var $aCell = $(a).find('td').eq(columnIndex);
                    var $bCell = $(b).find('td').eq(columnIndex);

                    var aValue = $aCell.data('sort-value');
                    var bValue = $bCell.data('sort-value');

                    // Handle different data types
                    if (typeof aValue === 'string') {
                        aValue = aValue.toLowerCase();
                        bValue = bValue.toLowerCase();
                    } else {
                        aValue = parseFloat(aValue) || 0;
                        bValue = parseFloat(bValue) || 0;
                    }

                    if (newSort === 'asc') {
                        return aValue > bValue ? 1 : (aValue < bValue ? -1 : 0);
                    } else {
                        return aValue < bValue ? 1 : (aValue > bValue ? -1 : 0);
                    }
                });

                // Re-append sorted rows (maintaining row pairs: expandable + details)
                $rows.each(function () {
                    var $row = $(this);
                    var targetId = $row.data('target');
                    var $detailsRow = $('#' + targetId);

                    // Append main row
                    $tbody.append($row);

                    // Append details row immediately after
                    if ($detailsRow.length) {
                        $tbody.append($detailsRow);
                    }
                });
            });

            console.log('Table handlers attached');

            // Restore collapse states after table is loaded
            setTimeout(restoreTableCollapseStates, 100);
        },

        /**
         * Save user's view preference
         */
        saveViewPreference: function (view) {
            $.post(ajaxurl, {
                action: 'mif_save_view_preference',
                nonce: mifData.nonce,
                view: view
            });
        },

        /**
         * Load user's saved view preference
         */
        loadUserPreference: function () {
            // Get preference from PHP
            var savedView = mifData.viewPreference || 'card';

            // Set the radio button
            if (savedView === 'table') {
                $('#mif-display-table').prop('checked', true);
            } else {
                $('#mif-display-card').prop('checked', true);
            }
        }
    };

    /**
     * Initialize when document ready
     */
    $(document).ready(function () {
        MIF_TableView.init();
    });

    /**
     * Add spin animation for loading spinner
     */
    var style = document.createElement('style');
    style.textContent = `
        .dashicons.spin {
            animation: mif-spin 1s linear infinite;
        }
        @keyframes mif-spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    `;
    document.head.appendChild(style);

})(jQuery);
