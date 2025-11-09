/**
 * Table View JavaScript
 *
 * Handles view switching between card and table views,
 * AJAX table loading, sorting, and pagination.
 *
 * @package MediaInventoryForge
 * @since   4.0.0
 * @author  Jim Roberts (Jim R Forge)
 */

(function ($) {
    'use strict';

    /**
     * Save category collapse state to localStorage
     *
     * @since 4.0.0
     * @param {string}  sectionId  The section ID to save state for
     * @param {boolean} isExpanded Whether the section is expanded
     * @returns {void}
     */
    function saveCollapseState(sectionId, isExpanded) {
        try {
            localStorage.setItem('mif_table_collapse_' + sectionId, isExpanded ? '1' : '0');
        } catch (e) {
            // Silently fail if localStorage is unavailable
        }
    }

    /**
     * Get category collapse state from localStorage
     *
     * @since 4.0.0
     * @param {string} sectionId The section ID to get state for
     * @returns {boolean} True if expanded, false if collapsed
     */
    function getCollapseState(sectionId) {
        try {
            const state = localStorage.getItem('mif_table_collapse_' + sectionId);
            return state === '1';
        } catch (e) {
            return true; // Default to expanded
        }
    }

    /**
     * Restore collapse states for all table category headers
     *
     * Reads saved states from localStorage and applies them to category sections.
     *
     * @since 4.0.0
     * @returns {void}
     */
    function restoreTableCollapseStates() {
        $('.mif-category-header').each(function() {
            const $header = $(this);
            const targetId = $header.data('target');

            if (!targetId) return;

            const savedState = localStorage.getItem('mif_table_collapse_' + targetId);
            if (savedState === null) return;

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
     *
     * Manages table view display, AJAX loading, sorting, and user preferences.
     *
     * @since 4.0.0
     */
    var MIF_TableView = {

        /**
         * Initialize table view functionality
         *
         * @since 4.0.0
         * @returns {void}
         */
        init: function () {
            this.bindEvents();
            this.loadUserPreference();
        },

        /**
         * Bind event handlers
         *
         * @since 4.0.0
         * @returns {void}
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
         *
         * @since 4.0.0
         * @param {string} view The view mode ('card' or 'table')
         * @returns {void}
         */
        handleViewChange: function (view) {
            if (view === 'card') {
                this.showCardView();
            } else {
                this.showTableView();
            }

            this.saveViewPreference(view);
        },

        /**
         * Show card view and hide table view
         *
         * Ensures card view container is visible and triggers refresh
         * of any collapsed/hidden content within.
         *
         * @since 4.0.0
         * @returns {void}
         */
        showCardView: function () {
            var $cardView = $('#mif-card-view');
            var $resultsContainer = $('#results-container');

            // Show card view, hide table view
            $cardView.show();
            $('#mif-table-view').hide();

            // Ensure results container and its content are visible
            if ($resultsContainer.children().length > 0) {
                $resultsContainer.show();

                // Ensure all category sections within card view are visible
                $resultsContainer.find('.fcc-info-toggle-section').each(function() {
                    var $section = $(this);
                    if ($section.css('display') === 'none') {
                        $section.show();
                    }
                });
            }

            // Trigger custom event for any additional handling
            $(document).trigger('mif_card_view_shown');
        },

        /**
         * Show table view and hide card view
         *
         * Loads table data via AJAX if not already loaded.
         *
         * @since 4.0.0
         * @returns {void}
         */
        showTableView: function () {
            $('#mif-card-view').hide();
            $('#mif-table-view').show();

            var $tableView = $('#mif-table-view');
            var hasTable = $tableView.find('table').length > 0;

            if (!hasTable) {
                this.loadTableData();
            }
        },

        /**
         * Apply current view based on radio selection
         *
         * @since 4.0.0
         * @returns {void}
         */
        applyCurrentView: function () {
            var selectedView = $('input[name="mif-display-mode"]:checked').val() || 'card';
            this.handleViewChange(selectedView);
        },

        /**
         * Load table data via AJAX
         *
         * @since 4.0.0
         * @param {Object} params Optional parameters (page, orderby, order, per_page)
         * @returns {void}
         */
        loadTableData: function (params) {
            var self = this;
            params = params || {};

            // Show loading state
            $('#mif-table-view').html(
                '<div class="mif-loading" style="text-align: center; padding: 40px;">' +
                '<span class="dashicons dashicons-update spin" style="font-size: 40px; color: #2271b1;"></span>' +
                '<p>Loading table view...</p>' +
                '</div>'
            );

            var ajaxData = {
                action: 'mif_get_table_view',
                nonce: mifData.nonce,
                page: params.page || 1,
                orderby: params.orderby || 'title',
                order: params.order || 'asc',
                per_page: params.per_page || 50
            };

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: ajaxData,
                success: function (response) {
                    if (response.success) {
                        $('#mif-table-view').html(response.data.html);
                        self.attachTableHandlers();
                    } else {
                        $('#mif-table-view').html(
                            '<div class="error" style="padding: 20px; text-align: center;">' +
                            '<p>Error loading table view: ' + (response.data || 'Unknown error') + '</p>' +
                            '</div>'
                        );
                    }
                },
                error: function (xhr, status, error) {
                    $('#mif-table-view').html(
                        '<div class="error" style="padding: 20px; text-align: center;">' +
                        '<p>Error loading table view: ' + error + '</p>' +
                        '</div>'
                    );
                }
            });
        },

        /**
         * Attach event handlers to table elements
         *
         * Sets up click handlers for expandable rows, category headers,
         * and sortable columns. Restores saved collapse states.
         *
         * @since 4.0.0
         * @returns {void}
         */
        attachTableHandlers: function () {
            // Expandable row click handlers
            $('#mif-table-view').on('click', '.mif-expandable-row', function (e) {
                e.preventDefault();
                var $row = $(this);
                var targetId = $row.data('target');
                // Scope selector to #mif-table-view to avoid duplicate IDs in card view
                var $details = $('#mif-table-view').find('#' + targetId);
                var $icon = $row.find('.mif-expand-icon');

                if ($details.length === 0) {
                    return;
                }

                if ($details.is(':visible')) {
                    $details.css('display', 'none');
                    $icon.removeClass('dashicons-minus').addClass('dashicons-plus-alt2');
                } else {
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

                if ($content.is(':visible')) {
                    $content.slideUp(300);
                    $icon.removeClass('dashicons-arrow-down-alt2').addClass('dashicons-arrow-up-alt2');
                    saveCollapseState(targetId, false);
                } else {
                    $content.slideDown(300);
                    $icon.removeClass('dashicons-arrow-up-alt2').addClass('dashicons-arrow-down-alt2');
                    saveCollapseState(targetId, true);
                }
            });

            // Sortable column headers
            $('#mif-table-view').on('click', '.mif-sortable', function (e) {
                e.preventDefault();
                var $header = $(this);
                var currentSort = $header.attr('data-sort');
                var newSort = currentSort === 'asc' ? 'desc' : 'asc';
                var $table = $header.closest('table');
                var $tbody = $table.find('tbody');

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

                    $tbody.append($row);

                    if ($detailsRow.length) {
                        $tbody.append($detailsRow);
                    }
                });
            });

            // Restore collapse states after table is loaded
            setTimeout(restoreTableCollapseStates, 100);
        },

        /**
         * Save user's view preference via AJAX
         *
         * @since 4.0.0
         * @param {string} view The view mode to save ('card' or 'table')
         * @returns {void}
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
         *
         * Reads the saved preference from PHP and sets the appropriate radio button.
         *
         * @since 4.0.0
         * @returns {void}
         */
        loadUserPreference: function () {
            var savedView = mifData.viewPreference || 'card';

            if (savedView === 'table') {
                $('#mif-display-table').prop('checked', true);
            } else {
                $('#mif-display-card').prop('checked', true);
            }
        }
    };

    /**
     * Initialize table view manager when document is ready
     *
     * @since 4.0.0
     */
    $(document).ready(function () {
        MIF_TableView.init();
    });

    /**
     * Add CSS for loading spinner animation
     *
     * @since 4.0.0
     */
    var style = document.createElement('style');
    style.textContent =
        '.dashicons.spin {' +
        '    animation: mif-spin 1s linear infinite;' +
        '}' +
        '@keyframes mif-spin {' +
        '    0% { transform: rotate(0deg); }' +
        '    100% { transform: rotate(360deg); }' +
        '}';
    document.head.appendChild(style);

})(jQuery);
