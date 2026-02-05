<div class="page-header">
    <div>
        <h2>Students <span
                style="font-size:16px; color:var(--muted); font-weight:normal; margin-left:8px">({{ count($students ?? []) }})</span>
        </h2>
        <p class="subtitle">Manage & monitor all enrolled students</p>
    </div>
    <a href="{{ route('students.create') }}" class="btn">+ Add Student</a>
</div>

<div class="controls-wrapper">
    @include('students.partials.filters', ['classrooms' => $classrooms, 'sections' => $sections])
</div>

<form id="bulkActionForm" action="{{ route('students.bulk-destroy') }}" method="POST">
    @csrf
    @method('DELETE')

    <div class="table-responsive">
        <table id="datatable">
            <!-- ... existing table headers ... -->
            <thead>
                <tr>
                    <th class="checkbox-col no-sort"><input type="checkbox" id="selectAll" onclick="toggleSelectAll()">
                    </th>
                    <th class="sl-col">SL</th>
                    <th>Name</th>
                    <th>ID</th>
                    <th>Class</th>
                    <th>Section</th>
                    <th class="actions-col no-sort">Actions</th>
                </tr>
            </thead>
            <tbody id="studentTable">
                @foreach ($students as $student)
                    <tr>
                        <td><input type="checkbox" name="selected_ids[]" value="{{ $student->id }}"
                                class="student-checkbox" onclick="updateBulkAction()"></td>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $student->name }}</td>
                        <td>{{ $student->student_id }}</td>
                        <td>{{ $student->classroom->name ?? 'N/A' }}</td>
                        <td>{{ $student->section }}</td>
                        <td class="actions-cell">
                            <button type="button" class="action-btn"
                                onclick="openPayModal({{ $student->id }}, '{{ $student->name }} ({{ $student->calculatedClassName }})', '{{ $student->father_name }}', {{ json_encode($student->calculatedRecurringFees) }}, {{ json_encode($student->discounts ?? []) }}, {{ json_encode($student->calculatedPaidFeeTracker) }}, {{ json_encode($student->calculatedAllClassFees) }}, {{ json_encode($student->partial_payments ?? []) }})"
                                title="Pay Fees">
                                Fees
                            </button>
                            <a href="{{ route('students.show', $student) }}" class="action-btn text-accent"
                                title="View Details">View</a>
                            <a href="{{ route('students.receipt.confirm', $student) }}" class="action-btn text-success"
                                title="View Receipt">Receipt</a>
                            <button type="button" class="action-btn text-danger delete"
                                onclick="confirmDelete('{{ route('students.destroy', $student) }}')"
                                title="Delete Student">
                                Delete
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</form>

<!-- Hidden Delete Form for Single Actions -->
<form id="deleteForm" method="POST" style="display:none">
    @csrf
    @method('DELETE')
</form>

<!-- PAY MODAL -->
@include('students.partials.payment-modal')
