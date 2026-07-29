<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Receipt #{{ $payment->id }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 13px;
            color: #333333;
            line-height: 1.5;
            margin: 0;
            padding: 20px;
        }
        .header-table, .details-table, .payment-table {
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
        .receipt-title {
            font-size: 20px;
            font-weight: bold;
            text-align: right;
            color: #16a34a;
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
        .badge-completed { background-color: #dcfce7; color: #15803d; }
        .badge-pending { background-color: #fef9c3; color: #a16207; }
        .badge-failed { background-color: #fee2e2; color: #b91c1c; }

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

        .amount-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 20px;
            text-align: center;
            margin: 25px 0;
        }
        .amount-title {
            font-size: 12px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: bold;
        }
        .amount-value {
            font-size: 28px;
            font-weight: bold;
            color: #15803d;
            margin-top: 5px;
        }

        .payment-table th {
            background-color: #f8fafc;
            color: #475569;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 10px;
            border-bottom: 2px solid #e2e8f0;
            text-align: left;
        }
        .payment-table td {
            padding: 10px;
            border-bottom: 1px solid #f1f5f9;
        }
        .footer {
            margin-top: 50px;
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
            <td style="text-align: right;">
                <div class="receipt-title">PAYMENT RECEIPT</div>
                <div style="font-weight: bold; margin-top: 4px;">Receipt #{{ $payment->id }}</div>
                <div style="margin-top: 6px;">
                    @php
                        $statusClass = match(strtolower($payment->status)) {
                            'completed' => 'badge-completed',
                            'pending' => 'badge-pending',
                            default => 'badge-failed'
                        };
                    @endphp
                    <span class="badge {{ $statusClass }}">{{ strtoupper($payment->status) }}</span>
                </div>
            </td>
        </tr>
    </table>

    <div class="divider"></div>

    <!-- Amount Card -->
    <div class="amount-card">
        <div class="amount-title">Amount Received</div>
        <div class="amount-value">{{ $currency }} {{ number_format($payment->amount, 2) }}</div>
    </div>

    <!-- Details Section -->
    <table class="details-table">
        <tr>
            <td style="width: 50%; vertical-align: top;">
                <div class="section-heading">Received From</div>
                <strong>{{ $guest->first_name }} {{ $guest->last_name }}</strong><br>
                Email: {{ $guest->email }}<br>
                Phone: {{ $guest->phone ?? 'N/A' }}
            </td>
            <td style="width: 50%; vertical-align: top;">
                <div class="section-heading">Transaction Information</div>
                <strong>Payment Method:</strong> {{ strtoupper($payment->payment_method) }}<br>
                <strong>Transaction Reference:</strong> {{ $payment->transaction_reference ?? 'N/A' }}<br>
                <strong>Payment Date:</strong> {{ $payment->created_at->format('M d, Y H:i A') }}<br>
                <strong>Booking Reference:</strong> {{ $booking->booking_reference }}
            </td>
        </tr>
    </table>

    <!-- Footer -->
    <div class="footer">
        Official payment receipt issued by {{ $hotel->name }}. Thank you for your business!
    </div>

</body>
</html>
