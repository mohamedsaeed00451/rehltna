<div class="table-responsive">
    <table class="table" id="table">
        <thead>
        <tr>
            <th class="text-center" width="60">
                <input id="loopId" type="checkbox" class="form-check-input" onclick="toggle(this)">
            </th>
            <th>Name & Details</th>
            <th>Contact</th>
            <th class="text-center">Trips</th>
            <th class="text-center">Orders</th>
            <th class="text-center px-4">Actions</th>
        </tr>
        </thead>
        <tbody>
        @foreach($residencyUsers as $residencyUser)
            <tr>
                <td class="text-center fw-bold">
                    <div class="d-flex flex-column align-items-center">
                        <input type="checkbox" name="ids[]" value="{{ $residencyUser->id }}" class="form-check-input mb-1">
                    </div>
                </td>
                <td>
                    <h6 class="mb-0 fw-bold text-dark">{{ $residencyUser->name }}</h6>
                    <span class="text-muted small fw-medium">{{ $residencyUser->email }}</span>
                </td>
                <td>
                    <span class="fw-bold" style="color: #4f46e5;"><i class="las la-phone me-1"></i>{{ $residencyUser->phone }}</span>
                </td>
                <td class="text-center">
                    <span class="count-badge" style="background: #f0fdf4; color: #10b981; border-color: #10b98130;">
                        {{ $residencyUser->items_count ?? 0 }}
                    </span>
                </td>
                <td class="text-center">
                    <span class="count-badge">
                        {{ $residencyUser->orders_count ?? 0 }}
                    </span>
                </td>
                <td class="text-center px-4">
                    <div class="d-flex justify-content-center">
                        <a class="btn-action btn-delete-action delete-btn shadow-sm"
                           data-route="{{ route('residency-users.destroy',$residencyUser->id) }}"
                           data-bs-toggle="modal" href="#" data-bs-target="#deleteModal">
                            <i class="las la-trash fs-18"></i>
                        </a>
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
