<script type="module" src="{{$app_path_js}}Libraries/alpine/alpine.js"></script>
<script nomodule src="{{$app_path_js}}Libraries/alpine/alpine-ie11.js" defer></script>

<h4 class="title"><i class="fas fa-tasks"></i> {{$lang['queue_system_monitor_page_title']}}</h4>

<div>

<p class="text-wrap">{!!$lang['queue_system_monitor_page_description']!!}</p>
</div>

<div class="wrapper">
    <table class="table table-bordered table-striped">
        <thead>
            <tr class="text-uppercase">
                <th>name</th>
                <th>status</th>
                {{-- <th>data</th> --}}
                <th>created at</th>
                <th>updated at</th>
                <th>event</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($messages as $message)
            <tr>
                <td title="{{@$message['id']}}">{{@$message['key']}}</td>
                <td class="text-center">{!!$printStatus(@$message['status'])!!}</td>
                <td>{{@$message['created_at']}}</td>
                <td>{{@$message['updated_at']}}</td>
                <td class="text-truncate" style="max-width: 150px;"
                title="{{@$message['log_description']}}">{{@$message['log_description']}}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div>
        {{$paginator->render()}}
    </div>
</div>
<style>
    .wrapper {
        max-width: 800px;
    }
    .wrapper table {
        table-layout: fixed;
        width: inherit;
    }
    .wrapper table td,
    .wrapper table th {
        padding: 0.5rem;

    }
    .text-wrap {
        white-space: pre-wrap !important;
    }
</style>