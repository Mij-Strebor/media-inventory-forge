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
                var $details = $('#' + targetId);
                var $icon = $row.find('.mif-expand-icon');

                console.log('Row clicked:', targetId);

                if ($details.is(':visible')) {
                    // Collapse
                    $details.hide();
                    $icon.removeClass('dashicons-minus').addClass('dashicons-plus-alt2');
                } else {
                    // Expand
                    $details.show();
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
                } else {
                    // Expand
                    $content.slideDown(300);
                    $icon.removeClass('dashicons-arrow-up-alt2').addClass('dashicons-arrow-down-alt2');
                }
            });

            console.log('Table handlers attached');
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
