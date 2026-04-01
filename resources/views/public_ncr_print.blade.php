<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>NCR Print</title>
    <style>
        @page { size: A4; margin: 10mm; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #000; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #000; padding: 4px; vertical-align: top; }
        .no-border { border: none; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .bg-yellow { background-color: #ffff99; }
        .mb-2 { margin-bottom: 8px; }
        .mt-2 { margin-top: 8px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
        .badge { padding: 2px 6px; border: 1px solid #000; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="header">
        <div style="font-size: 18px; font-weight: bold; color: #1e40af;">TOPSYSTEM</div>
        <div style="font-size: 10px;">NCR CAPA Management System</div>
    </div>

    <table>
        <thead>
            <tr>
                <th colspan="4" class="bg-yellow text-center" style="font-size: 16px;">NON CONFORMANCE REPORT (NCR)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="no-border">Finder/Department</td>
                <td class="no-border">: {{ $ncr->finderDepartment->department_name ?? '' }}</td>
                <td class="no-border">NCR No.</td>
                <td class="no-border">: {{ $ncr->ncr_number }}</td>
            </tr>
            <tr>
                <td class="no-border">To Receiver (Dept.)</td>
                <td class="no-border">: {{ $ncr->receiverDepartment->department_name ?? '' }}</td>
                <td class="no-border">Date of Issue</td>
                <td class="no-border">: {{ optional($ncr->issued_date ?? $ncr->date_found)->format('d M Y') }}</td>
            </tr>
            <tr>
                <td class="no-border">Project Name</td>
                <td class="no-border">: {{ $ncr->project_name }}</td>
                <td class="no-border">Order No.</td>
                <td class="no-border">: {{ $ncr->project_sn }}</td>
            </tr>
            <tr>
                <td class="no-border">Customer</td>
                <td class="no-border">: {{ $ncr->customer_name }}</td>
                <td class="no-border">PO No.</td>
                <td class="no-border">: {{ $ncr->order_number }}</td>
            </tr>
            <tr>
                <td class="no-border">Line No.</td>
                <td class="no-border">: {{ $ncr->line_no }}</td>
                <td class="no-border">Last NCR No.</td>
                <td class="no-border">: {{ $ncr->last_ncr_no }}</td>
            </tr>
        </tbody>
    </table>

    <div class="mt-2 font-bold">Defect Mode: {{ $ncr->defectCategory->category_name ?? '' }}</div>
    <div class="mb-2">Description of Non-Conformance:<br>{{ $ncr->defect_description }}</div>

    <table class="mt-2">
        <tr>
            <td class="font-bold">Disposition</td>
            <td>{{ $ncr->dispositionMethod->method_name ?? '' }}</td>
            <td class="font-bold">Assigned PIC</td>
            <td>{{ $ncr->assignedPic->name ?? '' }}</td>
        </tr>
    </table>

    <div class="mt-2">
        <span class="badge">{{ $ncr->status }}</span>
    </div>

    <div class="mt-2" style="font-size: 10px; text-align: right;">PT. Topsystem Asia Base</div>
</body>
</html>
