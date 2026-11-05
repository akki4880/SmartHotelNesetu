<div class="row">
    @foreach($data['fields'] ?? [] as $field)
    <div class="col-md-6 mb-2">
        <strong class="text-danger">{{ $field['label'] }}:</strong><br>
        <span>{{ $field['value'] ?? '-' }}</span>
    </div>
    @endforeach
</div>

@if(!empty($data['table']))
<hr>
<table class="table table-bordered table-sm">
    <thead class="bg-primary text-white">
        <tr>
            @foreach($data['table']['headers'] as $header)
            <th>{{ $header }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($data['table']['rows'] as $row)
        <tr>
            @foreach($row as $cell)
            <td>{{ $cell }}</td>
            @endforeach
        </tr>
        @endforeach
    </tbody>
</table>
@endif