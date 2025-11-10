/**
 * Media Inventory Forge - Admin JavaScript
 *
 * WordPress media library analysis and inventory management interface.
 * Provides comprehensive scanning, categorization, and display capabilities
 * for WordPress media libraries with batch processing and interactive UI.
 *
 * @file Admin interface for Media Inventory Forge plugin
 * @version 2.0.0
 * @author Jim R (JimRWeb)
 * @requires jQuery
 * @requires WordPress AJAX infrastructure (wp_ajax)
 * @requires WordPress nonces for security
 * @license GPLv2 or later
 *
 * @global {Array} inventoryData - Stores scanned media item data
 * @global {boolean} isScanning - Tracks current scanning state
 * @global {Object} mifData - WordPress localized script data (ajaxUrl, nonce)
 *
 * Architecture Overview:
 * 1. Initialization & Global State
 * 2. Interactive Components (Toggle System)
 * 3. Scan Control Event Handlers
 * 4. AJAX Communication & Batch Processing
 * 5. Main Display Orchestration
 * 6. Category Rendering System
 * 7. Specialized Type Handlers (Fonts, SVG, Default)
 * 8. Image Category Display System
 * 9. UI Component Builders
 * 10. Utility Functions
 */

/* ==========================================================================
   1. INITIALIZATION & GLOBAL STATE
   ========================================================================== */

/**
 * Main Application Initialization
 *
 * Self-executing function that initializes all media inventory functionality
 * when DOM is ready. Establishes global state variables and sets up all
 * event handlers for the admin interface.
 */
jQuery(document).ready(function ($) {
  /**
   * Global inventory data storage
   * Exposed to window for table-view.js access
   * @type {Array<Object>}
   */
  window.inventoryData = window.inventoryData || [];
  let inventoryData = window.inventoryData;

  /**
   * Scanning state flag
   * @type {boolean}
   */
  let isScanning = false;

  /* ==========================================================================
     1. INITIALIZATION
     ========================================================================== */

  /**
   * Update the toggle checkbox state with visual refresh
   */
  function updateToggleCheckboxState(checkedCount, totalCount) {
    var $toggleCheckbox = $("#mif-toggle-all-sources");
    var element = $toggleCheckbox[0];

    if (!element) return;

    // Reset state first
    element.checked = false;
    element.indeterminate = false;

    // Force a reflow to ensure browser registers the reset
    void element.offsetHeight;

    if (checkedCount === 0) {
      // Unchecked state
      element.checked = false;
      element.indeterminate = false;
    } else if (checkedCount === totalCount) {
      // Fully checked state
      element.checked = true;
      element.indeterminate = false;
    } else {
      // Indeterminate state - set checked to true first, then indeterminate
      element.checked = true;
      element.indeterminate = true;
    }

    // Force another reflow to ensure visual update
    void element.offsetHeight;
  }

  /**
   * Initialize tri-state checkbox for Scan Sources
   * Sets the correct initial state based on which child checkboxes are checked
   */
  function initializeToggleAllCheckbox() {
    var totalSources = $(".mif-source-filter").length;
    var checkedSources = $(".mif-source-filter:checked").length;

    updateToggleCheckboxState(checkedSources, totalSources);
  }

  // Call initialization with slight delay to ensure DOM is fully ready
  setTimeout(initializeToggleAllCheckbox, 100);

  /* ==========================================================================
     2. INTERACTIVE COMPONENTS - UNIVERSAL TOGGLE SYSTEM
     ========================================================================== */

  /**
   * LocalStorage Helper Functions for Collapse State Persistence
   */
  function saveCollapseState(sectionId, isExpanded) {
    try {
      localStorage.setItem('mif_collapse_' + sectionId, isExpanded ? '1' : '0');
    } catch (e) {
      // Silently fail if localStorage is unavailable
    }
  }

  function getCollapseState(sectionId) {
    try {
      const state = localStorage.getItem('mif_collapse_' + sectionId);
      return state === '1'; // Returns true if expanded, false if collapsed, false if not set
    } catch (e) {
      return false;
    }
  }

  function restoreCollapseStates() {
    $('.fcc-info-toggle').each(function() {
      const $toggle = $(this);
      let targetId;

      // Get target ID
      if ($toggle.data('toggle-target')) {
        targetId = $toggle.data('toggle-target');
      } else {
        const $content = $toggle.next('.fcc-info-content');
        targetId = $content.attr('id');
      }

      if (!targetId) return;

      const savedState = localStorage.getItem('mif_collapse_' + targetId);
      if (savedState === null) return; // No saved state, keep default

      const shouldExpand = savedState === '1';
      const $content = $('#' + targetId);

      if (shouldExpand) {
        $content.addClass('expanded').css('max-height', 'none');
        $toggle.addClass('expanded');
      } else {
        $content.removeClass('expanded').css('max-height', '0px');
        $toggle.removeClass('expanded');
      }
    });
  }

  /**
   * Universal Toggle System with Dynamic Height Calculation
   *
   * Provides collapsible panel functionality for any element with the
   * fcc-info-toggle class. Automatically calculates content height for
   * smooth animations regardless of content size. Supports both explicit
   * data-toggle-target attributes and implicit next-sibling relationships.
   *
   * Includes localStorage persistence to maintain collapse states across page refreshes.
   *
   * @listens click - Handles toggle button clicks
   * @param {Event} e - jQuery click event object
   * @returns {void}
   *
   * @note Uses CSS transition duration of 400ms for animations
   * @note Requires matching CSS classes: fcc-info-toggle, fcc-info-content, expanded
   * @note Searches for content in three ways: explicit target, next sibling, parent search
   * @note Saves collapse state to localStorage for persistence
   */
  $(document).on("click", ".fcc-info-toggle", function (e) {
    e.preventDefault();
    e.stopPropagation();

    const $toggle = $(this);
    let $content;
    let targetId;

    // Check for explicit target first
    targetId = $toggle.data("toggle-target");
    if (targetId) {
      $content = $("#" + targetId);
    } else {
      // Fall back to next sibling with fcc-info-content class
      $content = $toggle.next(".fcc-info-content");
      if (!$content.length) {
        // If not immediate sibling, look in parent container
        $content = $toggle
          .closest(".fcc-info-toggle-section")
          .find(".fcc-info-content");
      }
      targetId = $content.attr('id');
    }

    if (!$content.length) {
      return;
    }

    // Toggle states with dynamic height calculation
    if ($content.hasClass("expanded")) {
      // Collapse: set specific height then animate to 0
      const currentHeight = $content[0].scrollHeight;
      $content.css("max-height", currentHeight + "px");

      // Force reflow then animate to collapsed
      $content[0].offsetHeight;
      $content.css("max-height", "0px");
      $content.removeClass("expanded");
      $toggle.removeClass("expanded");

      // Save collapsed state
      if (targetId) saveCollapseState(targetId, false);
    } else {
      // Expand: temporarily show content to measure, then animate
      $content.css("max-height", "none");
      $content.addClass("expanded");
      const targetHeight = $content[0].scrollHeight;
      $content.css("max-height", "0px");

      // Force reflow then animate to target height
      $content[0].offsetHeight;
      $content.css("max-height", targetHeight + "px");
      $toggle.addClass("expanded");

      // Clean up after animation completes
      setTimeout(() => {
        if ($content.hasClass("expanded")) {
          $content.css("max-height", "none");
        }
      }, 400); // Match CSS transition duration

      // Save expanded state
      if (targetId) saveCollapseState(targetId, true);
    }
  });

  /* ==========================================================================
     2b. EXPANDABLE TABLE HANDLERS (FOR CARD VIEW)
     ========================================================================== */

  /**
   * Expandable Row Click Handler (Card View)
   *
   * Handles clicks on expandable table rows in card view
   * (specifically for Images when toggle is set to "Table")
   */
  $(document).on('click', '#results-container .mif-expandable-row', function (e) {
    e.preventDefault();
    const $row = $(this);
    const targetId = $row.data('target');
    // IMPORTANT: Scope selector to #mif-card-view to avoid duplicate IDs in table view
    const $details = $('#mif-card-view').find('#' + targetId);
    const $icon = $row.find('.mif-expand-icon');

    if ($details.length === 0) {
      return;
    }

    if ($details.is(':visible')) {
      // Collapse
      $details.css('display', 'none');
      $icon.removeClass('dashicons-minus').addClass('dashicons-plus-alt2');
    } else {
      // Expand
      $details.css('display', 'table-row');
      $icon.removeClass('dashicons-plus-alt2').addClass('dashicons-minus');
    }
  });

  /**
   * Sortable Column Click Handler (Card View)
   *
   * Handles sorting on table columns in card view
   */
  $(document).on('click', '#results-container .mif-sortable', function (e) {
    e.preventDefault();
    const $header = $(this);
    const column = $header.data('column');
    const currentSort = $header.attr('data-sort');
    const newSort = currentSort === 'asc' ? 'desc' : 'asc';
    const $table = $header.closest('table');
    const $tbody = $table.find('tbody');

    // Clear all sort indicators in this table
    $table.find('.mif-sortable').removeAttr('data-sort');

    // Set new sort direction
    $header.attr('data-sort', newSort);

    // Get column index
    const columnIndex = $header.index();

    // Get all main rows (not expanded details)
    const $rows = $tbody.find('tr.mif-expandable-row');

    // Sort rows
    $rows.sort(function (a, b) {
      const $aCell = $(a).find('td').eq(columnIndex);
      const $bCell = $(b).find('td').eq(columnIndex);

      let aValue = $aCell.data('sort-value');
      let bValue = $bCell.data('sort-value');

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
      const $row = $(this);
      const targetId = $row.data('target');
      const $detailsRow = $('#' + targetId);

      // Append main row
      $tbody.append($row);

      // Append details row immediately after
      if ($detailsRow.length) {
        $tbody.append($detailsRow);
      }
    });
  });

  /* ==========================================================================
     3. SCAN CONTROL EVENT HANDLERS
     ========================================================================== */

  /**
   * Start Scan Button Handler
   *
   * Initiates media library scanning process. Resets global state,
   * updates UI elements, and begins batch processing at offset 0.
   *
   * @listens click - #start-scan button click
   * @returns {void}
   *
   * @note Sets isScanning flag to prevent duplicate scans
   * @note Clears previous inventoryData before starting
   * @note Updates button states and shows progress bar
   */
  $("#start-scan").on("click", function () {
    if (isScanning) return;

    isScanning = true;
    inventoryData = [];
    window.inventoryData = inventoryData;

    $("#start-scan").prop("disabled", true).text("scanning...").hide();
    $("#stop-scan").show();
    $("#scan-progress").show();
    $("#summary-stats").hide();
    $("#export-csv").hide();

    // Reset progress bar for new scan
    $("#progress-bar").css("width", "0%");
    $("#progress-text").text("0 / 0 processed");

    $("#results-container").html(
      '<div style="text-align: center; padding: 40px; color: var(--clr-txt); font-style: italic;">Scanning in progress...</div>'
    );

    scanBatch(0);
  });

  /**
   * Stop Scan Button Handler
   *
   * Cancels ongoing scan operation by setting isScanning flag to false.
   * Updates UI to show scan stopped state and enables control buttons.
   *
   * @listens click - #stop-scan button click
   * @returns {void}
   *
   * @note Scan stops after current batch completes
   * @note Data scanned so far is preserved
   */
  $("#stop-scan").on("click", function () {
    isScanning = false;
    $("#start-scan").prop("disabled", false).text("🔍 start scan").show();
    $("#stop-scan").hide();
    $("#scan-progress").hide();
    $("#export-csv").show();
    $("#results-container").html(
      '<div style="text-align: center; padding: 40px; color: var(--clr-txt); font-style: italic;">Scan stopped. Click "start scan" to continue.</div>'
    );
  });


  /**
   * Export CSV Button Handler
   *
   * Generates and downloads CSV file containing complete inventory data.
   * Creates temporary form and submits to WordPress AJAX handler.
   *
   * @listens click - #export-csv button click
   * @returns {void}
   *
   * @note Validates inventoryData exists before export
   * @note Uses POST method for data transmission
   * @note Removes temporary form after submission
   */
  $("#export-csv").on("click", function () {
    if (inventoryData.length === 0) {
      alert("No data to export");
      return;
    }

    // Create form and submit
    const form = $("<form>", {
      method: "POST",
      action: mifData.ajaxUrl,
    });

    form.append(
      $("<input>", {
        type: "hidden",
        name: "action",
        value: "media_inventory_export",
      })
    );

    form.append(
      $("<input>", {
        type: "hidden",
        name: "nonce",
        value: mifData.nonce,
      })
    );

    form.append(
      $("<input>", {
        type: "hidden",
        name: "inventory_data",
        value: JSON.stringify(inventoryData),
      })
    );

    $("body").append(form);
    form.submit();
    form.remove();
  });

  /**
   * Toggle All Sources Checkbox Handler
   *
   * Checks or unchecks all source filter checkboxes based on toggle state.
   * When in indeterminate state and clicked, checks all boxes.
   *
   * @since 4.0.0
   */
  $("#mif-toggle-all-sources").on("click", function () {
    var $this = $(this);

    // If currently indeterminate, next click should check all
    if (this.indeterminate) {
      $this.prop("checked", true);
      this.indeterminate = false;
      $(".mif-source-filter").prop("checked", true);
    } else {
      // Normal toggle behavior
      var isChecked = $this.prop("checked");
      $(".mif-source-filter").prop("checked", isChecked);
    }
  });

  /**
   * Update Toggle All checkbox when individual sources change
   *
   * Implements tri-state checkbox:
   * - Unchecked: no children checked
   * - Indeterminate (faded): some but not all children checked
   * - Checked: all children checked
   *
   * @since 4.0.0
   */
  $(document).on("change", ".mif-source-filter", function () {
    var totalSources = $(".mif-source-filter").length;
    var checkedSources = $(".mif-source-filter:checked").length;

    updateToggleCheckboxState(checkedSources, totalSources);
  });

  /* ==========================================================================
     4. AJAX COMMUNICATION & BATCH PROCESSING
     ========================================================================== */

  /**
   * Batch Scanner Function
   *
   * Performs single batch of media inventory scanning via AJAX request.
   * Processes media files starting from specified offset, handles progress
   * updates, error reporting, and automatic batch continuation.
   *
   * @function scanBatch
   * @param {number} offset - Starting position in media library for this batch
   * @returns {void} Updates UI and global state
   *
   * @note Implements 30-second timeout for AJAX requests
   * @note Adds 500ms delay between batches to prevent server overload
   * @note Checks isScanning flag at multiple points for cancellation
   * @note Accumulates results in global inventoryData array
   * @note Automatically continues until response.data.complete is true
   * @note Calls displayResults() when scan completes
   */
  function scanBatch(offset) {
    if (!isScanning) return; // Check if user stopped the scan

    // Get selected source filters
    var selectedSources = [];
    $(".mif-source-filter:checked").each(function () {
      selectedSources.push($(this).val());
    });

    $.post({
      url: mifData.ajaxUrl,
      data: {
        action: "media_inventory_scan",
        nonce: mifData.nonce,
        offset: offset,
        sources: selectedSources,
      },
      timeout: 30000, // 30 second timeout
    })
      .done(function (response) {
        if (!isScanning) return; // Check again in case user stopped during request

        if (response.success) {
          inventoryData = inventoryData.concat(response.data.data);
          window.inventoryData = inventoryData; // Keep window reference synchronized

          // Update progress
          const progress = Math.round(
            (response.data.processed / response.data.total) * 100
          );
          $("#progress-bar").css("width", progress + "%");
          $("#progress-text").text(
            response.data.processed + " / " + response.data.total + " processed"
          );

          if (response.data.complete) {
            // Scanning complete
            isScanning = false;
            $("#start-scan")
              .prop("disabled", false)
              .text("🔍 start scan")
              .show();
            $("#stop-scan").hide();
            $("#scan-progress").hide();
            $("#export-csv").show();

            displayResults();

            // Save scan results for table view
            $.post(ajaxurl, {
              action: 'mif_save_scan_results',
              nonce: mifData.nonce,
              scan_data: JSON.stringify(inventoryData)
            });

            // Trigger custom event for view toggle
            $(document).trigger('mif_scan_complete');
          } else {
            // Continue scanning
            setTimeout(function () {
              scanBatch(response.data.offset);
            }, 500); // Small delay between batches
          }
        } else {
          alert("Error: " + response.data);
          isScanning = false;
          $("#start-scan").prop("disabled", false).text("🔍 start scan").show();
          $("#stop-scan").hide();
          $("#scan-progress").hide();
        }
      })
      .fail(function (xhr, status, error) {
        if (!isScanning) return; // User already stopped

        let errorMsg = "AJAX request failed";
        if (status === "timeout") {
          errorMsg =
            "Request timed out - try reducing batch size or check server resources";
        } else if (xhr.responseText) {
          errorMsg = "Server error: " + xhr.responseText.substring(0, 200);
        }

        alert(errorMsg);
        isScanning = false;
        $("#start-scan").prop("disabled", false).text("🔍 start scan").show();
        $("#stop-scan").hide();
        $("#scan-progress").hide();
      });
  }

  /* ==========================================================================
     5. MAIN DISPLAY ORCHESTRATION
     ========================================================================== */

  /**
   * Master Results Display Function
   *
   * Orchestrates complete display of inventory results. Groups data by category,
   * generates HTML for each category section, and updates summary statistics.
   * All categories are rendered as collapsible sections for consistent UI.
   *
   * @function displayResults
   * @returns {void} Updates DOM elements directly
   *
   * @note Depends on global inventoryData array
   * @note Updates #results-container and #summary-stats elements
   * @note All categories use collapsible rendering for consistency
   * @note Calls specialized display handlers based on category type
   */
  function displayResults() {
    if (inventoryData.length === 0) {
      $("#results-container").html(
        '<div style="text-align: center; padding: 40px; color: var(--clr-txt); font-style: italic;">No media files found.</div>'
      );
      return;
    }

    // Category icon and behavior configuration
    const categoryConfig = {
      Images: {
        icon: "🖼️",
        toggleClass: "images-toggle",
        defaultExpanded: true,
      },
      PDFs: {
        icon: "📄",
        toggleClass: "pdfs-toggle",
        defaultExpanded: true,
      },
      Documents: {
        icon: "📋",
        toggleClass: "documents-toggle",
        defaultExpanded: true,
      },
      Fonts: {
        icon: "🔤",
        toggleClass: "fonts-toggle",
        defaultExpanded: true,
      },
    };

    // Group by category and calculate totals
    const categories = {};
    const totals = { files: 0, size: 0, items: 0 };

    inventoryData.forEach((item) => {
      if (!categories[item.category]) {
        categories[item.category] = {
          items: [],
          totalSize: 0,
          totalFiles: 0,
          itemCount: 0,
        };
      }

      categories[item.category].items.push(item);
      categories[item.category].totalSize += item.total_size;
      categories[item.category].totalFiles += item.file_count;
      categories[item.category].itemCount++;

      totals.files += item.file_count;
      totals.size += item.total_size;
      totals.items++;
    });

    // Build results HTML with enhanced category rendering
    let html = "";
    const orderedCategories = getOrderedCategories(categories);

    orderedCategories.forEach(function (catName) {
      const category = categories[catName];
      const config = categoryConfig[catName] || {
        icon: "📁",
        toggleClass: "",
        defaultExpanded: true,
      };

      // All categories are collapsible for consistent UI
      html += createCollapsibleCategorySection(catName, category, config);
    });

    $("#results-container").html(html);
    updateSummaryDisplay(categories, totals);
    updatePieChart(categories, totals);

    // Restore collapse states after rendering categories
    setTimeout(restoreCollapseStates, 50);
  }

  /**
   * Category Ordering Function
   *
   * Returns category names in predefined display order with alphabetical
   * fallback for any categories not explicitly ordered.
   *
   * @function getOrderedCategories
   * @param {Object} categories - Category data object with names as keys
   * @returns {Array<string>} Ordered array of category names
   *
   * @note Predefined order: Images, Fonts, SVG, Videos, Audio, PDFs, Documents, Text Files, Other Documents, Other
   * @note Only includes categories that exist in input object
   * @note Unknown categories appended alphabetically after predefined ones
   */
  function getOrderedCategories(categories) {
    const categoryOrder = [
      "Images",
      "Fonts",
      "SVG",
      "Videos",
      "Audio",
      "PDFs",
      "Documents",
      "Text Files",
      "Archives",
      "Other Documents",
      "Other",
    ];
    const orderedCategories = [];

    categoryOrder.forEach(function (catName) {
      if (categories[catName]) {
        orderedCategories.push(catName);
      }
    });

    Object.keys(categories)
      .sort()
      .forEach(function (catName) {
        if (!orderedCategories.includes(catName)) {
          orderedCategories.push(catName);
        }
      });

    return orderedCategories;
  }

  /**
   * Summary Statistics Display Updater
   *
   * Generates and displays summary statistics showing storage usage
   * by category with total at bottom.
   *
   * @function updateSummaryDisplay
   * @param {Object} categories - Category data with statistics
   * @param {Object} totals - Overall totals object
   * @returns {void} Updates #summary-content DOM element
   *
   * @note Uses getOrderedCategories() for consistent ordering
   * @note Final row shows total across all categories
   * @note Makes #summary-stats visible after update
   */
  function updateSummaryDisplay(categories, totals) {
    let summaryHtml = "";
    const orderedCategories = getOrderedCategories(categories);

    orderedCategories.forEach(function (catName) {
      const category = categories[catName];
      summaryHtml += `<div class="summary-item">`;
      summaryHtml += `<span>${catName} (${category.items.length}):</span>`;
      summaryHtml += `<span>${formatBytes(category.totalSize)}</span>`;
      summaryHtml += `</div>`;
    });

    summaryHtml += `<div class="summary-item">`;
    summaryHtml += `<span>Total:</span>`;
    summaryHtml += `<span>${formatBytes(totals.size)}</span>`;
    summaryHtml += `</div>`;

    $("#summary-content").html(summaryHtml);
    $("#summary-stats").show();
  }

  /**
   * File Distribution Pie Chart Renderer
   *
   * Draws a pie chart visualization of file type distribution by storage size.
   * Uses HTML5 Canvas for smooth, responsive graphics.
   *
   * @function updatePieChart
   * @param {Object} categories - Category data with totalSize for each type
   * @param {Object} totals - Overall totals object
   * @returns {void} Updates #file-distribution-chart DOM element
   */
  function updatePieChart(categories, totals) {
    const orderedCategories = getOrderedCategories(categories);
    const container = $("#file-distribution-chart");
    
    // Clear existing content
    container.empty();
    
    // Create canvas element
    const canvas = $('<canvas id="pie-chart-canvas" width="280" height="280"></canvas>');
    container.append(canvas);
    
    const ctx = canvas[0].getContext('2d');
    const centerX = 140;
    const centerY = 140;
    const radius = 110;
    
    // Color palette for pie slices (JimRWeb browns, golds, oranges)
    const colors = [
      '#f4c542', // Gold
      '#c97b3c', // Burnt orange
      '#6d4c2f', // Medium brown
      '#3d2f1f', // Dark brown
      '#e5b12d', // Light gold
      '#a8632e', // Dark orange
      '#dcc7a8', // Tan
      '#9C7A4D', // Light brown
      '#D4A574', // Beige
      '#8B6F47', // Darker tan
    ];
    
    // Calculate percentages
    let startAngle = -Math.PI / 2; // Start at top
    
    orderedCategories.forEach(function(catName, index) {
      const category = categories[catName];
      const percentage = category.totalSize / totals.size;
      const sliceAngle = percentage * 2 * Math.PI;
      
      // Draw slice
      ctx.fillStyle = colors[index % colors.length];
      ctx.beginPath();
      ctx.moveTo(centerX, centerY);
      ctx.arc(centerX, centerY, radius, startAngle, startAngle + sliceAngle);
      ctx.closePath();
      ctx.fill();
      
      // Draw border
      ctx.strokeStyle = '#FAF6F0'; // Page background color
      ctx.lineWidth = 2;
      ctx.stroke();
      
      startAngle += sliceAngle;
    });
    
    // Add legend below chart
    let legendHtml = '<div style="margin-top: 16px; font-size: 13px;">';
    orderedCategories.forEach(function(catName, index) {
      const category = categories[catName];
      const percentage = ((category.totalSize / totals.size) * 100).toFixed(1);
      legendHtml += `<div style="display: flex; align-items: center; margin-bottom: 6px;">`;
      legendHtml += `<div style="width: 14px; height: 14px; background: ${colors[index % colors.length]}; margin-right: 8px; border-radius: 2px;"></div>`;
      legendHtml += `<span style="color: var(--clr-txt); font-weight: 500;">${catName}: ${percentage}%</span>`;
      legendHtml += `</div>`;
    });
    legendHtml += '</div>';
    
    container.append(legendHtml);
  }

  /* ==========================================================================
     6. CATEGORY RENDERING SYSTEM
     ========================================================================== */

  /**
   * Collapsible Category Section Generator
   *
   * Creates complete HTML structure for collapsible category section
   * including toggle button, icon, statistics, and content area.
   *
   * @function createCollapsibleCategorySection
   * @param {string} categoryName - Display name of category
   * @param {Object} category - Category data object with items and statistics
   * @param {Object} config - Configuration object
   * @param {string} config.icon - Emoji icon for category
   * @param {string} config.toggleClass - CSS class for toggle button
   * @param {boolean} config.defaultExpanded - Whether to start expanded
   * @returns {string} Complete HTML for collapsible section
   *
   * @note Uses fcc-info-toggle-section wrapper structure
   * @note Applies expanded class if defaultExpanded is true
   * @note Displays item count, file count, and total size in header
   * @note Content generated by getCategoryContent()
   */
  function createCollapsibleCategorySection(categoryName, category, config) {
    const expandedClass = config.defaultExpanded ? "expanded" : "";
    const categoryContent = getCategoryContent(categoryName, category);

    // Create unique ID for this category section
    const sectionId = 'category-' + categoryName.toLowerCase().replace(/\s+/g, '-');

    let html = '<div class="fcc-info-toggle-section">';
    html += `<button class="fcc-info-toggle fcc-category-toggle ${config.toggleClass} ${expandedClass}" data-toggle-target="${sectionId}">`;
    html += `<span style="color: var(--clr-light-txt) !important;">`;
    html += `${config.icon} ${categoryName} (${category.itemCount} items, ${
      category.totalFiles
    } files, ${formatBytes(category.totalSize)})`;
    html += `</span>`;
    html += `<span class="fcc-toggle-icon" style="color: var(--clr-light-txt) !important;">▼</span>`;
    html += `</button>`;
    html += `<div class="fcc-info-content ${expandedClass}" id="${sectionId}">`;
    html += categoryContent;
    html += `</div>`;
    html += `</div>`;

    return html;
  }

  /**
   * Category Content Router
   *
   * Routes category to appropriate specialized display handler based
   * on category type. Falls back to default table display.
   *
   * For Images: Checks view mode toggle to show cards or table.
   * For all others: Always shows as table (current simple table format).
   *
   * @function getCategoryContent
   * @param {string} categoryName - Name of category
   * @param {Object} category - Category data object
   * @returns {string} HTML content for category
   *
   * @note Specialized handlers: Fonts, SVG, Images
   * @note All other categories use displayDefaultTable()
   * @note Images respects "Image Display Mode" toggle
   */
  function getCategoryContent(categoryName, category) {

    if (categoryName === "Fonts") {
      return displayFonts(category);
    } else if (categoryName === "SVG") {
      return displaySVG(category);
    } else if (categoryName === "Images") {
      // Check view mode - only Images respects the toggle
      const viewMode = $('input[name="mif-display-mode"]:checked').val() || 'card';
      if (viewMode === 'table') {
        return displayImagesTable(category);
      } else {
        return displayImagesCategory(category);
      }
    } else {
      return displayDefaultTable(category);
    }
  }

  /* ==========================================================================
     7. SPECIALIZED TYPE HANDLERS (FONTS, SVG, DEFAULT)
     ========================================================================== */

  /**
   * Font Display Handler
   *
   * Generates HTML table for font category grouped by font family.
   * Shows consolidated information including variants, file counts,
   * total sizes, and detailed breakdown.
   *
   * @function displayFonts
   * @param {Object} category - Font category object
   * @param {Array} category.items - Array of font items
   * @returns {string} HTML table with font families and variants
   *
   * @note Groups by font_family property, defaults to "Unknown Font"
   * @note Sorts families alphabetically
   * @note Variants column shows uppercase extensions
   * @note Details column lists individual fonts with sizes
   * @note Aggregates counts and sizes across all variants
   */
  function displayFonts(category) {
    const fontFamilies = {};
    category.items.forEach((item) => {
      const family = item.font_family || "Unknown Font";
      if (!fontFamilies[family]) {
        fontFamilies[family] = {
          items: [],
          totalSize: 0,
          totalFiles: 0,
        };
      }
      fontFamilies[family].items.push(item);
      fontFamilies[family].totalSize += item.total_size;
      fontFamilies[family].totalFiles += item.file_count;
    });

    let html = '<table class="inventory-table">';
    html +=
      "<thead><tr><th>Font Family</th><th>Variants</th><th>Files</th><th>Total Size</th><th>Details</th></tr></thead>";
    html += "<tbody>";

    Object.keys(fontFamilies)
      .sort()
      .forEach((familyName) => {
        const family = fontFamilies[familyName];

        // Get unique variants only (no duplicates)
        const uniqueVariants = [...new Set(family.items.map((item) => item.extension.toUpperCase()))];
        const variants = uniqueVariants.join(", ");

        // Get unique sources for this font family
        const uniqueSources = [...new Set(family.items.map((item) => item.source).filter(Boolean))];

        // Build font family name with source badge
        let familyNameHtml = "<strong>" + escapeHtml(familyName) + "</strong>";
        if (uniqueSources.length > 0) {
          uniqueSources.forEach(source => {
            const sourceClass = source === 'Media Library' ? 'source-media-library' : 'source-theme';
            familyNameHtml += '<br><span class="source-badge ' + sourceClass + '">' + escapeHtml(source) + '</span>';
          });
        }

        // Build details without source badges (since they're in the family name now)
        const details = family.items
          .map((item) => escapeHtml(item.title) + " (" + formatBytes(item.total_size) + ")")
          .join("<br>");

        html += "<tr>";
        html += "<td>" + familyNameHtml + "</td>";
        html += "<td>" + variants + "</td>";
        html += "<td>" + family.totalFiles + "</td>";
        html += "<td>" + formatBytes(family.totalSize) + "</td>";
        html += '<td class="file-details">' + details + "</td>";
        html += "</tr>";
      });

    html += "</tbody></table>";
    return html;
  }

  /**
   * SVG Display Handler
   *
   * Generates HTML table for SVG category with detailed file information
   * including dimensions, file counts, sizes, and individual file breakdown.
   *
   * @function displaySVG
   * @param {Object} category - SVG category object
   * @param {Array} category.items - Array of SVG items
   * @returns {string} HTML table with SVG inventory data
   *
   * @note Shows title, extension, dimensions, file count, total size
   * @note File details show type, size, and dimensions per file
   * @note Extensions displayed in uppercase
   * @note Handles missing dimensions with "Unknown" fallback
   */
  function displaySVG(category) {
    let html = '<table class="inventory-table">';
    html +=
      "<thead><tr><th>Title</th><th>Extension</th><th>Dimensions</th><th>Files</th><th>Size</th><th>File Details</th></tr></thead>";
    html += "<tbody>";

    category.items.forEach((item) => {
      const fileDetails = item.files
        .map((f) => {
          let detail = f.type + ": " + formatBytes(f.size);
          if (f.dimensions) {
            detail += " (" + f.dimensions + ")";
          }
          return detail;
        })
        .join("<br>");

      // Build title with source badge
      let titleHtml = escapeHtml(item.title);
      if (item.source) {
        const sourceClass = item.source === 'Media Library' ? 'source-media-library' : 'source-theme';
        titleHtml += '<br><span class="source-badge ' + sourceClass + '">' + escapeHtml(item.source) + '</span>';
      }

      html += "<tr>";
      html += "<td>" + titleHtml + "</td>";
      html += "<td>" + item.extension.toUpperCase() + "</td>";
      html += "<td>" + (item.dimensions || "Unknown") + "</td>";
      html += "<td>" + item.file_count + "</td>";
      html += "<td>" + formatBytes(item.total_size) + "</td>";
      html += '<td class="file-details">' + fileDetails + "</td>";
      html += "</tr>";
    });

    html += "</tbody></table>";
    return html;
  }

  /**
   * Default Table Display Handler
   *
   * Generates standard 5-column table for categories without specialized
   * formatting needs. Provides essential information for all media types.
   *
   * @function displayDefaultTable
   * @param {Object} category - Category object
   * @param {Array} category.items - Array of media items
   * @returns {string} HTML table with media inventory data
   *
   * @note Shows title, extension, file count, size, and file details
   * @note Extensions displayed in uppercase
   * @note File details show type and size per file
   * @note Serves as fallback for all unspecialized categories
   */
  function displayDefaultTable(category) {
    let html = '<table class="inventory-table">';
    html +=
      "<thead><tr><th>Title</th><th>Extension</th><th>Files</th><th>Size</th><th>File Details</th></tr></thead>";
    html += "<tbody>";

    category.items.forEach((item) => {
      const fileDetails = item.files
        .map((f) => {
          return f.type + ": " + formatBytes(f.size);
        })
        .join("<br>");

      // Build title with source badge
      let titleHtml = escapeHtml(item.title);
      if (item.source) {
        const sourceClass = item.source === 'Media Library' ? 'source-media-library' : 'source-theme';
        titleHtml += '<br><span class="source-badge ' + sourceClass + '">' + escapeHtml(item.source) + '</span>';
      }

      html += "<tr>";
      html += "<td>" + titleHtml + "</td>";
      html += "<td>" + item.extension.toUpperCase() + "</td>";
      html += "<td>" + item.file_count + "</td>";
      html += "<td>" + formatBytes(item.total_size) + "</td>";
      html += '<td class="file-details">' + fileDetails + "</td>";
      html += "</tr>";
    });

    html += "</tbody></table>";
    return html;
  }

  /* ==========================================================================
     8. IMAGE CATEGORY DISPLAY SYSTEM
     ========================================================================== */

  /**
   * Image Category Master Display Function
   *
   * Displays comprehensive view of image category with WordPress size analysis
   * and individual image cards. Groups images by WordPress standard size
   * categories and generates two main sections.
   *
   * @function displayImagesCategory
   * @param {Object} category - Image category object
   * @param {Array} category.items - Array of image items
   * @returns {string} Complete HTML with size summary and image cards
   *
   * @note Categorizes by filename width suffixes (-150, -300, -768, etc.)
   * @note Size ranges: ≤150px, 151-300px, 301-768px, 769-1024px, 1025-1536px, >1536px
   * @note Creates two sub-panels: WordPress Size Summary and Individual Image Cards
   * @note Extracts width from filename patterns like "-150", "-300x200"
   */
  function displayImagesCategory(category) {
    // Group images by WordPress size categories
    const wpSizeCategories = {};

    category.items.forEach((item) => {
      item.files.forEach((file) => {
        const filename = file.filename || "";
        let sizeCategory = "Original Files";
        let sizeSuffix = "original";

        // Extract size suffix from filename (e.g., "-150", "-300x200", "-768")
        const sizeMatch = filename.match(/-(\d+)(?:x\d+)?(?:\.[^.]+)?$/);
        if (sizeMatch) {
          const width = parseInt(sizeMatch[1]);
          sizeSuffix = "-" + width;

          // Categorize by WordPress standard sizes
          if (width <= 150) {
            sizeCategory = "Thumbnails (≤150px)";
          } else if (width <= 300) {
            sizeCategory = "Small (151-300px)";
          } else if (width <= 768) {
            sizeCategory = "Medium (301-768px)";
          } else if (width <= 1024) {
            sizeCategory = "Large (769-1024px)";
          } else if (width <= 1536) {
            sizeCategory = "Extra Large (1025-1536px)";
          } else {
            sizeCategory = "Super Large (>1536px)";
          }
        }

        if (!wpSizeCategories[sizeCategory]) {
          wpSizeCategories[sizeCategory] = {
            items: [],
            totalSize: 0,
            totalFiles: 0,
            sizeSuffixes: new Set(),
          };
        }

        wpSizeCategories[sizeCategory].items.push({
          ...item,
          currentFile: file,
        });
        wpSizeCategories[sizeCategory].totalSize += file.size;
        wpSizeCategories[sizeCategory].totalFiles++;
        wpSizeCategories[sizeCategory].sizeSuffixes.add(sizeSuffix);
      });
    });

    let html = "";

    const wpCategoryOrder = [
      "Original Files",
      "Thumbnails (≤150px)",
      "Small (151-300px)",
      "Medium (301-768px)",
      "Large (769-1024px)",
      "Extra Large (1025-1536px)",
      "Super Large (>1536px)",
    ];

    const sortedWpCategories = wpCategoryOrder.filter(
      (cat) => wpSizeCategories[cat]
    );

    // Create the WordPress Size Summary sub-panel
    const summaryContent = displaySizeSummary(
      wpSizeCategories,
      sortedWpCategories
    );
    html += createSubPanel("WordPress Size Summary", summaryContent, {
      margin: "16px 16px 8px 16px",
    });

    // Create Individual Image Cards sub-panel
    const cardsContent = displayImageCards(category);
    html += createSubPanel("Individual Image Cards", cardsContent, {
      margin: "0 16px 8px 16px",
    });

    return html;
  }

  /**
   * Image Table Display Function
   *
   * Displays Images category in expandable table format (when toggle is set to "Table").
   * Creates a table with expandable rows showing all image size variants.
   *
   * @function displayImagesTable
   * @param {Object} category - Images category object
   * @param {Array} category.items - Array of image items
   * @returns {string} HTML table with expandable image rows
   *
   * @note Shows thumbnail, title, source, file count, size, dimensions
   * @note Expandable rows reveal all image size variants
   * @note Maintains same structure as class-table-builder for consistency
   */
  function displayImagesTable(category) {
    let html = '<table class="mif-expandable-table widefat mif-sortable-table">';
    html += '<thead><tr>';
    html += '<th style="width: 40px;"></th>';
    html += '<th style="width: 80px;">Thumbnail</th>';
    html += '<th class="mif-sortable" data-column="title"><span class="mif-sort-label">Title</span><span class="mif-sort-indicator"></span></th>';
    html += '<th>Source</th>';
    html += '<th class="mif-sortable" data-column="files" style="width: 100px;"><span class="mif-sort-label">Files</span><span class="mif-sort-indicator"></span></th>';
    html += '<th class="mif-sortable" data-column="size" style="width: 120px;"><span class="mif-sort-label">Total Size</span><span class="mif-sort-indicator"></span></th>';
    html += '<th style="width: 140px;">Dimensions</th>';
    html += '</tr></thead>';
    html += '<tbody>';

    category.items.forEach((item) => {
      const rowId = 'image-' + String(item.id).replace(/[^a-z0-9]/gi, '-');

      // Main row
      html += '<tr class="mif-expandable-row" data-target="' + rowId + '">';
      html += '<td><span class="dashicons dashicons-plus-alt2 mif-expand-icon"></span></td>';

      // Thumbnail
      html += '<td>';
      if (item.thumbnail_url) {
        html += '<img src="' + escapeHtml(item.thumbnail_url) + '" alt="' + escapeHtml(item.title) + '" style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px;" />';
      } else {
        html += '<div style="width: 60px; height: 60px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; border-radius: 4px;">📷</div>';
      }
      html += '</td>';

      html += '<td data-sort-value="' + escapeHtml(item.title.toLowerCase()) + '"><strong>' + escapeHtml(item.title) + '</strong></td>';

      // Source
      html += '<td>';
      if (item.source) {
        const sourceClass = item.source === 'Media Library' ? 'source-media-library' : 'source-theme';
        html += '<span class="source-badge ' + sourceClass + '">' + escapeHtml(item.source) + '</span>';
      }
      html += '</td>';

      html += '<td data-sort-value="' + item.file_count + '">' + item.file_count + '</td>';
      html += '<td data-sort-value="' + item.total_size + '">' + formatBytes(item.total_size) + '</td>';
      html += '<td>' + escapeHtml(item.dimensions || 'N/A') + '</td>';
      html += '</tr>';

      // Expanded details row
      html += '<tr class="mif-expanded-details" id="' + rowId + '" style="display: none;">';
      html += '<td colspan="7">';
      html += '<div style="padding: 12px; background: #f9f9f9;">';
      html += '<table class="mif-details-table" style="width: 100%; border-collapse: collapse;">';
      html += '<tr style="background: #e0e0e0; font-weight: 600;"><td>File</td><td>Type</td><td>Dimensions</td><td>Size</td></tr>';

      item.files.forEach((file) => {
        html += '<tr>';
        html += '<td>' + escapeHtml(file.filename || 'Unknown') + '</td>';
        html += '<td>' + escapeHtml(file.type) + '</td>';
        html += '<td>' + escapeHtml(file.dimensions || 'N/A') + '</td>';
        html += '<td>' + formatBytes(file.size) + '</td>';
        html += '</tr>';
      });

      html += '</table></div></td></tr>';
    });

    html += '</tbody></table>';

    return html;
  }

  /**
   * WordPress Size Summary Display Generator
   *
   * Creates 3-column grid layout showing WordPress image size category
   * statistics including file counts, total sizes, and filename suffixes.
   *
   * @function displaySizeSummary
   * @param {Object} wpSizeCategories - WordPress size category data
   * @param {Array<string>} sortedWpCategories - Ordered category names
   * @returns {string} HTML with 3-column grid of size statistics
   *
   * @note Distributes categories across columns using modulo operation (index % 3)
   * @note Each column is styled card with white background and shadow
   * @note Shows category name, suffixes list, file count, and total size
   * @note Uses round-robin distribution for balanced visual presentation
   */
  function displaySizeSummary(wpSizeCategories, sortedWpCategories) {
    let summaryContent =
      '<div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">';

    // Split into three columns more evenly
    const leftColumn = [];
    const middleColumn = [];
    const rightColumn = [];

    sortedWpCategories.forEach((cat, index) => {
      if (index % 3 === 0) {
        leftColumn.push(cat);
      } else if (index % 3 === 1) {
        middleColumn.push(cat);
      } else {
        rightColumn.push(cat);
      }
    });

    // Left column
    summaryContent +=
      '<div style="background: white; border-radius: var(--jimr-border-radius); padding: 12px; box-shadow: var(--clr-shadow); border: 1px solid var(--jimr-gray-200);">';
    leftColumn.forEach((categoryName) => {
      const wpCategory = wpSizeCategories[categoryName];
      const leftSizeSuffixList = Array.from(wpCategory.sizeSuffixes).join(", ");

      summaryContent +=
        '<div style="padding: 8px 0; border-bottom: 1px solid var(--jimr-gray-200);">';
      summaryContent +=
        '<div><strong style="color: var(--clr-secondary);">' +
        escapeHtml(categoryName) +
        "</strong><br>";
      summaryContent +=
        '<small style="color: var(--clr-txt);">Suffixes: ' +
        leftSizeSuffixList +
        "</small><br>";
      summaryContent +=
        '<small style="color: var(--clr-txt);">' +
        wpCategory.totalFiles +
        " files, " +
        formatBytes(wpCategory.totalSize) +
        "</small></div>";
      summaryContent += "</div>";
    });
    summaryContent += "</div>";

    // Middle column
    summaryContent +=
      '<div style="background: white; border-radius: var(--jimr-border-radius); padding: 12px; box-shadow: var(--clr-shadow); border: 1px solid var(--jimr-gray-200);">';
    middleColumn.forEach((categoryName) => {
      const wpCategory = wpSizeCategories[categoryName];
      const middleSizeSuffixList = Array.from(wpCategory.sizeSuffixes).join(
        ", "
      );

      summaryContent +=
        '<div style="padding: 8px 0; border-bottom: 1px solid var(--jimr-gray-200);">';
      summaryContent +=
        '<div><strong style="color: var(--clr-secondary);">' +
        escapeHtml(categoryName) +
        "</strong><br>";
      summaryContent +=
        '<small style="color: var(--clr-txt);">Suffixes: ' +
        middleSizeSuffixList +
        "</small><br>";
      summaryContent +=
        '<small style="color: var(--clr-txt);">' +
        wpCategory.totalFiles +
        " files, " +
        formatBytes(wpCategory.totalSize) +
        "</small></div>";
      summaryContent += "</div>";
    });
    summaryContent += "</div>";

    // Right column
    summaryContent +=
      '<div style="background: white; border-radius: var(--jimr-border-radius); padding: 12px; box-shadow: var(--clr-shadow); border: 1px solid var(--jimr-gray-200);">';
    rightColumn.forEach((categoryName) => {
      const wpCategory = wpSizeCategories[categoryName];
      const rightSizeSuffixList = Array.from(wpCategory.sizeSuffixes).join(
        ", "
      );

      summaryContent +=
        '<div style="padding: 8px 0; border-bottom: 1px solid var(--jimr-gray-200);">';
      summaryContent +=
        '<div><strong style="color: var(--clr-secondary);">' +
        escapeHtml(categoryName) +
        "</strong><br>";
      summaryContent +=
        '<small style="color: var(--clr-txt);">Suffixes: ' +
        rightSizeSuffixList +
        "</small><br>";
      summaryContent +=
        '<small style="color: var(--clr-txt);">' +
        wpCategory.totalFiles +
        " files, " +
        formatBytes(wpCategory.totalSize) +
        "</small></div>";
      summaryContent += "</div>";
    });
    summaryContent += "</div>";

    summaryContent += "</div>";
    return summaryContent;
  }

  /**
   * Image Cards Display Generator
   *
   * Generates 3-column grid of individual image cards showing thumbnails,
   * metadata, and detailed file information for each image item.
   *
   * @function displayImageCards
   * @param {Object} category - Image category object
   * @param {Array} category.items - Array of image items
   * @returns {string} HTML grid with image cards
   *
   * @note Uses CSS grid with 3 equal columns and 16px gap
   * @note Each card shows thumbnail, title, file count, size, dimensions
   * @note Includes error handling for missing thumbnails
   * @note Images use lazy loading for performance
   * @note Lists all associated files with type, dimensions, and size
   */
  function displayImageCards(category) {
    let cardsContent =
      '<div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">';

    category.items.forEach((item) => {
      cardsContent += '<div class="image-item">';
      cardsContent += '<div class="image-header">';

      // Add thumbnail if available
      if (item.thumbnail_url) {
        cardsContent += '<div class="image-thumbnail">';
        cardsContent += '<img src="' + escapeHtml(item.thumbnail_url) + '" ';
        cardsContent += 'alt="' + escapeHtml(item.title) + '" ';
        cardsContent += 'title="' + escapeHtml(item.title) + '" ';
        cardsContent += 'loading="lazy" ';
        cardsContent +=
          "onerror=\"this.parentElement.innerHTML='<div style=\\'display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 4px; text-align: center;\\'><span style=\\'font-size: 24px;\\'>📷</span><small style=\\'font-size: 10px; color: #999;\\'>Preview unavailable</small></div>';\" />";
        cardsContent += "</div>";
      } else {
        cardsContent += '<div class="image-thumbnail">';
        cardsContent += '<div style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 4px; text-align: center;">';
        cardsContent += '<span style="font-size: 24px;">📷</span>';
        cardsContent += '<small style="font-size: 10px; color: #999;">No preview</small>';
        cardsContent += '</div>';
        cardsContent += '</div>';
      }

      cardsContent += '<div class="image-info">';
      cardsContent += "<strong>" + escapeHtml(item.title) + "</strong><br>";

      // Add source badge if present
      if (item.source) {
        const sourceClass = item.source === 'Media Library' ? 'source-media-library' : 'source-theme';
        cardsContent += '<span class="source-badge ' + sourceClass + '">' + escapeHtml(item.source) + '</span><br>';
      }

      cardsContent +=
        '<span class="image-stats">(' +
        item.file_count +
        " files, " +
        formatBytes(item.total_size) +
        ")</span>";
      if (item.dimensions) {
        cardsContent +=
          '<br><span class="main-dimensions">Original: ' +
          item.dimensions +
          "</span>";
      }
      cardsContent += "</div>";
      cardsContent += "</div>";

      cardsContent += '<div class="image-files">';
      item.files.forEach((file) => {
        cardsContent += '<div class="file-item">';
        cardsContent +=
          '<span class="filename">' +
          escapeHtml(file.filename || "Unknown file") +
          "</span> ";
        cardsContent += '<span class="file-type">(' + file.type + ")</span> - ";
        cardsContent +=
          '<span class="file-dimensions">' +
          (file.dimensions || "Unknown size") +
          "</span> - ";
        cardsContent +=
          '<span class="file-size">' + formatBytes(file.size) + "</span>";
        cardsContent += "</div>";
      });
      cardsContent += "</div>";
      cardsContent += "</div>";
    });

    cardsContent += "</div>";
    return cardsContent;
  }

  /* ==========================================================================
     9. UI COMPONENT BUILDERS
     ========================================================================== */

  /**
   * Sub-Panel Component Builder
   *
   * Creates styled sub-panel component with header and content sections.
   * Used for creating nested panels within category sections.
   *
   * @function createSubPanel
   * @param {string} headerText - Text for panel header
   * @param {string} contentHtml - HTML content for panel body
   * @param {Object} [options={}] - Optional configuration
   * @param {string} [options.margin="8px 16px 8px 16px"] - CSS margin property
   * @param {string} [options.headerColor="var(--clr-sub-panel-header)"] - Header background color
   * @returns {string} Complete HTML for sub-panel component
   *
   * @note Uses CSS custom properties for consistent theming
   * @note Header has fixed styling with white text and 600 font-weight
   * @note Content area has consistent 16px padding
   */
  function createSubPanel(headerText, contentHtml, options = {}) {
    const margin = options.margin || "8px 16px 8px 16px";
    const headerColor = options.headerColor || "var(--clr-sub-panel-header)";

    let html = "";
    html += `<div style="background: var(--clr-light); margin: ${margin}; border-radius: var(--jimr-border-radius-lg); border: 1px solid var(--clr-secondary); box-shadow: var(--clr-shadow);">`;
    html += `<div style="background: ${headerColor}; color: color: var(--clr-light-txt); padding: 8px 12px; font-weight: 600; font-size: 14px;">${headerText}</div>`;
    html += `<div style="padding: 16px;">${contentHtml}</div>`;
    html += `</div>`;

    return html;
  }

  /* ==========================================================================
     10. UTILITY FUNCTIONS
     ========================================================================== */

  /**
   * Byte Formatter Utility
   *
   * Converts byte count into human-readable string with appropriate units.
   * Uses binary (1024-based) units for conversion.
   *
   * @function formatBytes
   * @param {number} bytes - Number of bytes to format
   * @returns {string} Formatted string with value and unit (e.g., "1.5 MB")
   *
   * @note Uses binary units: B, KB, MB, GB, TB
   * @note Results rounded to 2 decimal places
   * @note Handles negative numbers by treating as 0
   */
  function formatBytes(bytes) {
    const units = ["B", "KB", "MB", "GB", "TB"];
    let size = Math.max(bytes, 0);
    let pow = Math.floor(Math.log(size) / Math.log(1024));
    pow = Math.min(pow, units.length - 1);
    size /= Math.pow(1024, pow);
    return Math.round(size * 100) / 100 + " " + units[pow];
  }

  /**
   * HTML Escape Utility
   *
   * Escapes HTML special characters to prevent XSS attacks and ensure
   * safe rendering in HTML contexts. Uses browser's native HTML escaping.
   *
   * @function escapeHtml
   * @param {string} text - Text string to escape
   * @returns {string} Escaped HTML-safe string
   *
   * @note Leverages browser's textContent and innerHTML properties
   * @note Creates temporary DOM element for conversion
   * @note Handles all HTML special characters
   */
  function escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
  }

  /* ==========================================================================
     INITIALIZATION
     ========================================================================== */

  // Restore collapse states for About panel on page load
  restoreCollapseStates();

  /* ==========================================================================
     END OF MEDIA INVENTORY FORGE ADMIN JAVASCRIPT
     ========================================================================== */
});
