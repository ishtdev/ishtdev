<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        p{
            line-height: 19px;
        }
        .invoice {
            max-width: 660px;
            margin: 20px 0px;
            border: 1px solid #ccc;
            padding: 20px;
        } 
        .invoice-heading {
            font-size: 20px;
        }
        .company-info h3 {
            margin: 0;
            font-size: 18px;
        }
        .company-info p {
            margin: 5px 0;
        }
        .bill-to h3 {
            margin-top: 0;
        }
        .invoice-table th, .invoice-table td {
            border: 1px solid #ccc;
            padding: 5px;
            text-align: left;
            word-break: break-word;
        }
        .invoice-table th {
            background-color: #f2f2f2;
        }
        .total {
            text-align: right;
            font-weight: bold;
        }
        .logo {
            width: 100px;
        }
        .margin-right-5{
            margin-right: 5%;
        }
        .footer-line {
            width: 100%;
            height: 1px;
            border: 0;
            margin: 1px;
            background-color: gray;    
        }
        .margin-5{
            margin : 5px;
        }
        @media screen and (max-width: 660px) {
            .invoice {
                padding: 10px;
            }
            .invoice-table {
                font-size: 12px;
            }
            .row {
                flex-direction: column;
            }
            .col-2, .col-10, .col-11, .col-12 {
                flex: 0 0 100%;
                max-width: 100%;
            }
            .text-right {
                text-align: left !important;
            }
            .text-center{
                margin: 0 auto;
            }
            .margin-right-5{
                margin-right: 5%;
            }
        }
    </style>
</head>
<body>
    <div class="container invoice">
        <div class="row mb-4">
            <div class="col-12">
                <h3 class="text-center">TAX INVOICE</h3>
            </div>
        </div>
         <div class="row mb-4 mr-2">
            <div class="col-12">
                <table class="table">
                    <tbody>
                        <tr>
                            <img class="logo mb-3" src="image/logo.png" alt="Company Logo">
                        </tr>
                        <tr>
                            <td class="border border-0 pl-1">
                                <p>VERSHAMA TECH PRIVATE LIMITED<br>
                                    221B, Sector-E, <br>
                                    Prajapat Nagar <br>
                                    Sudama Nagar Main Road <br>
                                    Indore, Madhya Pradesh, 452009
                                </p>
                            </td>
                            <td class="text-right  border-0">
                                <p>{{ $boostRequest->profile_detail->full_name }} <br>
                                    Business: {{ $boostRequest->profile_detail->business_name }} <br>
                                    {{ $boostRequest->profile_detail->business_address }} <br>
                                    GST: {{ $boostRequest->profile_detail->gst_number }}
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-12">
                
                <h3>Invoice Date: {{ \Carbon\Carbon::parse($boostRequest->created_at)->format('Y-m-d') }}</h3>
                <p>Invoice ID: {{ $boostRequest->id }} <br>
                    GST No.: 23AAJCV8106A1ZP</p>
            </div>
        </div>
        <div class="margin-right-5">
            <div class="row mb-4 ">
                <div class="col-12">
                    <table class="table invoice-table">
                        <tbody>
                            <tr>
                                <td>Tax Invoice</td>
                                <td class="text-right">Original for recipient</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
        </div>
            <div class="row mb-4">
                <div class="col-12">
                    <div class="table-responsive">
                        <table class="table invoice-table">
                            <thead>
                                <tr>
                                    <th>Description</th>
                                    <th>Amount (INR)</th>
                                    <th>GST (%)</th>
                                    <th>Total (INR)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <strong>Package Type: {{ $boostRequest->package_type }}</strong><br>
                                        Duration: {{ $boostRequest->duration }}<br>
                                        Description: {!! $boostRequest->package_description !!}
                                    </td>
                                    <td>{{ $boostRequest->amount }}</td>
                                    <td>{{ $boostRequest->gst_in_percent }}%</td>
                                    <td>{{ $boostRequest->total }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12 text-right">
                    <p>Amount Paid: Rs. {{ $boostRequest->total }}</p>
                </div>
            </div>
            <hr class="footer-line">
            <p class="margin-5">The supplies detailed in this invoice are not subject to reverse charge.</p>
            <hr class="footer-line">
            <p class="margin-5">GST collected under the Goods and Services Tax Act, Government of India. For any GST-related queries, please visit the official GST portal at <a href="https://www.gst.gov.in/">www.gst.gov.in.</a></p>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
