<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #525659; font-size: 13px; }
        .page { background: white; width: 21cm; min-height: 29.7cm; padding: 1cm; margin: 0 auto; box-sizing: border-box; box-shadow: 0 0 10px rgba(0,0,0,0.5); }
        table { width: 100%; border-collapse: collapse; border: 1px solid black; font-family: Arial, sans-serif; font-size: 13px; }
        td, th { border: 1px solid black; padding: 5px; vertical-align: top; }
        .no-border-top { border-top: none !important; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .print-btn { position: fixed; bottom: 20px; right: 20px; background: #4f46e5; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-size: 16px; box-shadow: 0 4px 6px rgba(0,0,0,0.2); }
        .print-btn:hover { background: #4338ca; }
        @media print {
            body { background: white; margin: 0; padding: 0; }
            .page { margin: 0; padding: 0; box-shadow: none; width: 100%; min-height: auto; }
            .print-btn { display: none; }
        }
    </style>
</head>
<body>
    @php
        function getIndianCurrency(float $number)
        {
            $decimal = round($number - ($no = floor($number)), 2) * 100;
            $hundred = null;
            $digits_length = strlen($no);
            $i = 0;
            $str = array();
            $words = array(0 => '', 1 => 'One', 2 => 'Two',
                3 => 'Three', 4 => 'Four', 5 => 'Five', 6 => 'Six',
                7 => 'Seven', 8 => 'Eight', 9 => 'Nine',
                10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve',
                13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
                16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen',
                19 => 'Nineteen', 20 => 'Twenty', 30 => 'Thirty',
                40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty',
                70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety');
            $digits = array('', 'Hundred','Thousand','Lakh', 'Crore');
            while( $i < $digits_length ) {
                $divider = ($i == 2) ? 10 : 100;
                $number = floor($no % $divider);
                $no = floor($no / $divider);
                $i += $divider == 10 ? 1 : 2;
                if ($number) {
                    $plural = (($counter = count($str)) && $number > 9) ? '' : null;
                    $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
                    $str [] = ($number < 21) ? $words[$number].' '. $digits[$counter]. $plural.' '.$hundred:$words[floor($number / 10) * 10].' '.$words[$number % 10]. ' '.$digits[$counter].$plural.' '.$hundred;
                } else $str[] = null;
            }
            $Rupees = implode('', array_reverse($str));
            $paise = ($decimal > 0) ? " and " . ($words[$decimal / 10] . " " . $words[$decimal % 10]) . ' Paise' : '';
            return ($Rupees ? 'Rupees ' . strtoupper($Rupees) : '') . $paise . ' only.';
        }
    @endphp

    <button class="print-btn" onclick="window.print()">🖨️ Print Invoice</button>
    
    <div class="page">
        <table>
            <tr>
                <td colspan="3" class="text-center" style="font-size: 18px; font-weight: bold; letter-spacing: 1px; padding: 15px;">INVOICE</td>
            </tr>
            <tr>
                <td rowspan="3" style="width: 50%;">
                    <strong>SYSCOM INFOTECH,</strong><br>
                    #11 Priya Complex,<br>
                    Opp MSP School,<br>
                    Dindigul - 624 005<br>
                    Mob : 9952118780<br>
                    E-mail : contact@syscominfotech.net<br>
                    Web : www.syscominfotech.net
                </td>
                <td style="width: 25%;">
                    Invoice No:<br>
                    <strong>{{ $invoice->invoice_number }}</strong>
                </td>
                <td style="width: 25%;">
                    Dated<br>
                    <strong>{{ $invoice->invoice_date->format('d-M-Y') }}</strong>
                </td>
            </tr>
            <tr>
                <td><br><br></td>
                <td>Mode / Terms of Payment<br><br></td>
            </tr>
            <tr>
                <td>Supplier's Reference<br><br></td>
                <td>Other Reference(s)<br><br></td>
            </tr>
            
            <tr>
                <td style="padding-top: 15px;">
                    Buyer<br><br>
                    @php $party = $invoice->type === 'customer' ? $invoice->customer : $invoice->supplier; @endphp
                    <strong>{{ $party ? $party->name : '' }}</strong><br>
                    {!! $party ? nl2br(e($party->address)) : '' !!}
                </td>
                <td colspan="2" style="padding-top: 15px;">
                    Delivery to<br><br>
                    <strong>{{ $party ? $party->name : '' }}</strong><br>
                    {!! $party ? nl2br(e($party->address)) : '' !!}
                </td>
            </tr>
        </table>

        <table style="border-top: none;">
            <tr>
                <th style="border-top: none; width: 50%; font-weight: normal;" class="text-center">Description of Goods</th>
                <th style="border-top: none; font-weight: normal;" class="text-center">Qty.</th>
                <th style="border-top: none; font-weight: normal;" class="text-center">Rate</th>
                <th style="border-top: none; font-weight: normal;" class="text-center">Amount</th>
            </tr>
            
            <tr style="height: 350px;">
                <td style="border-bottom: none; border-top: none;">
                    @if($invoice->description)
                        <div style="padding: 10px 0;"><strong>{{ strtoupper($invoice->description) }}</strong></div>
                    @endif
                    @foreach($invoice->items as $item)
                        <div style="padding: 10px 0;">{{ strtoupper($item->description) }}</div>
                    @endforeach
                </td>
                <td class="text-center" style="border-bottom: none; border-top: none;">
                    @if($invoice->description)<div style="padding: 10px 0;">&nbsp;</div>@endif
                    @foreach($invoice->items as $item)
                        <div style="padding: 10px 0;">{{ $item->quantity + 0 }}no.</div>
                    @endforeach
                </td>
                <td class="text-right" style="border-bottom: none; border-top: none;">
                    @if($invoice->description)<div style="padding: 10px 0;">&nbsp;</div>@endif
                    @foreach($invoice->items as $item)
                        <div style="padding: 10px 0;">{{ number_format($item->unit_price, 2) }}</div>
                    @endforeach
                </td>
                <td class="text-right" style="border-bottom: none; border-top: none;">
                    @if($invoice->description)<div style="padding: 10px 0;">&nbsp;</div>@endif
                    @foreach($invoice->items as $item)
                        <div style="padding: 10px 0;">{{ number_format($item->total, 2) }}</div>
                    @endforeach
                </td>
            </tr>
            <tr>
                <th colspan="3" class="text-right">TOTAL</th>
                <th class="text-right">{{ number_format($invoice->total_amount, 2) }}</th>
            </tr>
        </table>

        <table style="border-top: none;">
            <tr>
                <td colspan="2" style="border-top: none;">
                    Amount in words: {{ getIndianCurrency($invoice->total_amount) }}
                </td>
            </tr>
            <tr>
                <td style="width: 50%; height: 120px; position: relative;">
                    Received the above goods in good condition.<br><br><br><br><br>
                    <div style="position: absolute; bottom: 10px;">Customer Signature</div>
                </td>
                <td style="width: 50%; height: 120px; position: relative; text-align: right;">
                    for <strong>SYSCOM INFOTECH,</strong><br><br><br><br>
                    @if($invoice->authorized_signatory)
                        <div style="font-family: 'Brush Script MT', cursive; font-size: 24px; position: absolute; bottom: 30px; right: 10px;">
                            {{ $invoice->authorized_signatory }}
                        </div>
                    @endif
                    <div style="position: absolute; bottom: 10px; right: 10px;">Authorized Signatory</div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
