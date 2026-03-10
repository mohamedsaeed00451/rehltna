@extends('layouts.app')

@section('styles')
    <style>
        /* Global Background */
        body {
            background-color: #f8fafc;
        }

        /* Card Styling */
        .custom-card {
            border: none;
            border-radius: 24px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.03);
            background: #fff;
            overflow: visible !important;
            margin-top: 20px;
        }

        /* Hero Header Section */
        .hero-section {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            border-radius: 24px;
            padding: 45px 35px;
            margin-bottom: 35px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 20px 30px rgba(0, 0, 0, 0.12);
        }

        /* FIX: Table Header Visibility & Space */
        .table-responsive {
            border-radius: 18px;
            overflow: hidden;
            padding: 2px;
        }

        .table {
            margin-bottom: 0;
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
        }

        .table thead th {
            background-color: #f1f5f9 !important;
            color: #475569 !important;
            text-transform: uppercase !important;
            font-size: 11px !important;
            font-weight: 800 !important;
            letter-spacing: 1.5px !important;
            padding: 25px 20px !important;
            border: none !important;
            border-bottom: 2px solid #e2e8f0 !important;
            line-height: 1.5 !important;
            vertical-align: middle !important;
        }

        .table tbody tr {
            transition: all 0.3s ease;
        }

        .table tbody tr:hover {
            background-color: #f8fafc;
            transform: translateY(-1px);
        }

        .table td {
            padding: 22px 20px;
            vertical-align: middle;
            color: #334155;
            border-top: 1px solid #f1f5f9;
        }

        /* FIX: Tools Button (No disappearing text) */
        .btn-tools-container .btn-tools {
            background-color: rgba(255, 255, 255, 0.12) !important;
            border: 1px solid rgba(255, 255, 255, 0.25) !important;
            color: #ffffff !important;
            font-weight: 600 !important;
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1) !important;
            backdrop-filter: blur(8px);
        }

        .btn-tools-container .btn-tools:hover {
            background-color: #ffffff !important;
            color: #1e293b !important;
            border-color: #ffffff !important;
            transform: translateY(-3px);
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.2) !important;
        }

        .btn-tools-container .btn-tools:hover .fa-file-excel {
            color: #10b981 !important;
        }

        /* Price Tag */
        .price-badge {
            background: #ecfdf5;
            color: #065f46;
            padding: 8px 16px;
            border-radius: 14px;
            font-weight: 800;
            border: 1px solid #d1fae5;
        }

        /* Action Buttons */
        .btn-action {
            width: 42px;
            height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: #fff;
            border: 1px solid #e2e8f0;
            color: #64748b;
        }

        .btn-action:hover {
            transform: scale(1.15) rotate(5deg);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.08);
        }

        .btn-edit-course:hover {
            color: #4f46e5;
            border-color: #4f46e5;
            background: #f5f3ff;
        }

        .btn-delete-course:hover {
            color: #ef4444;
            border-color: #ef4444;
            background: #fef2f2;
        }

        .lang-tag {
            font-size: 9px;
            padding: 2px 6px;
            background: #64748b;
            color: white;
            border-radius: 5px;
            margin-left: 8px;
        }

        .main-toggle {
            cursor: pointer;
        }
    </style>
@endsection

@section('content')

    <div class="breadcrumb-header justify-content-between mb-4 mt-3">
        <div class="my-auto">
            <h4 class="content-title mb-0 fw-bold" style="color: #1e293b; letter-spacing: -0.8px; font-size: 1.7rem;">
                Courses Catalog
            </h4>
            <p class="text-muted mb-0 small fw-medium">Management / <span class="text-primary">Global Items</span></p>
        </div>
    </div>

    <div class="hero-section">
        <div>
            <h3 class="mb-2 fw-bold" style="letter-spacing: -0.5px;">Premium Course Hub</h3>
            <p class="mb-0 opacity-75 fw-medium">Customize your catalog visibility and pricing strategy in
                real-time.</p>
        </div>
        <div class="d-flex gap-3 align-items-center">
            @if(\App\Models\ErrorUploaded::query()->where('type','item')->count() > 0)
                <a href="{{ route('items.export-excel-error-uploaded') }}"
                   class="btn btn-danger rounded-pill px-4 shadow-sm fw-bold">
                    <i class="fas fa-bug me-2"></i> Error Logs
                </a>
            @endif

            <div class="dropdown btn-tools-container">
                <button class="btn btn-tools dropdown-toggle rounded-pill px-4 shadow-sm" type="button"
                        data-bs-toggle="dropdown">
                    <i class="fas fa-file-excel me-2 text-success"></i> Tools
                </button>
                <ul class="dropdown-menu shadow-lg border-0 p-2 mt-3" style="border-radius: 18px; min-width: 200px;">
                    <li><a class="dropdown-item rounded-3 py-2 fw-600" href="{{ route('items.export-excel-temp') }}"><i
                                class="fas fa-download me-2 text-warning"></i>Get Excel Template</a></li>
                    <li>
                        <hr class="dropdown-divider opacity-50">
                    </li>
                    <li><a class="dropdown-item rounded-3 py-2 fw-600 modal-effect" data-bs-toggle="modal"
                           href="#importExcel"><i class="fas fa-upload me-2 text-success"></i>Bulk Import Data</a></li>
                </ul>
            </div>

            <a href="{{ route('items.create') }}"
               class="btn btn-primary rounded-pill px-4 shadow-lg fw-bold border-0 d-flex align-items-center"
               style="background: #ffffff; color: #4f46e5; height: 48px;">
                <i class="fas fa-plus-circle me-2"></i> New Course
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table" id="table">
                            <thead>
                            <tr>
                                <th class="text-center" width="80">Index</th>
                                @foreach(get_active_langs() as $lang)
                                    <th>Course Title <span class="lang-tag">{{ strtoupper($lang) }}</span></th>
                                @endforeach
                                <th>Price</th>
                                <th class="text-center">Order</th>
                                <th class="text-center">Featured</th>
                                <th class="text-center">Visibility</th>
                                <th class="text-center px-4">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($items as $item)
                                <tr>
                                    <td class="text-center fw-bold text-muted">#{{ $loop->iteration }}</td>

                                    @foreach(get_active_langs() as $lang)
                                        <td>
                                            <div class="fw-bold text-dark"
                                                 style="max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                {{ $item->{'title_'.$lang} }}
                                            </div>
                                        </td>
                                    @endforeach

                                    <td><span class="price-badge">{{ number_format($item->price, 0) }} $</span></td>

                                    <td class="text-center">
                                        <div class="order-indicator mx-auto">{{ $item->order ?? 0}}</div>
                                    </td>

                                    <td class="text-center">
                                        <div
                                            class="toggle-is-feature-btn main-toggle mx-auto {{ $item->is_feature == 1 ? 'main-toggle-success on' : 'main-toggle-danger of' }}"
                                            data-id="{{ $item->id }}">
                                            <span></span>
                                        </div>
                                    </td>

                                    <td class="text-center">
                                        <div
                                            class="toggle-status-btn main-toggle mx-auto {{ $item->status == 1 ? 'main-toggle-success on' : 'main-toggle-danger of' }}"
                                            data-id="{{ $item->id }}">
                                            <span></span>
                                        </div>
                                    </td>

                                    <td class="text-center px-4">
                                        <div class="d-flex justify-content-center gap-2">
                                            <a class="btn-action btn-edit-course"
                                               href="{{ route('items.edit', encrypt($item->id)) }}"
                                               title="Edit Item">
                                                <i class="las la-pen fs-18"></i>
                                            </a>
                                            <a class="btn-action btn-delete-course delete-btn"
                                               data-route="{{ route('items.destroy',$item->id) }}"
                                               data-bs-toggle="modal" href="#" data-bs-target="#deleteModal"
                                               title="Remove Item">
                                                <i class="las la-trash fs-18"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 p-4 mt-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <p class="text-muted small mb-0 fw-bold">Records: {{ $items->firstItem() }}
                            - {{ $items->lastItem() }} / Total: {{ $items->total() }}</p>
                        {{ $items->links() }}
                    </div>
                </div>
            </div>

            @include('pages.models.confirm-delete')
            @include('pages.items.models.import-excel')

        </div>
    </div>
@endsection

@section('scripts')
    <script>

        $(document).on('click', '.toggle-status-btn', function () {
            let button = $(this);
            let itemId = button.data('id');
            $.ajax({
                url: "{{ route('items.change.status', ['id' => ':id']) }}".replace(':id', itemId),
                type: 'POST',
                data: {_token: '{{ csrf_token() }}'},
                success: function (response) {
                    toastr.success("Visibility Sync Successful");
                    if (response.status == 1) {
                        button.removeClass('main-toggle-danger of').addClass('main-toggle-success on');
                    } else {
                        button.removeClass('main-toggle-success on').addClass('main-toggle-danger of');
                    }
                }
            });
        });

        $(document).on('click', '.toggle-is-feature-btn', function () {
            let button = $(this);
            let itemId = button.data('id');
            $.ajax({
                url: "{{ route('items.change.is_feature', ['id' => ':id']) }}".replace(':id', itemId),
                type: 'POST',
                data: {_token: '{{ csrf_token() }}'},
                success: function (response) {
                    toastr.success("Featured Logic Applied");
                    if (response.is_feature == 1) {
                        button.removeClass('main-toggle-danger of').addClass('main-toggle-success on');
                    } else {
                        button.removeClass('main-toggle-success on').addClass('main-toggle-danger of');
                    }
                }
            });
        });

        document.addEventListener('DOMContentLoaded', function () {
            confirmDelete();
        });
    </script>
@endsection
