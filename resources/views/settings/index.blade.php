@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Settings</h1>
        <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createSettingModal">
            Create Setting
        </button>
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Value</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                @foreach($settings as $setting)
                    <tr>
                        <td>{{ $setting->name }}</td>
                        <td>{{ $setting->value }}</td>
                        <td>{{ $setting->description }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>


    <!-- Create settings modal -->
    <div class="modal fade" id="createSettingModal" tabindex="-1" aria-labelledby="createSettingModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createSettingModalLabel">Create Setting</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('settings.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <select class="form-control" id="name" name="name" required>
                                <option value="">Select a setting</option>
                                @foreach($availableSettings as $key => $description)
                                    <option value="{{ $key }}">{{ $key }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" readonly></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="value" class="form-label">Value</label>
                            <input type="text" class="form-control" id="value" name="value">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


<script>
    document.getElementById('name').addEventListener('change', function() {
        const selectedSetting = this.value;
        const descriptionField = document.getElementById('description');
        const availableSettings = @json($availableSettings);

        if (selectedSetting && availableSettings[selectedSetting]) {
            descriptionField.value = availableSettings[selectedSetting];
        } else {
            descriptionField.value = '';
        }
    });
</script>
@endsection


