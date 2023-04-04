<!DOCTYPE html>
<html>
<head>
    <title>PDF</title>
    <style type="text/css">
    * {font-family: sans-serif;}
    body {font-size: 12px; line-height: 1.5}
    table {border-collapse: collapse;}
    td {vertical-align: top;}
    p {text-align: justify;}
    td p{padding: 0; margin: 0;}
    .fw-bold {font-weight: bold;}
    .heading {text-transform: uppercase; font-weight: bold;}
    .text-center {text-align: center;}
    .text-right {text-align: right;}
    .mb {margin-bottom: 12px;}
    .mt {margin-top: 12px;}
    .watermark {position: fixed;top: 30%;width: 100%;text-align: center;opacity: .2;transform: rotate(45deg);transform-origin: 50% 50%;z-index: -1000; font-size: 100px}
    table[border] th, table[border] td {
        padding: 2px 4px;
        border-color: #484848;
    }
    thead th, tfoot th {background-color: #eee;}
    .custom-table thead th {
        background: #fff;
        padding: 6px;
        font-weight: 600;
        border-bottom: 1px solid #000;
        border-top: 1px solid #000;
    }
    .custom-table tbody td {
        padding: 3px 6px;
    }
    .custom-table tbody tr:last-child td {
        border-bottom: 1px solid #000;
    }
    .custom-table tfoot th,
    .custom-table tfoot td {
        background: #fff;
        padding: 3px 6px;
    }
    @page {
        margin-top: 1cm;
        margin-bottom: 2cm;
        margin-left: 2.5cm;
        margin-right: 2cm;
    }
    .break-page, .new-page { page-break-before: always; }
    </style>
</head>
 
<body>
<?php echo $view_content; ?>
</body>
</html>