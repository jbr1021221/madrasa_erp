<div class="filters-container">
    <!-- Filters Form -->
    <form method="GET" action="{{ route('students.index') }}" class="filter-form">
        <select name="class_id" onchange="this.form.submit()" class="filter-select">
            <option value="">All Classes</option>
            @foreach ($classrooms as $classroom)
                <option value="{{ $classroom->id }}" {{ request('class_id') == $classroom->id ? 'selected' : '' }}>
                    {{ $classroom->name }}
                </option>
            @endforeach
        </select>

        <select name="section" onchange="this.form.submit()" class="filter-select">
            <option value="">All Sections</option>
            @foreach ($sections as $section)
                <option value="{{ $section }}" {{ request('section') == $section ? 'selected' : '' }}>
                    Section {{ $section }}
                </option>
            @endforeach
        </select>

        <input type="date" name="start_date" value="{{ request('start_date') }}" class="filter-input date-input"
            title="Start Date">
        <input type="date" name="end_date" value="{{ request('end_date') }}" class="filter-input date-input"
            title="End Date">

        <input type="text" name="search" placeholder="Search by Name or ID" value="{{ request('search') }}"
            class="filter-input search-input">

        <button type="submit" class="btn ghost filter-btn">Filter</button>
        @if (request()->hasAny(['class_id', 'section', 'search', 'start_date', 'end_date']))
            <a href="{{ route('students.index') }}" class="btn ghost filter-btn">Clear</a>
        @endif
    </form>

    <!-- Bulk Actions Dropdown -->
    <select id="bulkActionDropdown" onchange="handleBulkAction(this.value); this.value='';"
        class="filter-select bulk-select">
        <option value="">Bulk Actions</option>
        <option value="delete">Delete Selected</option>
    </select>
</div>
