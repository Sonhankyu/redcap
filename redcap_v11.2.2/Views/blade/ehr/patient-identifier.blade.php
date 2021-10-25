<div class="container-fluid">
    <div class="col-12 text-secondary">
        <p>
            <button class="btn btn-secondary btn-xs" type="button" data-toggle="collapse" data-target="#ehr-identifier-container" aria-expanded="false" aria-controls="ehr-identifier-container">
                View EHR patient identifier keys
            </button>
        </p>
        <div class="collapse" id="ehr-identifier-container">
            <div class="card card-body">
                @foreach($identifiers as $identifier)
                @php
                $value = @$identifier['value'];
                $system = @$identifier['system'];
                if(empty($value) || empty($system)) continue;
                @endphp
                <span style="display: block;">
                    <span>•</span>
                    <span>{{ $value }}, </span>
                    <span>{{ $system }}</span>
                </span>
                @endforeach
            </div>
        </div>
    </div>
</div>