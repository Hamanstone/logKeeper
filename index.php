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
    <style>
        #group-list .list-group-item {
            cursor: pointer;
        }
        #preview-modal .modal-body {
            max-height: 60vh;
            overflow-y: auto;
        }
        .tree {
            list-style: none;
            padding-left: 0;
        }
        .tree-node {
            padding-left: 1.5em;
        }
        .tree-node .tree-toggler {
            cursor: pointer;
            margin-right: 5px;
        }
        .tree-node .tree-toggler::before {
            content: "►";
            display: inline-block;
            transition: transform 0.2s;
        }
        .tree-node.open > .tree-toggler::before {
            transform: rotate(90deg);
        }
        .tree-node ul {
            list-style: none;
            padding-left: 1.5em;
            display: none;
        }
        .tree-node.open > ul {
            display: block;
        }
        .tree-leaf a {
            text-decoration: none;
            color: inherit;
        }
        .tree-leaf a.active {
            font-weight: bold;
        }
        .highlight {
            background-color: yellow;
            color: black;
        }
        #preview-content.line-numbers-on {
            counter-reset: line;
        }
        #preview-content.line-numbers-on .line::before {
            content: counter(line);
            counter-increment: line;
            display: inline-block;
            width: 3em;
            padding-right: 1em;
            margin-left: -4em;
            text-align: right;
            color: #999;
            -webkit-user-select: none;
            user-select: none;
        }
    </style>
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
                <h4>Logs</h4>
                <table id="logs-table" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
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
                <span class="close">&times;</span>
                <h2 id="log-filename"></h2>
                <p id="log-details"></p>
            </div>
            <div class="modal-body">
                <div class="controls">
                    <button id="copy-log">Copy</button>
                    <button id="download-log">Download</button>
                    <button id="toggle-line-numbers">Show Line Numbers</button>
                    <input type="text" id="log-search-input" placeholder="Search log...">
                </div>
                <pre id="log-content"></pre>
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
<script src="js/scripts.js"></script>

</body>
</html>