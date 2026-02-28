<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title><?php echo $page_title ?? 'Network Monitor'; ?></title>

    <!-- Custom fonts for this template-->
    <link href="<?php echo BASE_PATH; ?>sb_admin_theme/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="<?php echo BASE_PATH; ?>sb_admin_theme/css/sb-admin-2.min.css" rel="stylesheet">

    <!-- Theme Styles -->
    <style>
        /* Light Mode (Default) */
        body {
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        /* Dark Mode */
        body.dark-mode {
            background-color: #1a1a1a !important;
            color: #e0e0e0;
        }

        body.dark-mode::before,
        body.dark-mode::after {
            background-color: #1a1a1a !important;
        }

        body.dark-mode #wrapper {
            background-color: #1a1a1a !important;
        }

        body.dark-mode #content-wrapper {
            background-color: #1a1a1a !important;
        }

        body.dark-mode #content {
            background-color: #1a1a1a !important;
        }

        body.dark-mode .container-fluid {
            background-color: #1a1a1a !important;
        }

        /* Sidebar stays with gradient in dark mode */
        body.dark-mode .sidebar {
            background: linear-gradient(180deg, #224abe 10%, #1a1a1a 100%) !important;
        }

        body.dark-mode .topbar {
            background-color: #2d2d2d !important;
            border-bottom: 1px solid #404040;
        }

        body.dark-mode .topbar .nav-link {
            color: #b0b0b0 !important;
        }

        body.dark-mode .topbar .text-gray-600 {
            color: #b0b0b0 !important;
        }

        body.dark-mode .card {
            background-color: #2d2d2d;
            border-color: #404040;
        }

        body.dark-mode .card-header {
            background-color: #252525;
            border-bottom: 1px solid #404040;
        }

        body.dark-mode .text-gray-800 {
            color: #e0e0e0 !important;
        }

        body.dark-mode .text-gray-900 {
            color: #e0e0e0 !important;
        }

        body.dark-mode .text-muted {
            color: #888 !important;
        }

        body.dark-mode .table {
            color: #e0e0e0;
            background-color: #2d2d2d;
        }

        body.dark-mode .table-bordered {
            border-color: #404040;
        }

        body.dark-mode .table-bordered th,
        body.dark-mode .table-bordered td {
            border-color: #404040;
        }

        body.dark-mode .form-control {
            background-color: #252525;
            border-color: #404040;
            color: #e0e0e0;
        }

        body.dark-mode .form-control:focus {
            background-color: #2d2d2d;
            border-color: #4e73df;
            color: #e0e0e0;
        }

        body.dark-mode .form-control::placeholder {
            color: #666;
        }

        body.dark-mode select.form-control option {
            background-color: #252525;
            color: #e0e0e0;
        }

        body.dark-mode .alert {
            border-color: #404040;
        }

        body.dark-mode .alert-warning {
            background-color: #3d3410;
            color: #ffc107;
            border-color: #664d03;
        }

        body.dark-mode .alert-info {
            background-color: #0c3d5d;
            color: #6dc4e8;
            border-color: #055160;
        }

        body.dark-mode .alert-success {
            background-color: #0f3d2e;
            color: #75d99c;
            border-color: #0a5132;
        }

        body.dark-mode .alert-danger {
            background-color: #4d1f1f;
            color: #f87171;
            border-color: #721c24;
        }

        body.dark-mode .badge-secondary {
            background-color: #404040;
        }

        body.dark-mode .btn-secondary {
            background-color: #404040;
            border-color: #404040;
        }

        body.dark-mode .btn-secondary:hover {
            background-color: #505050;
            border-color: #505050;
        }

        body.dark-mode .btn-outline-primary {
            color: #4e73df;
            border-color: #4e73df;
        }

        body.dark-mode .btn-outline-primary:hover {
            background-color: #4e73df;
            color: #fff;
        }

        body.dark-mode .dropdown-menu {
            background-color: #2d2d2d;
            border-color: #404040;
        }

        body.dark-mode .dropdown-item {
            color: #e0e0e0;
        }

        body.dark-mode .dropdown-item:hover {
            background-color: #404040;
            color: #fff;
        }

        body.dark-mode .dropdown-divider {
            border-top: 1px solid #404040;
        }

        body.dark-mode .border-left-primary {
            border-left: 0.25rem solid #4e73df !important;
        }

        body.dark-mode .border-left-success {
            border-left: 0.25rem solid #1cc88a !important;
        }

        body.dark-mode .border-left-danger {
            border-left: 0.25rem solid #e74a3b !important;
        }

        body.dark-mode .border-left-warning {
            border-left: 0.25rem solid #f6c23e !important;
        }

        body.dark-mode #themeToggle {
            color: #ffc107;
        }

        /* DataTables Dark Mode */
        body.dark-mode .dataTables_wrapper .dataTables_length,
        body.dark-mode .dataTables_wrapper .dataTables_filter,
        body.dark-mode .dataTables_wrapper .dataTables_info,
        body.dark-mode .dataTables_wrapper .dataTables_paginate {
            color: #e0e0e0;
        }

        body.dark-mode .dataTables_wrapper .dataTables_paginate .paginate_button {
            color: #e0e0e0 !important;
        }

        body.dark-mode .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #404040;
            border-color: #404040;
            color: #fff !important;
        }

        body.dark-mode .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #4e73df;
            border-color: #4e73df;
            color: #fff !important;
        }

        /* Dashboard Card Links */
        a.text-decoration-none .card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        a.text-decoration-none:hover .card {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.2) !important;
        }

        body.dark-mode a.text-decoration-none:hover .card {
            box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.5) !important;
        }

        a.text-decoration-none .text-gray-800 {
            color: #5a5c69 !important;
        }

        body.dark-mode a.text-decoration-none .text-gray-800 {
            color: #e0e0e0 !important;
        }

        /* Theme Toggle Button */
        #themeToggle {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.5rem;
            transition: color 0.3s ease;
        }

        #themeToggle:hover {
            color: #4e73df;
        }

        #themeToggle:focus {
            outline: none;
            box-shadow: none;
        }

        body.dark-mode #themeToggle:hover {
            color: #ffd54f;
        }

        /* Footer Dark Mode */
        body.dark-mode .sticky-footer {
            background-color: #2d2d2d !important;
        }

        body.dark-mode .sticky-footer .copyright {
            color: #888;
        }
    </style>

    <?php if (isset($page_styles)): ?>
        <!-- Page level plugins -->
        <?php echo $page_styles; ?>
    <?php endif; ?>
</head>

<body id="page-top">
