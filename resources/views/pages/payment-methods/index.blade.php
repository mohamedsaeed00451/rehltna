@extends('layouts.app')

@section('styles')
    <style>
        .payment-card {
            transition: all 0.3s ease;
            border: 1px solid #e0e0e0;
        }

        .payment-card:hover {
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            transform: translateY(-5px);
        }

        .payment-img-wrapper {
            height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            border-bottom: 1px solid #eee;
            padding: 1px;
        }

        .payment-img {
            max-height: 100%;
            max-width: 100%;
            object-fit: contain;
        }

        .section-label {
            font-size: 13px;
            font-weight: bold;
            color: #666;
            margin-bottom: 5px;
            text-transform: uppercase;
            display: block;
        }

        .input-group-text {
            background-color: #f1f2f9;
            border: 1px solid #e1e5ef;
            color: #5b6e88;
        }
    </style>
@endsection

@section('content')

    <div class="breadcrumb-header justify-content-between mb-4">
        <div class="my-auto">
            <div class="d-flex">
                <a href="{{ route('dashboard') }}" class="content-title mb-0 my-auto text-dark">Home</a>
                <span class="text-muted mt-1 tx-13 ms-2 mb-0">/ Payment Methods</span>
            </div>
        </div>
    </div>

    <div class="row">
        @foreach($paymentMethods as $method)
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card custom-card payment-card h-auto">
                    <div class="payment-img-wrapper">
                        @if($method->code === 'bank_transfer')
                            <img src="{{ asset($method->banner_en) }}" alt="{{ $method->title_en }}"
                                 class="payment-img w-50 h-50">
                        @else
                            <img src="{{ asset($method->banner_en) }}" alt="{{ $method->title_en }}"
                                 class="payment-img">
                        @endif
                    </div>

                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0 font-weight-bold">{{ $method->title_en }}</h5>
                            <div
                                class="main-toggle {{ $method->status ? 'main-toggle-success on' : 'main-toggle-danger of' }} toggle-status-btn"
                                data-id="{{ $method->id }}">
                                <span></span>
                            </div>
                        </div>

                        <form action="{{ route('payment-methods.update', $method->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            @if($method->code === 'bank_transfer')
                                <div class="row">
                                    <div class="col-12 form-group">
                                        <label class="section-label">Bank Name</label>
                                        <input type="text" name="config[bank_name]" class="form-control"
                                               value="{{ $method->config['bank_name'] ?? '' }}" placeholder="e.g. NBE">
                                    </div>

                                    <div class="col-12 form-group">
                                        <label class="section-label">Account Name</label>
                                        <input type="text" name="config[account_name]" class="form-control"
                                               value="{{ $method->config['account_name'] ?? '' }}"
                                               placeholder="Company Name">
                                    </div>

                                    <div class="col-12 form-group">
                                        <label class="section-label">Account Number</label>
                                        <input type="text" name="config[account_number]" class="form-control"
                                               value="{{ $method->config['account_number'] ?? '' }}">
                                    </div>

                                    <div class="col-12 form-group">
                                        <label class="section-label">IBAN</label>
                                        <input type="text" name="config[iban]" class="form-control"
                                               value="{{ $method->config['iban'] ?? '' }}">
                                    </div>

                                    <div class="col-12 form-group">
                                        <label class="section-label">SWIFT Code</label>
                                        <input type="text" name="config[swift_code]" class="form-control"
                                               value="{{ $method->config['swift_code'] ?? '' }}">
                                    </div>

                                    <div class="col-12 form-group">
                                        <label class="section-label">Bank Address</label>
                                        <textarea name="config[bank_address]" class="form-control"
                                                  rows="2">{{ $method->config['bank_address'] ?? '' }}</textarea>
                                    </div>

                                    <div class="col-12 form-group">
                                        <label class="section-label">Instructions (Shown to user)</label>
                                        <textarea name="config[instructions]" class="form-control"
                                                  rows="2">{{ $method->config['instructions'] ?? '' }}</textarea>
                                    </div>
                                </div>

                            @elseif($method->code === 'creditcard' || $method->code === 'instapay')
                                <div class="form-group">
                                    <label class="section-label">Environment Mode</label>
                                    <select name="config[mode]" class="form-control form-select">
                                        <option
                                            value="test" {{ ($method->config['mode'] ?? '') == 'test' ? 'selected' : '' }}>
                                            Test Mode (Sandbox)
                                        </option>
                                        <option
                                            value="live" {{ ($method->config['mode'] ?? '') == 'live' ? 'selected' : '' }}>
                                            Live Mode (Production)
                                        </option>
                                    </select>
                                </div>

                                <hr class="my-3">

                                <div class="form-group">
                                    <label class="section-label text-warning"><i class="fas fa-flask"></i> Test
                                        URL</label>
                                    <input type="url" name="config[test][url]"
                                           class="form-control"
                                           value="{{ $method->config['test']['url'] ?? '' }}"
                                           placeholder="https://test.api...">
                                </div>

                                <div class="form-group">
                                    <label class="section-label text-success"><i class="fas fa-check-circle"></i> Live
                                        URL</label>
                                    <input type="url" name="config[live][url]"
                                           class="form-control"
                                           value="{{ $method->config['live']['url'] ?? '' }}"
                                           placeholder="https://live.api...">
                                </div>

                            @else
                                <div class="alert alert-info">
                                    No configuration needed for this method.
                                </div>
                            @endif

                            <div class="mt-4 pt-2 border-top">
                                <button type="submit" class="btn btn-primary btn-block w-100">
                                    <i class="fas fa-save mr-1"></i> Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card custom-card mb-4 border-primary" style="border-top: 3px solid #007bff;">
                <div class="card-header pb-0">
                    <h5 class="card-title mb-0"><i class="fas fa-link"></i> Frontend Payment Redirect URLs</h5>
                    <p class="text-muted tx-13 mb-0">
                        URLs where user is redirected after payment.
                    </p>
                </div>
                <div class="card-body">
                    <form action="{{ route('update.settings') }}" method="POST">
                        @csrf
                        <div class="col-md-12 mb-3">
                            <label class="font-weight-bold text-success text-xs uppercase">
                                <i class="fas fa-check-circle"></i> Success URL
                            </label>
                            <input type="url" name="payment_success_url"
                                   class="form-control"
                                   placeholder="https://site.com/checkout/success"
                                   value="{{ get_setting('payment_success_url') }}">
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="font-weight-bold text-danger text-xs uppercase">
                                <i class="fas fa-times-circle"></i> Failed URL
                            </label>
                            <input type="url" name="payment_failed_url"
                                   class="form-control"
                                   placeholder="https://site.com/checkout/failed"
                                   value="{{ get_setting('payment_failed_url') }}">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="font-weight-bold text-warning text-xs uppercase">
                                <i class="fas fa-ban"></i> Cancel URL
                            </label>
                            <input type="url" name="payment_cancel_url"
                                   class="form-control"
                                   placeholder="https://site.com/checkout/cancel"
                                   value="{{ get_setting('payment_cancel_url') }}">
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary btn-block w-100">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

@endsection

@section('scripts')
    <script>
        $(document).on('click', '.toggle-status-btn', function () {
            let button = $(this);
            let paymentId = button.data('id');

            if (button.hasClass('disabled')) return;

            button.addClass('disabled');

            $.ajax({
                url: "{{ route('payment.methods.change.status', ['id' => ':id']) }}".replace(':id', paymentId),
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {
                    toastr.success("Status updated successfully");
                    if (response.status == 1) {
                        button.removeClass('main-toggle-danger of').addClass('main-toggle-success on');
                    } else {
                        button.removeClass('main-toggle-success on').addClass('main-toggle-danger of');
                    }
                    button.removeClass('disabled');
                },
                error: function () {
                    toastr.error("Something went wrong");
                    button.removeClass('disabled');
                }
            });
        });
    </script>
@endsection
