@extends('layouts.app')

@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
    <style>
        /* Wizard & Layout Styles */
        .wizard-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            position: relative;
        }

        .wizard-steps::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 2px;
            background: #e9ecef;
            transform: translateY(-50%);
            z-index: 0;
        }

        .step-item {
            position: relative;
            z-index: 1;
            text-align: center;
            width: 100%;
        }

        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #fff;
            border: 2px solid #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-weight: bold;
            color: #6c757d;
            transition: all 0.3s;
        }

        .step-item.active .step-circle {
            background: #0d6efd;
            border-color: #0d6efd;
            color: #fff;
            box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.2);
        }

        .step-item.completed .step-circle {
            background: #198754;
            border-color: #198754;
            color: #fff;
        }

        .step-label {
            font-size: 12px;
            font-weight: 600;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .step-item.active .step-label {
            color: #0d6efd;
        }

        .step-content {
            display: none;
            animation: fadeIn 0.4s ease-in-out;
        }

        /* FIX: Mobile Select Issue */
        .step-content.active {
            display: block;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Form Controls */
        .form-label {
            font-weight: 600;
            font-size: 0.9rem;
            color: #344767;
            margin-bottom: 0.5rem;
        }

        .form-control, .form-select {
            border: 1px solid #e0e6ed;
            border-radius: 8px;
            padding: 10px 15px;
            transition: all 0.2s;
            background-color: #fcfcfc;
        }

        .form-control:focus, .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
            background-color: #fff;
        }

        .form-control.is-invalid, .form-select.is-invalid {
            border-color: #dc3545;
            background-image: none;
        }

        /* Media Selector */
        .media-selector-group {
            position: relative;
            display: flex;
            align-items: center;
            border: 1px solid #e1e5ef;
            border-radius: 8px;
            background-color: #fff;
            padding: 4px;
            transition: all 0.3s;
        }

        .media-selector-group input {
            border: none;
            box-shadow: none;
            background: transparent !important;
            padding-left: 10px;
            color: #555;
            font-weight: 500;
            width: 100%;
            cursor: pointer;
        }

        .media-selector-group .btn-choose {
            border-radius: 6px;
            padding: 8px 20px;
            font-weight: 600;
            font-size: 12px;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }

        .media-preview-card {
            margin-top: 10px;
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 5px;
            background: #fafafa;
            display: inline-block;
            position: relative;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .media-preview-card img, .media-preview-card video {
            border-radius: 6px;
            display: block;
            max-width: 100%;
        }

        .remove-btn {
            position: absolute;
            top: -8px;
            right: -8px;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #ff4d4f;
            color: white;
            border: 2px solid #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            font-size: 12px;
            z-index: 10;
        }

        .card, .card-body {
            overflow: visible !important;
        }

        /* Packages */
        .package-item {
            border: 1px solid #e9ecef;
            border-radius: 12px;
            overflow: hidden;
            transition: transform 0.2s;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
            margin-bottom: 20px;
        }

        .package-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.05);
        }

        .package-header {
            background: #f8f9fa;
            padding: 12px 20px;
            border-bottom: 1px solid #e9ecef;
        }
    </style>
@endsection

@section('content')

    <div class="breadcrumb-header justify-content-between mb-4">
        <div class="my-auto">
            <div class="d-flex align-items-center">
                <a href="{{ route('dashboard') }}" class="content-title mb-0 my-auto text-dark hover-primary">Home</a>
                <span class="text-muted mt-1 tx-13 ms-2 mb-0">/ Edit Course</span>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">

            <div class="wizard-steps">
                <div class="step-item active" data-step="1">
                    <div class="step-circle"><i class="fas fa-info"></i></div>
                    <div class="step-label">Basic Info</div>
                </div>
                <div class="step-item" data-step="2">
                    <div class="step-circle"><i class="fas fa-align-left"></i></div>
                    <div class="step-label">Content</div>
                </div>
                <div class="step-item" data-step="3">
                    <div class="step-circle"><i class="fas fa-photo-video"></i></div>
                    <div class="step-label">Media</div>
                </div>
                <div class="step-item" data-step="4">
                    <div class="step-circle"><i class="fas fa-search"></i></div>
                    <div class="step-label">SEO</div>
                </div>
                <div class="step-item" data-step="5">
                    <div class="step-circle"><i class="fas fa-box-open"></i></div>
                    <div class="step-label">Packages</div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-4">
                    <form action="{{ route('items.update', $item->id) }}" method="POST" enctype="multipart/form-data"
                          id="editForm">
                        @csrf
                        @method('PUT')

                        <div class="step-content active" id="step-1">
                            <h5 class="mb-4 text-primary fw-bold border-bottom pb-2">Step 1: Basic Information</h5>
                            <div class="row">
                                @foreach(get_active_langs() as $lang)
                                    <div class="{{ colClass() }} mb-3">
                                        <label class="form-label">Title ({{ strtoupper($lang) }}) <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="title_{{ $lang }}" class="form-control form-control-lg"
                                               value="{{ old('title_'.$lang, $item->{'title_'.$lang}) }}" required>
                                    </div>
                                @endforeach
                                @foreach(get_active_langs() as $lang)
                                    <div class="{{ colClass() }} mb-3">
                                        <label class="form-label">Slug ({{ strtoupper($lang) }})</label>
                                        <input type="text" name="slug_{{ $lang }}" class="form-control bg-light"
                                               value="{{ old('slug_'.$lang, $item->{'slug_'.$lang}) }}" readonly>
                                    </div>
                                @endforeach
                                <div class="col-md-3 mb-3"><label class="form-label">Type <span
                                            class="text-danger">*</span></label><select name="item_type_id"
                                                                                        class="form-select"
                                                                                        required>@foreach($itemTypes as $t)
                                            <option
                                                value="{{$t->id}}" {{$item->item_type_id==$t->id?'selected':''}}>{{transDB($t,'title')}}</option>
                                        @endforeach</select></div>
                                <div class="col-md-3 mb-3"><label class="form-label">Status</label><select name="status"
                                                                                                           class="form-select">
                                        <option value="1" {{$item->status==1?'selected':''}}>Active</option>
                                        <option value="0" {{$item->status==0?'selected':''}}>Inactive</option>
                                    </select></div>
                                <div class="col-md-3 mb-3"><label class="form-label">Feature</label><select
                                        name="is_feature" class="form-select">
                                        <option value="1" {{$item->is_feature==1?'selected':''}}>Feature</option>
                                        <option value="0" {{$item->is_feature==0?'selected':''}}>Not Feature</option>
                                    </select></div>
                                <div class="col-md-3 mb-3"><label class="form-label">Order</label><select name="order"
                                                                                                          class="form-select"
                                                                                                          required>@for($i=1;$i<=5;$i++)
                                            <option value="{{$i}}" {{$item->order==$i?'selected':''}}>{{$i}}</option>
                                        @endfor</select></div>
                            </div>
                        </div>

                        <div class="step-content" id="step-2">
                            <h5 class="mb-4 text-primary fw-bold border-bottom pb-2">Step 2: Course Content</h5>
                            <div class="row">
                                @foreach(get_active_langs() as $lang)
                                    <div class="{{ colClass() }} mb-3"><label class="form-label">Short Description
                                            ({{ strtoupper($lang) }})</label><textarea
                                            name="short_description_{{ $lang }}" class="form-control"
                                            rows="3">{{ old('short_description_'.$lang, $item->{'short_description_'.$lang}) }}</textarea>
                                    </div>
                                @endforeach
                                <div class="col-12">
                                    <hr class="my-4">
                                </div>
                                @foreach(get_active_langs() as $lang)
                                    <div class="{{ colClass() }} mb-4"><label class="form-label">Full Description
                                            ({{ strtoupper($lang) }})</label><textarea name="description_{{ $lang }}"
                                                                                       id="description_{{ $lang }}"
                                                                                       class="form-control">{{ old('description_'.$lang, $item->{'description_'.$lang}) }}</textarea>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="step-content" id="step-3">
                            <h5 class="mb-4 text-primary fw-bold border-bottom pb-2">Step 3: Media & Assets</h5>
                            <div class="row mb-4">
                                @foreach(get_active_langs() as $lang)
                                    <div class="{{ colClass() }} mb-3">
                                        <label class="form-label">Banner ({{ strtoupper($lang) }})</label>
                                        <div class="media-selector-group">
                                            <input type="text" id="banner_{{ $lang }}" name="banner_{{ $lang }}"
                                                   value="{{ $item->{'banner_'.$lang} ? asset($item->{'banner_'.$lang}) : '' }}"
                                                   readonly placeholder="Select..."
                                                   onclick="$('#btn_banner_{{ $lang }}').click()">
                                            <button id="btn_banner_{{ $lang }}"
                                                    class="btn btn-primary btn-choose open-gallery" type="button"
                                                    data-input="banner_{{ $lang }}"
                                                    data-preview="preview_banner_{{ $lang }}">Choose
                                            </button>
                                        </div>
                                        <div id="preview_banner_{{ $lang }}">
                                            @if($item->{'banner_'.$lang})
                                                @php $ext = pathinfo($item->{'banner_'.$lang}, PATHINFO_EXTENSION); $isVid = in_array(strtolower($ext), ['mp4', 'mov', 'avi', 'mkv']); @endphp
                                                <div class="media-preview-card">
                                                    @if($isVid)
                                                        <video controls width="300" height="180">
                                                            <source src="{{ asset($item->{'banner_'.$lang}) }}">
                                                        </video>
                                                    @else
                                                        <img src="{{ asset($item->{'banner_'.$lang}) }}" width="200">
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <h6 class="text-dark fw-bold mb-3 border-bottom pb-2">Gallery</h6>
                                    <div class="bg-light p-4 rounded border border-dashed text-center">
                                        <button class="btn btn-outline-primary mb-3 open-gallery" type="button"
                                                data-input="gallery" data-preview="gallery_container" data-multi="true">
                                            <i class="fas fa-images me-1"></i> Add Images
                                        </button>
                                        <div id="gallery_container"
                                             class="d-flex flex-wrap justify-content-center gap-2">
                                            @foreach($item->galleries as $gallery)
                                                <div
                                                    class="d-inline-block position-relative shadow-sm border rounded bg-white"
                                                    style="width: 100px; height: 100px;">
                                                    <input type="hidden" name="gallery[]"
                                                           value="{{ asset($gallery->image) }}">
                                                    @if(str_contains($gallery->image, 'mp4'))
                                                        <video width="100%" height="100%" class="rounded bg-black">
                                                            <source src="{{ asset($gallery->image) }}">
                                                        </video>
                                                    @else
                                                        <img src="{{ asset($gallery->image) }}"
                                                             class="w-100 h-100 rounded" style="object-fit:cover">
                                                    @endif
                                                    <button type="button" class="remove-btn btn btn-danger btn-sm p-0"
                                                            onclick="$(this).parent().remove()">×
                                                    </button>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <h6 class="text-dark fw-bold mb-3 border-bottom pb-2">Speakers</h6>
                                    <div class="bg-light p-4 rounded border border-dashed text-center">
                                        <button class="btn btn-outline-info mb-3 open-gallery" type="button"
                                                data-input="speakers_gallery" data-preview="speakers_container"
                                                data-multi="true"><i class="fas fa-user-plus me-1"></i> Add Speakers
                                        </button>
                                        <div id="speakers_container"
                                             class="d-flex flex-wrap justify-content-center gap-2">
                                            @foreach($item->speakersGalleries as $gallery)
                                                <div
                                                    class="d-inline-block position-relative shadow-sm border rounded bg-white"
                                                    style="width: 100px; height: 100px;">
                                                    <input type="hidden" name="speakers_gallery[]"
                                                           value="{{ asset($gallery->image) }}">
                                                    <img src="{{ asset($gallery->image) }}" class="w-100 h-100 rounded"
                                                         style="object-fit:cover">
                                                    <button type="button" class="remove-btn btn btn-danger btn-sm p-0"
                                                            onclick="$(this).parent().remove()">×
                                                    </button>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 mb-3"><label class="form-label">PDF Attachment</label><input
                                        type="file" name="pdf" class="form-control"> @if($item->pdf)
                                        <a href="{{ asset($item->pdf) }}" target="_blank"
                                           class="small mt-1 d-block text-info">View current PDF</a>
                                    @endif</div>
                            </div>
                        </div>

                        <div class="step-content" id="step-4">
                            <h5 class="mb-4 text-primary fw-bold border-bottom pb-2">Step 4: Logistics & SEO</h5>

                            <div class="row mb-4">
                                <div class="col-md-3 mb-3"><label class="form-label">Price <span
                                            class="text-danger">*</span></label><input type="number" name="price"
                                                                                       class="form-control"
                                                                                       value="{{ $item->price }}"
                                                                                       required></div>
                                <div class="col-md-3 mb-3"><label class="form-label">Start <span
                                            class="text-danger">*</span></label><input type="date" name="start_date"
                                                                                       class="form-control"
                                                                                       value="{{ $item->start_date }}"
                                                                                       required></div>
                                <div class="col-md-3 mb-3"><label class="form-label">End <span
                                            class="text-danger">*</span></label><input type="date" name="end_date"
                                                                                       class="form-control"
                                                                                       value="{{ $item->end_date }}"
                                                                                       required></div>
                                <div class="col-md-3 mb-3"><label class="form-label">Map <span
                                            class="text-danger">*</span></label><input type="text" name="map"
                                                                                       class="form-control"
                                                                                       value="{{ $item->map }}"
                                                                                       required></div>
                            </div>

                            <div class="col-12">
                                <hr class="my-4 border-light">
                            </div>

                            <h6 class="text-dark fw-bold mb-3">SEO Configuration</h6>

                            <div class="mb-4">
                                <label class="form-label">Meta Image</label>
                                <div class="media-selector-group">
                                    <input type="text" id="meta_img" name="meta_img"
                                           value="{{ $item->meta_img ? asset($item->meta_img) : '' }}" readonly
                                           placeholder="Select meta image..." onclick="$('#btn_meta').click()">
                                    <button id="btn_meta" class="btn btn-dark btn-choose open-gallery" type="button"
                                            data-input="meta_img" data-preview="preview_meta_img">Choose
                                    </button>
                                </div>
                                <div id="preview_meta_img">@if($item->meta_img)
                                        <div class="media-preview-card"><img src="{{ asset($item->meta_img) }}"
                                                                             width="150"></div>
                                    @endif</div>
                            </div>

                            @foreach(get_active_langs() as $lang)
                                <div class="card bg-light border-0 mb-3">
                                    <div class="card-body">
                                        <h6 class="text-dark fw-bold mb-3 border-bottom pb-2">SEO
                                            ({{ strtoupper($lang) }})</h6>
                                        <div class="row">
                                            <div class="col-md-12 mb-3">
                                                <label class="form-label">Meta Title</label>
                                                <input type="text" name="meta_title_{{ $lang }}" class="form-control"
                                                       value="{{ old('meta_title_'.$lang, $item->{'meta_title_'.$lang}) }}">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Meta Description</label>
                                                <textarea name="meta_description_{{ $lang }}" class="form-control"
                                                          rows="3">{{ old('meta_description_'.$lang, $item->{'meta_description_'.$lang}) }}</textarea>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Meta Keywords</label>
                                                <textarea name="meta_keywords_{{ $lang }}" class="form-control"
                                                          rows="3">{{ old('meta_keywords_'.$lang, $item->{'meta_keywords_'.$lang}) }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="step-content" id="step-5">
                            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
                                <h5 class="text-primary fw-bold mb-0">Step 5: Packages</h5>
                                <button type="button" class="btn btn-success btn-sm shadow-sm" id="add-package"><i
                                        class="fas fa-plus me-1"></i> Add Package
                                </button>
                            </div>
                            <div id="packages-container" class="row">
                                @foreach($item->packages as $index => $package)
                                    <div class="col-md-12 package-item mb-4 bg-white" data-index="{{ $index }}">
                                        <div class="package-header d-flex justify-content-between align-items-center">
                                            <span class="badge bg-primary">Package #{{ $index + 1 }}</span>
                                            <input type="hidden" name="packages[{{ $index }}][id]"
                                                   value="{{ $package->id }}">
                                            <button type="button" class="btn btn-outline-danger btn-sm remove-package">
                                                <i class="fas fa-trash"></i></button>
                                        </div>
                                        <div class="p-4">
                                            <div class="row">
                                                @foreach(get_active_langs() as $lang)
                                                    <div class="{{ colClass() }} mb-3"><label
                                                            class="form-label small text-muted">Title
                                                            ({{ strtoupper($lang) }})</label><input type="text"
                                                                                                    name="packages[{{ $index }}][title_{{ $lang }}]"
                                                                                                    class="form-control"
                                                                                                    value="{{ $package->{'title_'.$lang} }}"
                                                                                                    required></div>
                                                @endforeach
                                                <div class="col-md-3 mb-3"><label class="form-label small text-muted">Price</label><input
                                                        type="number" name="packages[{{ $index }}][price]"
                                                        class="form-control" value="{{ $package->price }}" required>
                                                </div>
                                                <div class="col-md-3 mb-3"><label class="form-label small text-muted">Status</label><select
                                                        name="packages[{{ $index }}][status]" class="form-select">
                                                        <option value="1" {{$package->status==1?'selected':''}}>Active
                                                        </option>
                                                        <option value="0" {{$package->status==0?'selected':''}}>
                                                            Inactive
                                                        </option>
                                                    </select></div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label small text-muted">Attachment</label>
                                                    <div class="media-selector-group">
                                                        <input type="text" id="pkg_att_{{ $index }}"
                                                               name="packages[{{ $index }}][attachment]"
                                                               value="{{ $package->attachment ? asset($package->attachment) : '' }}"
                                                               readonly placeholder="Select..."
                                                               onclick="$('#btn_pkg_{{ $index }}').click()">
                                                        <button id="btn_pkg_{{ $index }}"
                                                                class="btn btn-primary btn-choose open-gallery"
                                                                type="button" data-input="pkg_att_{{ $index }}"
                                                                data-preview="preview_pkg_att_{{ $index }}">Choose
                                                        </button>
                                                    </div>
                                                    <div id="preview_pkg_att_{{ $index }}">@if($package->attachment)
                                                            <div class="media-preview-card"><img
                                                                    src="{{ asset($package->attachment) }}" width="100">
                                                            </div>
                                                        @endif</div>
                                                </div>
                                                <div class="col-12">
                                                    <hr class="my-2">
                                                </div>
                                                @foreach(get_active_langs() as $lang)
                                                    <div class="{{ colClass() }} mb-3"><label
                                                            class="form-label small text-muted">Features
                                                            ({{ strtoupper($lang) }})</label><textarea
                                                            name="packages[{{ $index }}][features_{{ $lang }}]"
                                                            id="pkg_feat_{{ $index }}_{{ $lang }}"
                                                            class="form-control package-summernote">{!! $package->{'features_'.$lang} !!}</textarea>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-5 pt-3 border-top">
                            <button type="button" class="btn btn-secondary px-4" id="prevBtn" style="display:none;"
                                    onclick="nextPrev(-1)"><i class="fas fa-arrow-left me-1"></i> Previous
                            </button>
                            <div class="ms-auto">
                                <button type="button" class="btn btn-primary px-4" id="nextBtn" onclick="nextPrev(1)">
                                    Next <i class="fas fa-arrow-right ms-1"></i></button>
                                <button type="submit" class="btn btn-success px-5" id="submitBtn" style="display:none;">
                                    <i class="fas fa-save"></i> Update Course
                                </button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

    @include('pages.models.gallery-modal')

@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // --- Helper: Slugify ---
            function slugify(text) {
                return text.toString().toLowerCase().replace(/\s+/g, '-').replace(/[^a-zA-Z0-9\u0600-\u06FF\-]/g, '').replace(/\-\-+/g, '-').replace(/^-+/, '').replace(/-+$/, '');
            }

            // --- Init Summernote & Slugs ---
            const activeLangs = @json(get_active_langs());
            activeLangs.forEach(lang => {
                const titleInput = document.querySelector(`input[name="title_${lang}"]`);
                const slugInput = document.querySelector(`input[name="slug_${lang}"]`);
                if (titleInput && slugInput) {
                    titleInput.addEventListener('input', function () {
                        slugInput.value = slugify(this.value);
                    });
                }
                if ($(`#description_${lang}`).length) {
                    $(`#description_${lang}`).summernote({
                        height: 200,
                        lang: lang === 'ar' ? 'ar-AR' : 'en-US',
                        direction: lang === 'ar' ? 'rtl' : 'ltr'
                    });
                }
            });

            // --- Wizard Logic ---
            let currentTab = 0;
            const steps = document.querySelectorAll(".step-content");
            const stepIndicators = document.querySelectorAll(".step-item");

            showTab(currentTab);

            window.nextPrev = function (n) {
                if (n == 1 && !validateForm()) return false;
                currentTab = currentTab + n;
                if (currentTab >= steps.length) return false;
                showTab(currentTab);
                window.scrollTo(0, 0);
            }

            function showTab(n) {
                steps.forEach(s => s.classList.remove('active'));
                steps[n].classList.add('active');

                document.getElementById("prevBtn").style.display = (n == 0) ? "none" : "inline-block";

                if (n == (steps.length - 1)) {
                    document.getElementById("nextBtn").style.display = "none";
                    document.getElementById("submitBtn").style.display = "inline-block";
                } else {
                    document.getElementById("nextBtn").style.display = "inline-block";
                    document.getElementById("submitBtn").style.display = "none";
                }
                updateIndicators(n);
            }

            function validateForm() {
                let valid = true;
                const currentStepDiv = document.getElementsByClassName("step-content")[currentTab];
                const inputs = currentStepDiv.querySelectorAll("input[required], select[required], textarea[required]");

                for (let i = 0; i < inputs.length; i++) {
                    if (inputs[i].value.trim() === "") {
                        inputs[i].classList.add("is-invalid");
                        valid = false;
                    } else {
                        inputs[i].classList.remove("is-invalid");
                    }
                }
                return valid;
            }

            function updateIndicators(n) {
                stepIndicators.forEach((ind, idx) => {
                    ind.classList.remove('active');
                    if (idx < n) ind.classList.add('completed');
                    if (idx === n) {
                        ind.classList.remove('completed');
                        ind.classList.add('active');
                    }
                });
            }

            document.addEventListener('input', function (e) {
                if (e.target.classList.contains('is-invalid')) e.target.classList.remove('is-invalid');
            });

            // --- Gallery Modal Callback Functions ---

            let targetInputId = null;
            let targetPreviewId = null;
            let isMulti = false;

            $(document).on('click', '.open-gallery', function () {
                targetInputId = $(this).data('input');
                targetPreviewId = $(this).data('preview');
                isMulti = $(this).data('multi') || false;

                window.galleryState.inputId = targetInputId;
                window.galleryState.previewId = targetPreviewId;
                window.galleryState.isMulti = isMulti;

                $('#galleryPickerModal').modal('show');
                if (window.loadPicker) window.loadPicker('root');
            });

            window.setSingleFile = function (url, type) {
                $(`#${targetInputId}`).val(url);
                let previewHtml = (type === 'video')
                    ? `<div class="media-preview-card"><video controls width="300" height="180"><source src="${url}"></video><div class="mt-1 text-center small text-muted">Video Selected</div></div>`
                    : `<div class="media-preview-card"><img src="${url}" width="200"></div>`;
                $(`#${targetPreviewId}`).html(previewHtml);
            };

            window.addMultiFile = function (url, type) {
                let inputName = targetInputId + "[]";
                let previewContent = (type === 'video')
                    ? `<video width="100%" height="100%" class="rounded bg-black"><source src="${url}"></video><i class="fas fa-play-circle position-absolute text-white" style="top:50%; left:50%; transform:translate(-50%,-50%);"></i>`
                    : `<img src="${url}" class="w-100 h-100 rounded" style="object-fit:cover">`;

                let itemHtml = `
                    <div class="d-inline-block position-relative shadow-sm border rounded bg-white me-2 mb-2" style="width: 100px; height: 100px;">
                        <input type="hidden" name="${inputName}" value="${url}">
                        ${previewContent}
                        <button type="button" class="remove-btn btn btn-danger btn-sm p-0 d-flex justify-content-center align-items-center" onclick="$(this).parent().remove()">×</button>
                    </div>
                `;
                $(`#${targetPreviewId}`).append(itemHtml);
            };

            // --- Packages Logic ---
            let pkgIdx = {{ $item->packages->count() }}; // Start with existing count
            const columnClass = "{{ colClass() }}";

            // Init Existing Package Summernotes
            $('.package-summernote').summernote({height: 100});

            $('#add-package').on('click', function () {
                let html = `
                <div class="col-md-12 package-item bg-white" data-index="${pkgIdx}">
                    <div class="package-header d-flex justify-content-between align-items-center">
                        <span class="badge bg-primary">Package #${pkgIdx + 1}</span>
                        <button type="button" class="btn btn-outline-danger btn-sm remove-package"><i class="fas fa-trash"></i></button>
                    </div>
                    <div class="p-4">
                        <div class="row">
                            ${activeLangs.map(l => `<div class="${columnClass} mb-3"><label class="form-label small text-muted">Title (${l.toUpperCase()})</label><input type="text" name="packages[${pkgIdx}][title_${l}]" class="form-control" required></div>`).join('')}
                            <div class="col-md-3 mb-3"><label class="form-label small text-muted">Price</label><input type="number" name="packages[${pkgIdx}][price]" class="form-control" required></div>
                            <div class="col-md-3 mb-3"><label class="form-label small text-muted">Status</label><select name="packages[${pkgIdx}][status]" class="form-select"><option value="1">Active</option><option value="0">Inactive</option></select></div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small text-muted">Attachment</label>
                                <div class="media-selector-group">
                                    <input type="text" id="pkg_att_${pkgIdx}" name="packages[${pkgIdx}][attachment]" readonly placeholder="Select..." onclick="$('#btn_pkg_${pkgIdx}').click()">
                                    <button id="btn_pkg_${pkgIdx}" class="btn btn-primary btn-choose open-gallery" type="button" data-input="pkg_att_${pkgIdx}" data-preview="preview_pkg_att_${pkgIdx}">Choose</button>
                                </div>
                                <div id="preview_pkg_att_${pkgIdx}"></div>
                            </div>
                            <div class="col-12"><hr class="my-2"></div>
                            ${activeLangs.map(l => `<div class="${columnClass} mb-3"><label class="form-label small text-muted">Features (${l.toUpperCase()})</label><textarea name="packages[${pkgIdx}][features_${l}]" id="pkg_new_${pkgIdx}_${l}" class="form-control package-summernote"></textarea></div>`).join('')}
                        </div>
                    </div>
                </div>`;

                $('#packages-container').append(html);
                activeLangs.forEach(l => {
                    $(`#pkg_new_${pkgIdx}_${l}`).summernote({height: 100});
                });
                pkgIdx++;
            });

            $(document).on('click', '.remove-package', function () {
                if (confirm("Remove this package?")) $(this).closest('.package-item').remove();
            });

        });
    </script>
@endsection
