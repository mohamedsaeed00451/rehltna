<!DOCTYPE html>
@php
    $lang = app()->getLocale();
    $dir = $lang === 'ar' ? 'rtl' : 'ltr';
    $alignLeft = $lang === 'ar' ? 'right' : 'left';
    $alignRight = $lang === 'ar' ? 'left' : 'right';
@endphp
<html lang="{{ $lang }}" dir="{{ $dir }}">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; direction: {{ $dir }}; text-align: {{ $alignLeft }}; }
        .container { width: 100%; max-width: 600px; margin: 0 auto; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        .header { text-align: center; border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 20px; }
        .details { margin-bottom: 20px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: {{ $alignLeft }}; }
        .table th { background-color: #f4f4f4; }
        .text-right { text-align: {{ $alignRight }}; }
        .total { text-align: {{ $alignRight }}; margin-top: 10px; font-size: 16px; font-weight: bold; color: #243C56; }
        .footer { text-align: center; font-size: 12px; color: #777; margin-top: 20px; border-top: 1px solid #eee; padding-top: 10px; }
        .badge { display: inline-block; padding: 4px 8px; font-size: 12px; font-weight: bold; color: #fff; background-color: #28a745; border-radius: 4px; }
    </style>
    <title>{{ __('invoice.title') }}</title>
</head>
<body>
<div class="container">
    <div class="header">
        <h2>{{ __('invoice.payment_successful') }}</h2>
        <p>{{ __('invoice.order_id') }} #{{ $order->transaction_token }}</p>
        <span class="badge">{{ __('invoice.status_paid') }}</span>
    </div>

    <div class="details">
        <p><strong>{{ __('invoice.hello') }} {{ $order->name }},</strong></p>
        <p>{{ __('invoice.intro_msg') }}</p>
    </div>

    @if($order->items && $order->items->count() > 0)

        <table class="table">
            <thead>
            <tr>
                <th>{{ __('invoice.col_course') }}</th>
                <th>{{ __('invoice.col_attendance') }}</th>
                <th>{{ __('invoice.col_price') }}</th>
                <th>{{ __('invoice.col_total') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach($order->items as $item)
                @php
                    // محاولة جلب الاسم حسب اللغة أو العودة للإنجليزية
                    $itemName = $item->item->{'title_' . $lang} ?? $item->item->title_en ?? 'Item #' . $item->item_id;
                @endphp
                <tr>
                    <td>{{ $itemName }}</td>
                    <td>{{ $item->attendees_count }}</td>
                    <td>{{ number_format($item->price_per_unit, 2) }}</td>
                    <td>{{ number_format($item->total, 2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @else

        <table class="table">
            <thead>
            <tr>
                <th>{{ __('invoice.col_desc') }}</th>
                <th class="text-right">{{ __('invoice.col_amount') }}</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>
                    <strong>{{ __('invoice.custom_payment') }}</strong><br>
                    <small style="color: #777;">{{ __('invoice.payment_ref') }}: {{ $order->transaction_token ?? $order->id }}</small>
                </td>
                <td class="text-right">{{ number_format($order->total_amount, 2) }}</td>
            </tr>
            </tbody>
        </table>
    @endif

    <div style="margin-top: 15px; border-top: 2px solid #eee; padding-top: 10px;">

        @if($order->items && $order->items->count() > 0)
            <p style="text-align: {{ $alignRight }}; margin: 5px 0; font-size: 13px;">
                {{ __('invoice.sub_total') }}: {{ number_format($order->sub_total, 2) }}
            </p>
            @if($order->discount_amount > 0)
                <p style="text-align: {{ $alignRight }}; margin: 5px 0; color: red; font-size: 13px;">
                    {{ __('invoice.discount') }}: -{{ number_format($order->discount_amount, 2) }}
                </p>
            @endif
        @endif

        <div class="total">
            {{ __('invoice.total_paid') }}: {{ number_format($order->total_amount, 2) }} <small>$</small>
        </div>
    </div>

    <div class="footer">
        <p>{{ __('invoice.date') }}: {{ $order->created_at->format('Y-m-d H:i') }}</p>
        <p>{{ __('invoice.footer_contact') }}</p>
    </div>
</div>
</body>
</html>
