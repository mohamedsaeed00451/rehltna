@extends('layouts.app')

@section('styles')
    <style>
        /* Card Styling & Hover Effects */
        .payment-card {
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            overflow: hidden;
            background: #ffffff;
        }

        .payment-card:hover {
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.05);
            transform: translateY(-5px);
            border-color: #cbd5e1;
        }

        /* Image Wrapper */
        .payment-img-wrapper {
            height: 130px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
            border-bottom: 1px solid #e2e8f0;
            padding: 20px;
        }

        .payment-img {
            max-height: 85px;
            max-width: 80%;
            object-fit: contain;
            filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.04));
        }

        /* Form Labels & Inputs */
        .section-label {
            font-size: 11px;
            font-weight: 800;
            color: #64748b;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: block;
        }

        .input-group-text {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            color: #475569;
            width: 45px;
            justify-content: center;
            transition: all 0.2s;
        }

        .form-control, .form-select {
            border: 1px solid #cbd5e1;
            font-size: 14px;
            transition: all 0.2s;
            background-color: #fcfcfc;
        }

        .form-control:focus, .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            background-color: #ffffff;
        }

        .form-control:focus + .input-group-text {
            border-color: #3b82f6;
            color: #3b82f6;
        }

        /* Info Boxes (Tamara) */
        .config-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .main-toggle {
            cursor: pointer;
        }
    </style>
@endsection

@section('content')

    <div class="breadcrumb-header justify-content-between mb-4">
        <div class="my-auto">
            <div class="d-flex align-items-center">
                <a href="{{ route('dashboard') }}" class="content-title mb-0 my-auto text-dark fw-bold">Home</a>
                <span class="text-muted mt-1 tx-13 ms-2 mb-0">/ Payment Methods</span>
            </div>
        </div>
    </div>

    <div class="row">

        <div class="col-xl-6 col-md-6 mb-4">
            <div class="card custom-card h-100 mb-0 border-primary" style="border-top: 4px solid #3b82f6;">
                <div class="card-header pb-0 border-bottom-0 pt-4 px-4">
                    <h5 class="card-title mb-1 fw-bold text-dark"><i class="fas fa-link text-primary me-2"></i>Frontend
                        Redirect
                        URLs</h5>
                    <p class="text-muted tx-13 mb-0">Frontend endpoints for payment flow.</p>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('update.settings') }}" method="POST" class="h-100 d-flex flex-column">
                        @csrf
                        <div class="flex-grow-1 mt-2">
                            <div class="mb-4">
                                <label class="section-label text-success"><i class="fas fa-check-circle"></i> Success
                                    URL</label>
                                <input type="url" name="payment_success_url" class="form-control shadow-sm bg-light"
                                       placeholder="https://site.com/checkout/success"
                                       value="{{ get_setting('payment_success_url') }}">
                            </div>

                            <div class="mb-4">
                                <label class="section-label text-danger"><i class="fas fa-times-circle"></i> Failed URL</label>
                                <input type="url" name="payment_failed_url" class="form-control shadow-sm bg-light"
                                       placeholder="https://site.com/checkout/failed"
                                       value="{{ get_setting('payment_failed_url') }}">
                            </div>

                            <div class="mb-4">
                                <label class="section-label text-warning"><i class="fas fa-ban"></i> Cancel URL</label>
                                <input type="url" name="payment_cancel_url" class="form-control shadow-sm bg-light"
                                       placeholder="https://site.com/checkout/cancel"
                                       value="{{ get_setting('payment_cancel_url') }}">
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-dark btn-block w-100 rounded-3 shadow-sm fw-bold"
                                    style="height: 48px; letter-spacing: 0.5px;">
                                <i class="fas fa-save me-1"></i> Save URLs
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-6 col-md-6 mb-4">
            <div class="card custom-card h-100 mb-0 border-primary" style="border-top: 4px solid #3b82f6;">
                <div class="card-header pb-0 border-bottom-0 pt-4 px-4">
                    <h5 class="card-title mb-1 fw-bold text-dark"><i class="fas fa-link text-primary me-2"></i>APP
                        Redirect
                        URLs</h5>
                    <p class="text-muted tx-13 mb-0">App endpoints for payment flow.</p>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('update.settings') }}" method="POST" class="h-100 d-flex flex-column">
                        @csrf
                        <div class="flex-grow-1 mt-2">
                            <div class="mb-4">
                                <label class="section-label text-success"><i class="fas fa-check-circle"></i> Success
                                    URL</label>
                                <input type="url" name="payment_success_url_app" class="form-control shadow-sm bg-light"
                                       placeholder="https://site.com/checkout/success"
                                       value="{{ get_setting('payment_success_url_app') }}">
                            </div>

                            <div class="mb-4">
                                <label class="section-label text-danger"><i class="fas fa-times-circle"></i> Failed URL</label>
                                <input type="url" name="payment_failed_url_app" class="form-control shadow-sm bg-light"
                                       placeholder="https://site.com/checkout/failed"
                                       value="{{ get_setting('payment_failed_url_app') }}">
                            </div>

                            <div class="mb-4">
                                <label class="section-label text-warning"><i class="fas fa-ban"></i> Cancel URL</label>
                                <input type="url" name="payment_cancel_url_app" class="form-control shadow-sm bg-light"
                                       placeholder="https://site.com/checkout/cancel"
                                       value="{{ get_setting('payment_cancel_url_app') }}">
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-dark btn-block w-100 rounded-3 shadow-sm fw-bold"
                                    style="height: 48px; letter-spacing: 0.5px;">
                                <i class="fas fa-save me-1"></i> Save URLs
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @foreach($paymentMethods as $method)
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card custom-card payment-card mb-0">
                    <div class="payment-img-wrapper">
                        <img src="{{ asset($method->banner_en) }}" alt="{{ $method->title_en }}"
                             class="payment-img">
                    </div>

                    <div class="card-body d-flex flex-column flex-grow-1 p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                            <h5 class="card-title mb-0 font-weight-bold text-dark">{{ $method->title_en }}</h5>
                            <div
                                class="main-toggle {{ $method->status ? 'main-toggle-success on' : 'main-toggle-danger off' }} toggle-status-btn"
                                data-id="{{ $method->id }}">
                                <span></span>
                            </div>
                        </div>

                        <form action="{{ route('payment-methods.update', $method->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="flex-grow-1">
                                @if(str_contains($method->code, 'bank_transfer'))
                                    <div class="row">
                                        <div class="col-12 mb-3">
                                            <label class="section-label">Bank Name</label>
                                            <div class="input-group shadow-sm">
                                                <span class="input-group-text"><i class="fas fa-university"></i></span>
                                                <input type="text" name="config[bank_name]" class="form-control fw-bold"
                                                       value="{{ $method->config['bank_name'] ?? '' }}"
                                                       placeholder="e.g. AlAhli Bank">
                                            </div>
                                        </div>

                                        <div class="col-12 mb-3">
                                            <label class="section-label">Account Name</label>
                                            <div class="input-group shadow-sm">
                                                <span class="input-group-text"><i class="fas fa-user-tie"></i></span>
                                                <input type="text" name="config[account_name]" class="form-control"
                                                       value="{{ $method->config['account_name'] ?? '' }}"
                                                       placeholder="Company Name">
                                            </div>
                                        </div>

                                        <div class="col-12 mb-3">
                                            <label class="section-label">Account Number</label>
                                            <div class="input-group shadow-sm">
                                                <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                                                <input type="text" name="config[account_number]"
                                                       class="form-control fw-bold text-primary"
                                                       value="{{ $method->config['account_number'] ?? '' }}"
                                                       style="letter-spacing: 1px;">
                                            </div>
                                        </div>

                                        <div class="col-12 mb-3">
                                            <label class="section-label">IBAN</label>
                                            <div class="input-group shadow-sm">
                                                <span class="input-group-text"><i class="fas fa-money-check"></i></span>
                                                <input type="text" name="config[iban]" class="form-control"
                                                       value="{{ $method->config['iban'] ?? '' }}"
                                                       style="font-family: monospace; font-size: 13px;">
                                            </div>
                                        </div>

                                        <div class="col-12 mb-3">
                                            <label class="section-label">Instructions</label>
                                            <div class="input-group shadow-sm">
                                                <span class="input-group-text"><i class="fas fa-info-circle"></i></span>
                                                <textarea name="config[instructions]" class="form-control"
                                                          rows="2">{{ $method->config['instructions'] ?? '' }}</textarea>
                                            </div>
                                        </div>
                                    </div>

                                @elseif($method->code === 'tamara')
                                    <div class="mb-4">
                                        <label class="section-label">Environment Mode</label>
                                        <div class="input-group shadow-sm">
                                            <span class="input-group-text"><i class="fas fa-cogs"></i></span>
                                            <select name="config[mode]" class="form-control form-select fw-bold">
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
                                    </div>

                                    <div class="config-box">
                                        <h6 class="text-warning fw-bold mb-3 border-bottom pb-2"><i
                                                class="fas fa-flask me-1"></i> Test Configuration</h6>
                                        <div class="mb-2">
                                            <label class="section-label">Base URL</label>
                                            <input type="url" name="config[test][base_url]"
                                                   class="form-control shadow-sm"
                                                   value="{{ $method->config['test']['base_url'] ?? '' }}">
                                        </div>
                                        <div class="mb-2">
                                            <label class="section-label">Public Key</label>
                                            <input type="text" name="config[test][public_key]"
                                                   class="form-control shadow-sm"
                                                   value="{{ $method->config['test']['public_key'] ?? '' }}">
                                        </div>
                                        <div class="mb-0">
                                            <label class="section-label">Secret Key</label>
                                            <input type="text" name="config[test][secret_key]"
                                                   class="form-control shadow-sm"
                                                   value="{{ $method->config['test']['secret_key'] ?? '' }}">
                                        </div>
                                    </div>

                                    <div class="config-box">
                                        <h6 class="text-success fw-bold mb-3 border-bottom pb-2"><i
                                                class="fas fa-check-circle me-1"></i> Live Configuration</h6>
                                        <div class="mb-2">
                                            <label class="section-label">Base URL</label>
                                            <input type="url" name="config[live][base_url]"
                                                   class="form-control shadow-sm"
                                                   value="{{ $method->config['live']['base_url'] ?? '' }}">
                                        </div>
                                        <div class="mb-2">
                                            <label class="section-label">Public Key</label>
                                            <input type="text" name="config[live][public_key]"
                                                   class="form-control shadow-sm"
                                                   value="{{ $method->config['live']['public_key'] ?? '' }}">
                                        </div>
                                        <div class="mb-0">
                                            <label class="section-label">Secret Key</label>
                                            <input type="text" name="config[live][secret_key]"
                                                   class="form-control shadow-sm"
                                                   value="{{ $method->config['live']['secret_key'] ?? '' }}">
                                        </div>
                                    </div>
                                @else
                                    <div
                                        class="alert alert-info border-0 shadow-sm d-flex align-items-center rounded-3">
                                        <i class="fas fa-info-circle fs-4 me-3 text-info"></i>
                                        <div class="text-dark fw-medium">No extra configuration needed for this payment
                                            method.
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="mt-4 pt-3 border-top">
                                <button type="submit"
                                        class="btn btn-primary btn-block w-100 rounded-3 shadow-sm fw-bold"
                                        style="height: 48px; letter-spacing: 0.5px;">
                                    <i class="fas fa-save me-1"></i> Save Configuration
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach

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
                        button.removeClass('main-toggle-danger off').addClass('main-toggle-success on');
                    } else {
                        button.removeClass('main-toggle-success on').addClass('main-toggle-danger off');
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
