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
                        <a class="nav-link" href="api/auth/logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container-fluid mt-3">
        <div class="row">
            <div class="col-md-3">
                <h4>Groups</h4>
                <div id="group-tree">
                    <!-- Tree will be loaded here -->
                </div>
            </div>
            <div class="col-md-9">
                <h4>Search</h4>
                <form id="search-form" class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label for="search-start" class="form-label">Start Time</label>
                        <input type="datetime-local" class="form-control" id="search-start">
                    </div>
                    <div class="col-md-3">
                        <label for="search-end" class="form-label">End Time</label>
                        <input type="datetime-local" class="form-control" id="search-end">
                    </div>
                    <div class="col-md-2">
                        <label for="search-customer" class="form-label">Customer</label>
                        <select id="search-customer" class="form-select">
                            <option value="">All</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="search-sku" class="form-label">SKU</label>
                        <select id="search-sku" class="form-select">
                            <option value="">All</option>
                        </select>
                    </div>
                    <div class="col-md-2 align-self-end">
                        <button type="submit" class="btn btn-primary">Search</button>
                        <button type="reset" class="btn btn-secondary">Clear</button>
                    </div>
                </form>
                <hr>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h4 class="mb-0">Logs</h4>
                    <button id="batch-download-btn" class="btn btn-success" style="display: none;">
                        <i class="bi bi-download"></i> Download Selected (<span id="selected-count">0</span>)
                    </button>
                </div>
                <table id="logs-table" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th style="width: 40px;">
                                <input type="checkbox" id="select-all-checkbox" title="Select All">
                            </th>
                            <th>File Name</th>
                            <th>File Size</th>
                            <th>Modification Time</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
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
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <h1 class="text-center mt-5">Log Explorer</h1>
                <div class="card mt-4">
                    <div class="card-body">
                        <h5 class="card-title text-center">Login</h5>
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
            { data: 'modified' },
            { data: 'actions' }
        ]
    });

    // Load groups and populate search dropdowns
    $.ajax({
        url: 'api/groups.php',
        dataType: 'json',
        success: function(groups) {
            const groupTree = $('#group-tree');
            const customerSelect = $('#search-customer');
            const skuSelect = $('#search-sku');
            const addedSkus = new Set();

            groupTree.empty();
            const tree = $('<ul class="tree"></ul>');

            Object.entries(groups).forEach(([customer, skus]) => {
                const customerNode = $('<li class="tree-node"></li>');
                customerNode.append(`<span class="tree-toggler"></span><strong>${customer}</strong>`);
                
                customerSelect.append($('<option>', { value: customer, text: customer }));

                const skuList = $('<ul></ul>');
                Object.entries(skus).forEach(([sku, dates]) => {
                    const skuNode = $('<li class="tree-node"></li>');
                    skuNode.append(`<span class="tree-toggler"></span>${sku}`);
                    
                    if (!addedSkus.has(sku)) {
                        skuSelect.append($('<option>', { value: sku, text: sku }));
                        addedSkus.add(sku);
                    }

                    const dateList = $('<ul></ul>');
                    dates.forEach(date => {
                        const dateNode = $(`<li class="tree-leaf"></li>`);
                        dateNode.append(`<a href="#" data-customer="${customer}" data-sku="${sku}" data-date="${date}">${date}</a>`);
                        dateList.append(dateNode);
                    });
                    skuNode.append(dateList);
                    skuList.append(skuNode);
                });
                customerNode.append(skuList);
                tree.append(customerNode);
            });
            groupTree.append(tree);
        }
    });

    $('#search-form').on('submit', function(e) {
        e.preventDefault();
        const searchData = {
            start: $('#search-start').val(),
            end: $('#search-end').val(),
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

    $('#group-tree').on('click', '.tree-toggler', function() {
        $(this).parent('.tree-node').toggleClass('open');
    });

    $('#group-tree').on('click', '.tree-leaf a', function(e) {
        e.preventDefault();

        $('#group-tree .tree-leaf a').removeClass('active');
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

    // Batch selection functionality
    let selectedFiles = new Set();

    function updateBatchDownloadButton() {
        const count = selectedFiles.size;
        $('#selected-count').text(count);
        if (count > 0) {
            $('#batch-download-btn').show();
        } else {
            $('#batch-download-btn').hide();
        }
    }

    // Select all checkbox
    $('#select-all-checkbox').on('change', function() {
        const isChecked = $(this).prop('checked');
        $('#logs-table tbody .row-checkbox').each(function() {
            $(this).prop('checked', isChecked);
            const row = $(this).closest('tr');
            const path = row.find('.btn-preview').data('path');
            
            if (isChecked) {
                selectedFiles.add(path);
                row.addClass('table-active');
            } else {
                selectedFiles.delete(path);
                row.removeClass('table-active');
            }
        });
        updateBatchDownloadButton();
    });

    // Individual row checkbox
    $('#logs-table tbody').on('change', '.row-checkbox', function(e) {
        e.stopPropagation();
        const isChecked = $(this).prop('checked');
        const row = $(this).closest('tr');
        const path = row.find('.btn-preview').data('path');
        
        if (isChecked) {
            selectedFiles.add(path);
            row.addClass('table-active');
        } else {
            selectedFiles.delete(path);
            row.removeClass('table-active');
        }
        
        // Update select-all checkbox state
        const totalCheckboxes = $('#logs-table tbody .row-checkbox').length;
        const checkedCheckboxes = $('#logs-table tbody .row-checkbox:checked').length;
        $('#select-all-checkbox').prop('checked', totalCheckboxes === checkedCheckboxes && totalCheckboxes > 0);
        
        updateBatchDownloadButton();
    });

    // Click on row to toggle checkbox
    $('#logs-table tbody').on('click', 'tr', function(e) {
        // Don't toggle if clicking on action buttons or checkbox itself
        if ($(e.target).hasClass('btn-preview') || $(e.target).hasClass('row-checkbox')) {
            return;
        }
        
        const checkbox = $(this).find('.row-checkbox');
        checkbox.prop('checked', !checkbox.prop('checked')).trigger('change');
    });

    // Batch download button
    $('#batch-download-btn').on('click', function() {
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
    });

    // Clear selection when table is cleared
    $('#search-form').on('reset', function() {
        selectedFiles.clear();
        $('#select-all-checkbox').prop('checked', false);
        updateBatchDownloadButton();
        logsTable.clear().draw();
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

</body>
</html>
