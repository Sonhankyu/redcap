<p>{{$lang['global_21']}}</p>
<p>The Adjudication process is completed for project <strong>{{$project_id}}</strong></p>
<table border='1' style='border-collapse:collapse'>
  <thead>
    <tr>
    @foreach(array_keys($data) as $key)
      <th style="text-transform: uppercase">{{$key}}</th>
    @endforeach
    </tr>
  </thead>
  <tbody>
    <tr>
      @foreach($data as $key => $value)
      <td><span title="{{$key}}">{{$value}}</span></td>
      @endforeach
    </tr>
  </tbody>
</table>
<ul>
@if(count($errors)>0)
<h3>Errors</h3>
@foreach ($errors as $record_id => $error_list)
  @foreach ($error_list as $rerror_message)
  <li>{{$record_id}}: {{$rerror_message}}</li>
  @endforeach
@endforeach
@endif
</ul>