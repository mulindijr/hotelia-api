<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 13px;
            color: #333333;
            line-height: 1.5;
            margin: 0;
            padding: 20px;
        }
        .header-table, .details-table, .items-table, .summary-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table td {
            vertical-align: top;
        }
        .brand-title {
            font-size: 24px;
            font-weight: bold;
            color: #1e293b;
            margin: 0;
        }
        .subtitle {
            color: #64748b;
            font-size: 12px;
        }
        .invoice-title {
            font-size: 20px;
            font-weight: bold;
            text-align: right;
            color: #0284c7;
            text-transform: uppercase;
        }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            font-size: 11px;
            font-weight: bold;
            border-radius: 4px;
            text-transform: uppercase;
        }
        .badge-paid { background-color: #dcfce7; color: #15803d; }
        .badge-partial { background-color: #fef9c3; color: #a16207; }
        .badge-unpaid { background-color: #fee2e2; color: #b91c1c; }

        .divider {
            height: 1px;
            background-color: #e2e8f0;
            margin: 20px 0;
        }
        .section-heading {
            font-size: 12px;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 6px;
        }
        .items-table th {
            background-color: #f8fafc;
            color: #475569;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 10px;
            border-bottom: 2px solid #e2e8f0;
            text-align: left;
        }
        .items-table td {
            padding: 10px;
            border-bottom: 1px solid #f1f5f9;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .summary-box {
            width: 280px;
            float: right;
            margin-top: 15px;
        }
        .summary-table td {
            padding: 6px 10px;
        }
        .grand-total {
            font-weight: bold;
            font-size: 15px;
            color: #0f172a;
            border-top: 2px solid #cbd5e1;
        }
        .footer {
            margin-top: 60px;
            text-align: center;
            font-size: 11px;
            color: #94a3b8;
            border-top: 1px solid #f1f5f9;
            padding-top: 15px;
        }
    </style>
</head>
<body>

    <!-- Header Section -->
    <table class="header-table">
        <tr>
            <td>
                <div class="brand-title">{{ $hotel->name }}</div>
                <div class="subtitle">
                    {{ $hotel->address ?? '' }} {{ $hotel->city ? ', '.$hotel->city : '' }}<br>
                    Email: {{ $hotel->email }} | Phone: {{ $hotel->phone ?? 'N/A' }}
                </div>
            </td>
            <td class="text-right">
                <div class="invoice-title">INVOICE</div>
                <div style="font-weight: bold; margin-top: 4px;">#{{ $invoice->invoice_number }}</div>
                <div style="margin-top: 6px;">
                    @php
                        $statusClass = match(strtolower($invoice->status)) {
                            'paid' => 'badge-paid',
                            'partial' => 'badge-partial',
                            default => 'badge-unpaid'
                        };
                    @endphp
                    <span class="badge {{ $statusClass }}">{{ strtoupper($invoice->status) }}</span>
                </div>
            </td>
        </tr>
    </table>

    <div class="divider"></div>

    <!-- Details Section -->
    <table class="details-table">
        <tr>
            <td style="width: 50%;">
                <div class="section-heading">Billed To (Guest)</div>
                <strong>{{ $guest->first_name }} {{ $guest->last_name }}</strong><br>
                Email: {{ $guest->email }}<br>
                Phone: {{ $guest->phone ?? 'N/A' }}<br>
                Nationality: {{ $guest->nationality ?? 'N/A' }}
            </td>
            <td style="width: 50%;">
                <div class="section-heading">Booking & Invoice Summary</div>
                <strong>Booking Reference:</strong> {{ $booking->booking_reference }}<br>
                <strong>Check-In Date:</strong> {{ $booking->check_in_date->format('M d, Y') }}<br>
                <strong>Check-Out Date:</strong> {{ $booking->check_out_date->format('M d, Y') }}<br>
                <strong>Issued Date:</strong> {{ $invoice->created_at->format('M d, Y') }}
            </td>
        </tr>
    </table>

    <div style="margin-top: 25px;"></div>

    <!-- Line Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th>Description</th>
                <th class="text-center">Qty / Nights</th>
                <th class="text-right">Unit Price</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
                <tr>
                    <td>
                        <strong>{{ $item->description }}</strong>
                    </td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">{{ number_format($item->total_price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Financial Summary -->
    <div class="summary-box">
        <table class="summary-table">
            <tr>
                <td>Subtotal:</td>
                <td class="text-right">{{ $currency }} {{ number_format($invoice->subtotal, 2) }}</td>
            </tr>
            <tr>
                <td>Tax ({{ number_format($invoice->tax_rate ?? 0, 1) }}%):</td>
                <td class="text-right">{{ $currency }} {{ number_format($invoice->tax_amount, 2) }}</td>
            </tr>
            <tr class="grand-total">
                <td>Total Amount:</td>
                <td class="text-right">{{ $currency }} {{ number_format($invoice->total_amount, 2) }}</td>
            </tr>
            <tr>
                <td>Amount Paid:</td>
                <td class="text-right" style="color: #15803d;">{{ $currency }} {{ number_format($totalPaid, 2) }}</td>
            </tr>
            <tr style="font-weight: bold; background-color: #f8fafc;">
                <td>Balance Due:</td>
                <td class="text-right" style="color: {{ $balanceDue > 0 ? '#b91c1c' : '#15803d' }};">
                    {{ $currency }} {{ number_format($balanceDue, 2) }}
                </td>
            </tr>
        </table>
    </div>

    <div style="clear: both;"></div>

    <!-- Footer -->
    <div class="footer">
        Thank you for choosing {{ $hotel->name }}! We hope to welcome you again soon.
    </div>

</body>
</html>
