<?php session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once '../../utils/log_utils.php';
require_once BASE_PATH . '/components/header.php'; ?>
<style>
    .btn-wrapper {
        display: flex;
        align-items: flex-end;
        justify-content: space-between
    }

    .btn-block {
        display: inline-block;
        width: 100%;
        margin-right: 10px
    }
</style>

<body id="page-top">
    <div id="wrapper"><?php include BASE_PATH . "/components/navbar.php"; ?>
        <div class="d-flex flex-column" id="content-wrapper">
            <div id="content"><?php include BASE_PATH . "/components/topbar.php"; ?>
                <div class="container-fluid"><?php include BASE_PATH . "/utils/alerts.php"; ?>
                    <div class="mb-4 align-items-center d-sm-flex justify-content-between">
                        <h1 class="h3 mb-0 text-gray-800">Monitoraggio Lotti di Produzione</h1>
                    </div>
                    <ol class="mb-4 breadcrumb">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="home">Tracking</a></li>
                        <li class="breadcrumb-item active">Packing List</li>
                    </ol>
                    <div class="row">
                        <div class="col-lg-5 col-xl-4">
                            <div class="mb-4 card shadow">
                                <div class="align-items-center card-header d-flex py-3">
                                    <h6 class="font-weight-bold m-0 text-primary">Opzioni</h6>
                                </div>
                                <div class="card-body">
                                    <textarea class="form-control" id="cartellini-input"
                                        placeholder="Inserisci cartellini, uno per riga" rows="10"></textarea>
                                    <button class="shadow btn btn-block btn-warning mt-2" id="generateReportBtn">Genera
                                        Report</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-7 col-xl-8">
                            <div class="mb-4 card shadow">
                                <div class="align-items-center card-header d-flex py-3">
                                    <h6 class="font-weight-bold m-0 text-primary">Packing List</h6>
                                </div>
                                <div class="card-body" id="report-container"></div>
                            </div>
                        </div>
                    </div>
                </div><?php include_once BASE_PATH . '/components/scripts.php'; ?>
            </div><?php include_once BASE_PATH . '/components/footer.php'; ?>
        </div>
        <script>
            document.getElementById('generateReportBtn').addEventListener('click', function () {
                var inputText = document.getElementById('cartellini-input').value.trim();
                var lines = inputText.split('\n').filter(Boolean);

                var dataToSend = {
                    cartellini: lines
                };

                var xhr = new XMLHttpRequest();
                var url = 'generateReportFiches.php'; // Utilizza sempre generateReportCartel.php

                xhr.open('POST', url, true);
                xhr.setRequestHeader('Content-Type', 'application/json');

                xhr.onreadystatechange = function () {
                    if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
                        var blob = new Blob([xhr.response], { type: 'application/pdf' });
                        var url = URL.createObjectURL(blob);
                        var reportContainer = document.getElementById('report-container');
                        reportContainer.innerHTML = '<iframe src="' + url + '" width="100%" height="500px"></iframe>';
                    }
                };

                xhr.responseType = 'blob';
                xhr.send(JSON.stringify(dataToSend));
            });
        </script>
</body>