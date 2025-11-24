<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Explorer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-okaidia.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/diff2html/bundles/css/diff2html.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">
</head>
<body>

<?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Log Explorer</a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <button class="btn btn-link nav-link" id="theme-toggle" title="Toggle Dark Mode">
                            <i class="bi bi-moon-fill"></i>
                        </button>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="api/auth/logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container-fluid mt-3">
        <div class="row">
            <div class="col-md-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0">Groups</h4>
                    <button class="btn btn-sm btn-outline-secondary" id="collapse-all-groups">
                        <i class="bi bi-arrows-collapse"></i>
                    </button>
                </div>
                <div class="mb-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" id="group-search" placeholder="Search customers, SKUs...">
                        <button class="btn btn-outline-secondary" type="button" id="clear-group-search" style="display: none;">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                </div>
                <div id="group-cards" class="group-cards-container">
                    <!-- Cards will be loaded here -->
                </div>
            </div>
            <div class="col-md-9">
                <!-- Search Section -->
                <div class="card-modern">
                    <h4 class="mb-3">Search</h4>
                    <form id="search-form" class="row g-3">
                        <div class="col-md-4">
                            <label for="search-date-range" class="form-label">Date Range</label>
                            <input type="text" class="form-control" id="search-date-range" placeholder="Select date range...">
                        </div>
                        <div class="col-md-3">
                            <label for="search-customer" class="form-label">Customer</label>
                            <select class="form-select" id="search-customer">
                                <option value="">All Customers</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="search-sku" class="form-label">SKU</label>
                            <select class="form-select" id="search-sku">
                                <option value="">All SKUs</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Search</button>
                        </div>
                        <div class="col-md-12 d-flex justify-content-end">
                            <button type="reset" class="btn btn-outline-secondary btn-sm">Reset Filters</button>
                        </div>
                    </form>
                </div>

                <!-- Logs Table Section -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0">Logs</h4>
                </div>
                <div class="table-container">
                    <table id="logs-table" class="table table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th style="width: 40px;">
                                    <input type="checkbox" id="select-all-checkbox" title="Select All">
                                </th>
                                <th>File Name</th>
                                <th style="width: 100px;">File Size</th>
                                <th style="width: 180px;">Modification Time</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Log Preview Modal -->
    <div id="log-preview-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="log-filename"></h2>
                <span class="close" role="button" aria-label="Close">&times;</span>
            </div>
            <div class="modal-body">
                <div class="controls">
                    <button id="toggle-theme">Dark Mode</button>
                    <button id="view-markdown" style="display:none;">View Markdown</button>
                    <button id="copy-log">Copy</button>
                    <button id="download-log">Download</button>
                    <button id="toggle-line-numbers">Show Line Numbers</button>
                    <input type="text" id="log-search-input" placeholder="Search log...">
                    <p id="log-details"></p>
                </div>
                <pre id="log-content"></pre>
                <div id="markdown-view" class="markdown-view" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button id="prev-log" disabled>&laquo; Prev</button>
                <button id="next-log" disabled>Next &raquo;</button>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="login-container">
        <div class="login-theme-toggle">
            <button class="btn btn-link" id="theme-toggle" title="Toggle Dark Mode">
                <i class="bi bi-moon-fill"></i>
            </button>
        </div>
        <div class="login-card">
            <div class="login-header">
                <h1>Log Explorer</h1>
                <p class="login-subtitle">Sign in to continue</p>
            </div>
            <div class="login-body">
                <form id="loginForm">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Login</button>
                    </div>
                    <div id="error-message" class="alert alert-danger mt-3" style="display: none;"></div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-core.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/autoloader/prism-autoloader.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/diff@5.1.0/dist/diff.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/diff2html@3.4.47/bundles/js/diff2html.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="js/scripts.js"></script>
<script>
$(document).ready(function() {
    <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
    // Initialize DataTable
    const logsTable = $('#logs-table').DataTable({
        data: [],
        columns: [
            { 
                data: null,
                orderable: false,
                className: 'select-checkbox',
                defaultContent: '<input type="checkbox" class="row-checkbox">'
            },
            { data: 'name' },
            { data: 'size' },
            { data: 'modified' }
        ]
    });

    // Load groups and populate search dropdowns
    $.ajax({
        url: 'api/groups.php',
        dataType: 'json',
        success: function(groups) {
            const groupCards = $('#group-cards');
            const customerSelect = $('#search-customer');
            const skuSelect = $('#search-sku');
            const addedSkus = new Set();

            groupCards.empty();
            customerSelect.find('option:not(:first)').remove();
            skuSelect.find('option:not(:first)').remove();

            Object.entries(groups).forEach(([customer, skus]) => {
                // Add to customer dropdown
                customerSelect.append($('<option>', { value: customer, text: customer }));

                // Create customer card
                const card = $('<div>', { class: 'customer-card' });
                
                // Card header
                const cardHeader = $('<div>', { class: 'customer-card-header' });
                cardHeader.html(`
                    <div class="customer-name">
                        <i class="bi bi-building"></i>
                        <span>${customer}</span>
                    </div>
                    <div class="customer-stats">
                        <span class="badge bg-secondary">${Object.keys(skus).length} SKUs</span>
                        <i class="bi bi-chevron-down card-chevron"></i>
                    </div>
                `);
                card.append(cardHeader);

                // Card body with SKU badges
                const cardBody = $('<div>', { class: 'customer-card-body' });
                const skuContainer = $('<div>', { class: 'sku-badges' });

                Object.entries(skus).forEach(([sku, dates]) => {
                    // Add to SKU dropdown
                    if (!addedSkus.has(sku)) {
                        skuSelect.append($('<option>', { value: sku, text: sku }));
                        addedSkus.add(sku);
                    }

                    // Create SKU badge
                    const skuBadge = $('<div>', { 
                        class: 'sku-badge',
                        'data-customer': customer,
                        'data-sku': sku
                    });
                    
                    // Create badge header with name and count
                    const badgeHeader = $('<div>', { class: 'sku-badge-header' });
                    badgeHeader.html(`
                        <span class="sku-name">${sku}</span>
                        <span class="sku-count">${dates.length}</span>
                    `);
                    skuBadge.append(badgeHeader);

                    // Create date list (hidden by default)
                    const dateList = $('<div>', { class: 'date-list' });
                    dates.forEach(date => {
                        const dateItem = $('<a>', {
                            href: '#',
                            class: 'date-item',
                            'data-customer': customer,
                            'data-sku': sku,
                            'data-date': date
                        });
                        dateItem.html(`
                            <i class="bi bi-calendar3"></i>
                            <span>${date}</span>
                        `);
                        dateList.append(dateItem);
                    });

                    skuBadge.append(dateList);
                    skuContainer.append(skuBadge);
                });

                cardBody.append(skuContainer);
                card.append(cardBody);
                        groupCards.append(card);
            });
        }
    });

    // Toggle card collapse/expand
    $('#group-cards').on('click', '.customer-card-header', function(e) {
        e.stopPropagation();
        const card = $(this).closest('.customer-card');
        const cardBody = card.find('.customer-card-body');
        
        card.toggleClass('collapsed');
        cardBody.slideToggle(200);
    });

    // Initialize date range picker
    const dateRangePicker = flatpickr("#search-date-range", {
        mode: "range",
        dateFormat: "Y-m-d",
        maxDate: "today",
        locale: {
            rangeSeparator: " to "
        }
    });

    // Search form submission
    $('#search-form').on('submit', function(e) {
        e.preventDefault();
        
        const dateRange = $('#search-date-range').val();
        let startDate = '';
        let endDate = '';
        
        // Parse date range
        if (dateRange) {
            const dates = dateRange.split(' to ');
            startDate = dates[0] || '';
            endDate = dates[1] || dates[0] || ''; // If only one date, use it for both
        }
        
        const searchData = {
            start: startDate,
            end: endDate,
            customer: $('#search-customer').val(),
            sku: $('#search-sku').val()
        };

        $.ajax({
            url: 'api/search.php',
            data: searchData,
            dataType: 'json',
            success: function(response) {
                logsTable.clear();
                logsTable.rows.add(response.data);
                logsTable.draw();
            }
        });
    });

    $('#search-form').on('reset', function() {
        dateRangePicker.clear();
        logsTable.clear().draw();
        selectedFiles.clear();
    });

    // SKU badge click - toggle date list
    $('#group-cards').on('click', '.sku-badge', function(e) {
        e.stopPropagation();
        const $this = $(this);
        
        // Close other open date lists in the same card
        $this.siblings('.sku-badge').removeClass('active').find('.date-list').slideUp(200);
        
        // Toggle this date list
        $this.toggleClass('active');
        $this.find('.date-list').slideToggle(200);
    });

    // Date item click - load logs
    $('#group-cards').on('click', '.date-item', function(e) {
        e.preventDefault();

        // Remove active class from all date items
        $('.date-item').removeClass('active');
        $(this).addClass('active');

        const customer = $(this).data('customer');
        const sku = $(this).data('sku');
        const date = $(this).data('date');

        $.ajax({
            url: 'api/logs.php',
            data: { customer, sku, date },
            dataType: 'json',
            success: function(response) {
                logsTable.clear();
                logsTable.rows.add(response.data);
                logsTable.draw();
            }
        });
    });

    // Collapse all groups
    $('#collapse-all-groups').on('click', function() {
        $('.sku-badge').removeClass('active');
        $('.date-list').slideUp(200);
    });

    // Group search functionality
    $('#group-search').on('input', function() {
        const searchTerm = $(this).val().toLowerCase().trim();
        const clearBtn = $('#clear-group-search');
        
        // Show/hide clear button
        if (searchTerm) {
            clearBtn.show();
        } else {
            clearBtn.hide();
        }
        
        // If empty, show all cards
        if (!searchTerm) {
            $('.customer-card').show();
            $('.sku-badge').show();
            return;
        }
        
        // Filter cards
        $('.customer-card').each(function() {
            const $card = $(this);
            const customerName = $card.find('.customer-name span').text().toLowerCase();
            let hasMatch = false;
            
            // Check if customer name matches
            if (customerName.includes(searchTerm)) {
                hasMatch = true;
                $card.show();
                // Expand card if collapsed
                if ($card.hasClass('collapsed')) {
                    $card.removeClass('collapsed');
                    $card.find('.customer-card-body').slideDown(200);
                }
                // Show all SKUs in this card
                $card.find('.sku-badge').show();
            } else {
                // Check SKUs and dates
                let hasMatchingSku = false;
                $card.find('.sku-badge').each(function() {
                    const $badge = $(this);
                    const skuName = $badge.find('.sku-name').text().toLowerCase();
                    const dates = $badge.find('.date-item span').map(function() {
                        return $(this).text().toLowerCase();
                    }).get();
                    
                    // Check if SKU name or any date matches
                    if (skuName.includes(searchTerm) || dates.some(date => date.includes(searchTerm))) {
                        $badge.show();
                        hasMatchingSku = true;
                    } else {
                        $badge.hide();
                    }
                });
                
                if (hasMatchingSku) {
                    hasMatch = true;
                    $card.show();
                    // Expand card if collapsed
                    if ($card.hasClass('collapsed')) {
                        $card.removeClass('collapsed');
                        $card.find('.customer-card-body').slideDown(200);
                    }
                } else {
                    $card.hide();
                }
            }
        });
    });

    // Clear search button
    $('#clear-group-search').on('click', function() {
        $('#group-search').val('').trigger('input');
        $(this).hide();
    });

    // Batch selection functionality
    let selectedFiles = new Set();
    let lastCheckedIndex = -1; // Track last checked row for shift-click



    // Select all checkbox
    $('#select-all-checkbox').on('change', function() {
        const isChecked = $(this).prop('checked');
        selectedFiles.clear();
        
        $('#logs-table tbody .row-checkbox').each(function() {
            $(this).prop('checked', isChecked);
            const row = $(this).closest('tr');
            const path = row.find('.file-name').data('path');
            
            if (isChecked) {
                selectedFiles.add(path);
                row.addClass('table-active');
            } else {
                row.removeClass('table-active');
            }
        });
    });

    // Individual row checkbox with Shift-click support
    $('#logs-table tbody').on('click', '.row-checkbox', function(e) {
        e.stopPropagation();
        
        const $checkbox = $(this);
        const currentIndex = $checkbox.closest('tr').index();
        const isChecked = $checkbox.prop('checked');
        
        // Handle Shift+Click for range selection
        if (e.shiftKey && lastCheckedIndex !== -1) {
            const start = Math.min(lastCheckedIndex, currentIndex);
            const end = Math.max(lastCheckedIndex, currentIndex);
            const $rows = $('#logs-table tbody tr');
            
            // Sync all rows in range to the current checkbox's state
            for (let i = start; i <= end; i++) {
                // Skip the current row as it's already handled
                if (i === currentIndex) continue;
                
                const $cb = $rows.eq(i).find('.row-checkbox');
                if ($cb.prop('checked') !== isChecked) {
                    $cb.prop('checked', isChecked).trigger('change');
                }
            }
        }
        
        lastCheckedIndex = currentIndex;
    });

    // Handle state changes (triggered by click or programmatic change)
    $('#logs-table tbody').on('change', '.row-checkbox', function() {
        const $checkbox = $(this);
        const $row = $checkbox.closest('tr');
        const path = $row.find('.file-name').data('path');
        const isChecked = $checkbox.prop('checked');
        
        if (isChecked) {
            selectedFiles.add(path);
            $row.addClass('table-active');
        } else {
            selectedFiles.delete(path);
            $row.removeClass('table-active');
        }
        
    });

    // Click on row to toggle checkbox
    $('#logs-table tbody').on('click', 'tr', function(e) {
        // Don't toggle if clicking on action buttons or checkbox itself
        if ($(e.target).hasClass('row-checkbox')) {
            return;
        }
        
        const $checkbox = $(this).find('.row-checkbox');
        const isChecked = !$checkbox.prop('checked');
        const currentIndex = $(this).index();
        
        // Handle Shift+Click on row
        if (e.shiftKey && lastCheckedIndex !== -1) {
            const start = Math.min(lastCheckedIndex, currentIndex);
            const end = Math.max(lastCheckedIndex, currentIndex);
            const $rows = $('#logs-table tbody tr');
            
            for (let i = start; i <= end; i++) {
                const $cb = $rows.eq(i).find('.row-checkbox');
                // Determine target state: match the clicked row's new state
                if ($cb.prop('checked') !== isChecked) {
                    $cb.prop('checked', isChecked).trigger('change');
                }
            }
        } else {
            // Normal toggle
            $checkbox.prop('checked', isChecked).trigger('change');
        }
        
        lastCheckedIndex = currentIndex;
    });

    // Double-click on row to preview
    $('#logs-table tbody').on('dblclick', 'tr', function(e) {
        const path = $(this).find('.file-name').data('path');
        if (path) {
            showLogPreview(path);
        }
    });

    function performBatchDownload() {
        if (selectedFiles.size === 0) {
            alert('Please select at least one file to download.');
            return;
        }

        const paths = Array.from(selectedFiles);
        
        if (paths.length === 1) {
            // Single file - direct download
            window.location.href = `api/download.php?path=${encodeURIComponent(paths[0])}`;
        } else {
            // Multiple files - batch download
            const form = $('<form>', {
                method: 'POST',
                action: 'api/batch_download.php'
            });
            
            paths.forEach(path => {
                form.append($('<input>', {
                    type: 'hidden',
                    name: 'paths[]',
                    value: path
                }));
            });
            
            $('body').append(form);
            form.submit();
            form.remove();
        }
    }

    // Clear selection when table is cleared
    $('#search-form').on('reset', function() {
        selectedFiles.clear();
        $('#select-all-checkbox').prop('checked', false);
        logsTable.clear().draw();
    });

    // Custom Context Menu
    const contextMenu = $('#custom-context-menu');
    let contextMenuTargetRow = null;

    // Show context menu on right-click
    $('#logs-table tbody').on('contextmenu', 'tr', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        contextMenuTargetRow = $(this);
        const path = contextMenuTargetRow.find('.file-name').data('path');
        
        // If no path, don't show menu
        if (!path) return false;
        
        // Get menu dimensions (show it first to measure)
        contextMenu.css({
            display: 'block',
            visibility: 'hidden',
            left: '0px',
            top: '0px'
        });
        
        const menuWidth = contextMenu.outerWidth();
        const menuHeight = contextMenu.outerHeight();
        const windowWidth = $(window).width();
        const windowHeight = $(window).height();
        const scrollTop = $(window).scrollTop();
        const scrollLeft = $(window).scrollLeft();
        
        // Calculate position (use clientX/clientY + scroll offset)
        let left = e.clientX + scrollLeft;
        let top = e.clientY + scrollTop;
        
        // Adjust if menu would go off-screen (viewport coordinates)
        if (e.clientX + menuWidth > windowWidth) {
            left = e.clientX + scrollLeft - menuWidth;
        }
        if (e.clientY + menuHeight > windowHeight) {
            top = e.clientY + scrollTop - menuHeight;
        }
        
        // Position the context menu and make it visible
        contextMenu.css({
            visibility: 'visible',
            left: left + 'px',
            top: top + 'px'
        });
        
        // Update batch download menu item state
        const batchDownloadItem = $('#ctx-batch-download');
        if (selectedFiles.size > 1) {
            batchDownloadItem.show();
            batchDownloadItem.removeClass('disabled');
            batchDownloadItem.html(`<i class="bi bi-download"></i> Batch Download <span class="badge-count">${selectedFiles.size}</span>`);
        } else {
            batchDownloadItem.hide();
        }
        
        // Update compare menu item state (show only when exactly 2 files selected)
        // Update compare menu item state (show only when exactly 2 files selected)
        const compareItem = $('#ctx-compare');
        if (selectedFiles.size === 2) {
            compareItem.show();
        } else {
            compareItem.hide();
        }

        // Handle separators visibility
        const separatorBatch = $('#ctx-separator-batch');
        const separatorCompare = $('#ctx-separator-compare');

        if (batchDownloadItem.is(':visible')) {
            separatorBatch.show();
        } else {
            separatorBatch.hide();
        }

        if (compareItem.is(':visible')) {
            separatorCompare.show();
        } else {
            separatorCompare.hide();
        }
        
        return false;
    });

    // Hide context menu on click outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.context-menu').length) {
            contextMenu.hide();
        }
    });

    // Hide context menu on ESC key
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && contextMenu.is(':visible')) {
            contextMenu.hide();
        }
    });

    // Prevent default context menu on the custom menu itself
    contextMenu.on('contextmenu', function(e) {
        e.preventDefault();
        return false;
    });

    // Context menu actions
    $('#ctx-preview').on('click', function() {
        if (contextMenuTargetRow) {
            const path = contextMenuTargetRow.find('.file-name').data('path');
            if (path) {
                showLogPreview(path);
            }
        }
        contextMenu.hide();
    });

    $('#ctx-download').on('click', function() {
        if (contextMenuTargetRow) {
            const path = contextMenuTargetRow.find('.file-name').data('path');
            if (path) {
                window.location.href = `api/download.php?path=${encodeURIComponent(path)}`;
            }
        }
        contextMenu.hide();
    });



    $('#ctx-batch-download').on('click', function() {
        if (!$(this).hasClass('disabled')) {
            performBatchDownload();
        }
        contextMenu.hide();
    });

    // File Comparison functionality
    let currentDiffView = 'side-by-side'; // or 'line-by-line'
    const compareModal = $('#compare-modal');
    const compareClose = $('#compare-close');
    const compareViewToggle = $('#compare-view-toggle');
    const compareDiffOutput = $('#compare-diff-output');
    const compareFile1Name = $('#compare-file1-name');
    const compareFile2Name = $('#compare-file2-name');

    $('#ctx-compare').on('click', function() {
        if (selectedFiles.size !== 2) {
            alert('Please select exactly 2 files to compare.');
            contextMenu.hide();
            return;
        }

        const paths = Array.from(selectedFiles);
        
        // Show loading
        compareModal.show();
        compareDiffOutput.html('<div style="padding: 40px; text-align: center;"><i class="bi bi-hourglass-split"></i> Loading files...</div>');
        
        // Fetch and compare files
        $.ajax({
            type: 'POST',
            url: 'api/compare.php',
            data: {
                path1: paths[0],
                path2: paths[1]
            },
            dataType: 'json',
            success: function(response) {
                if (response.error) {
                    compareDiffOutput.html('<div style="padding: 40px; text-align: center; color: #dc3545;"><i class="bi bi-exclamation-triangle"></i> ' + response.error + '</div>');
                    return;
                }
                
                // Update file names
                compareFile1Name.text(response.file1.name);
                compareFile2Name.text(response.file2.name);
                
                // Generate and display diff
                renderDiff(response.file1.content, response.file2.content, response.file1.name, response.file2.name);
            },
            error: function(xhr) {
                let errorMsg = 'Failed to load files for comparison.';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMsg = xhr.responseJSON.error;
                }
                compareDiffOutput.html('<div style="padding: 40px; text-align: center; color: #dc3545;"><i class="bi bi-exclamation-triangle"></i> ' + errorMsg + '</div>');
            }
        });
        
        contextMenu.hide();
    });

    function renderDiff(content1, content2, filename1, filename2) {
        // Create unified diff using diff library
        const diff = Diff.createTwoFilesPatch(
            filename1,
            filename2,
            content1,
            content2,
            '',
            '',
            { context: 3 }
        );
        
        // Render with diff2html
        const diffHtml = Diff2Html.html(diff, {
            drawFileList: false,
            matching: 'lines',
            outputFormat: currentDiffView === 'side-by-side' ? 'side-by-side' : 'line-by-line',
            renderNothingWhenEmpty: false
        });
        
        compareDiffOutput.html(diffHtml);
    }

    compareViewToggle.on('click', function() {
        // Toggle view
        currentDiffView = currentDiffView === 'side-by-side' ? 'line-by-line' : 'side-by-side';
        
        // Re-render if we have content
        const file1Name = compareFile1Name.text();
        const file2Name = compareFile2Name.text();
        
        if (file1Name && file2Name) {
            // We need to store the original content to re-render
            // For now, just update the button text
            $(this).html(currentDiffView === 'side-by-side' 
                ? '<i class="bi bi-list-ul"></i> Unified View' 
                : '<i class="bi bi-layout-split"></i> Side-by-Side');
            
            // Trigger a re-fetch (simplified approach)
            // In production, you'd want to cache the content
            const paths = Array.from(selectedFiles);
            if (paths.length === 2) {
                $.ajax({
                    type: 'POST',
                    url: 'api/compare.php',
                    data: { path1: paths[0], path2: paths[1] },
                    dataType: 'json',
                    success: function(response) {
                        if (!response.error) {
                            renderDiff(response.file1.content, response.file2.content, response.file1.name, response.file2.name);
                        }
                    }
                });
            }
        }
    });

    compareClose.on('click', function() {
        compareModal.hide();
    });

    // Close modal on click outside
    compareModal.on('click', function(e) {
        if (e.target === this) {
            compareModal.hide();
        }
    });

    <?php else: ?>
    $('#loginForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            type: 'POST',
            url: 'api/auth/login.php',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    window.location.reload();
                } else {
                    $('#error-message').text(response.message).show();
                }
            },
            error: function() {
                $('#error-message').text('An error occurred during login.').show();
            }
        });
    });
    <?php endif; ?>
});
</script>

<!-- Custom Context Menu -->
<div id="custom-context-menu" class="context-menu" style="display: none;">
    <ul>
        <li id="ctx-preview">
            <i class="bi bi-eye"></i> Preview
        </li>
        <li id="ctx-download">
            <i class="bi bi-download"></i> Download
        </li>
        <li id="ctx-separator-batch" class="separator"></li>

        <li id="ctx-batch-download">
            <i class="bi bi-file-earmark-zip"></i> Batch Download Selected
        </li>
        <li id="ctx-separator-compare" class="separator"></li>
        <li id="ctx-compare" style="display: none;">
            <i class="bi bi-file-diff"></i> Compare Files
        </li>
    </ul>
</div>

<!-- File Comparison Modal -->
<div id="compare-modal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 95%; width: 95%;">
        <div class="modal-header">
            <h2 id="compare-title">File Comparison</h2>
            <span class="close" id="compare-close" role="button" aria-label="Close">&times;</span>
        </div>
        <div class="modal-body" style="padding: 0;">
            <div class="compare-controls" style="padding: 15px 25px; background: #f7f7f7; border-bottom: 1px solid #e0e0e0; display: flex; gap: 10px; align-items: center;">
                <button id="compare-view-toggle" class="btn btn-sm btn-primary">
                    <i class="bi bi-layout-split"></i> Toggle View
                </button>
                <div style="flex: 1; display: flex; gap: 15px; font-size: 13px; color: #555;">
                    <span id="compare-file1-name"></span>
                    <span style="color: #999;">vs</span>
                    <span id="compare-file2-name"></span>
                </div>
            </div>
            <div id="compare-diff-output" style="overflow: auto; max-height: 70vh; background: #fff;"></div>
        </div>
    </div>
</div>

</body>
</html>
