<!DOCTYPE html>
<html>
<head>
    <title><?= $projectName ?> Timesheet</title>
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.1/css/bootstrap.min.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.18/css/dataTables.bootstrap4.min.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.5.6/css/buttons.bootstrap4.min.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/colreorder/1.5.0/css/colReorder.bootstrap4.min.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/fixedheader/3.1.4/css/fixedHeader.bootstrap4.min.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/select/1.3.0/css/select.bootstrap4.min.css"/>
    <link href="https://fonts.googleapis.com/css?family=Lato" rel="stylesheet">
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.1/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/2.5.0/jszip.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.10.18/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.10.18/js/dataTables.bootstrap4.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.5.6/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.5.6/js/buttons.bootstrap4.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.5.6/js/buttons.html5.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.5.6/js/buttons.print.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/colreorder/1.5.0/js/dataTables.colReorder.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/fixedheader/3.1.4/js/dataTables.fixedHeader.min.js"></script>
    <style>
        body {
            font-family: "Lato", sans-serif;
            font-size: 15px;
        }
    </style>
</head>
<body>
<div class="navbar navbar-expand navbar-light bg-light mb-4">
    <a class="navbar-brand" href="/"><?= $projectName ?> Timesheet</a>
</div>

<div class="container mb-4">
    <form action="/" method="get">
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="createdAfter" data-toggle="tooltip" data-placement="top" title="Issue 'Created' date">From</label>
                <input type="date" class="form-control" id="createdAfter" name="createdAfter" value="<?= $flash->getFirstMessage('createdAfter') ?>">
            </div>
            <div class="form-group col-md-6">
                <label for="createdBefore" data-toggle="tooltip" data-placement="top" title="Issue 'Created' date">To</label>
                <input type="date" class="form-control" id="createdBefore" name="createdBefore" value="<?= $flash->getFirstMessage('createdBefore') ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="assignee">Assignee</label>
                <select id="assignee" name="assignee" class="form-control">
                    <option value=""<?= $flash->getFirstMessage('assignee') ? null : ' selected' ?>>All</option>
                    <?php foreach ($assignees as $assignee): ?>
                        <option value="<?= $assignee['shortName'] ?>"<?= $flash->getFirstMessage('assignee') === $assignee['shortName'] ? ' selected' : null ?>>
                            <?= $assignee['fullName'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group col-md-6">
                <label for="maxResults">Max Results</label>
                <select id="maxResults" name="maxResults" class="form-control">
                    <option value="50" <?= $flash->getFirstMessage('maxResults') === '50' ? 'selected' : null ?>>50</option>
                    <option value="100" <?= $flash->getFirstMessage('maxResults') === '100' ? 'selected' : null ?>>100</option>
                    <option value="250" <?= $flash->getFirstMessage('maxResults') === '250' ? 'selected' : null ?>>250</option>
                    <option value="300" <?= $flash->getFirstMessage('maxResults') === '300' ? 'selected' : null ?>>300</option>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="col-md-12 text-right">
                <a href="/" class="btn btn-secondary">Reset</a>
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </div>
    </form>
</div>

<div class="container-fluid">
    <table id="issueTable" class="table table-sm table-hover table-striped w-100">
        <thead>
        <tr>
            <th>Created</th>
            <th>Assignee</th>
            <th>Summary</th>
            <th>Customer</th>
            <th>Contact Name</th>
            <th>Contact Phone</th>
            <th>Logged</th>
        </tr>
        </thead>
    </table>
</div>

<script>
    $(document).ready(function () {
        $('#issueTable').DataTable({
            processing: true,
            pageLength: 25,
            paging: false,
            dom: "<'d-flex align-items-center'<B><'ml-auto'p><'ml-3'f>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-5'i><'col-sm-7'p>>",
            order: [[0, "desc"]],
            fixedHeader: true,
            colReorder: true,
            buttons: [
                'copy', 'excel', 'pdf', 'print'
            ],
            ajax: {
                url: 'api/issues',
                data: {
                    createdAfter: "<?= $flash->getFirstMessage('createdAfter') ?>",
                    createdBefore: "<?= $flash->getFirstMessage('createdBefore') ?>",
                    assignee: "<?= $flash->getFirstMessage('assignee') ?>",
                    maxResults: "<?= $flash->getFirstMessage('maxResults') ?>"
                }
            },
            columns: [
                {
                    data: 'created',
                    render: function (data) {
                        return moment(data).format('MM/DD/YYYY');
                    }
                },
                {
                    data: 'assignee',
                    render: function (data, type, row, meta) {
                        return `
                            <img src="${row.assigneeAvatarUrl}">
                            ${data}
                        `;
                    }
                },
                {
                    data: 'summary',
                    render: function (data, type, row, meta) {
                        return `
                        <span data-toggle="tooltip" data-placement="top" title="${row.key} (${row.type.name})">
                            <img src="${row.type.iconUrl}">
                            <a href="${row.issueLink}" target="_blank">${data}</a>
                        </span>
                        `;
                    }
                },
                {data: 'customer'},
                {data: 'contactName'},
                {data: 'contactPhone'},
                {
                    data: 'timeSpent',
                    render: function (data, type, row, meta) {
                        return `<span data-toggle="tooltip" data-placement="top" title="${row.timeSpentInHours}" class="font-weight-bold">${data}</span>`;
                    }
                },
            ],
            drawCallback: function () {
                $('[data-toggle="tooltip"]').tooltip();
            },
        });
    });
</script>
</body>
</html>
