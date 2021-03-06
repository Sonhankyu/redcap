@php
$objHtmlPage = new HtmlPage();
@endphp
<noscript>
    <strong>We're sorry but Mapping Helper doesn't work properly without JavaScript enabled. Please enable it to continue.</strong>
</noscript>

 @if(!$browser_supported)
<h3>
    <i class="fas fa-exclamation-triangle"></i>
    <span>This feature is not available for your browser.</span>
</h3>

@else
<h3>{{$lang['mapping_helper_01']}}</h3>

<div style="max-width:800px;">
    <div id="mapping-helper"></div>
</div>

<script src="{{$app_path_js}}vue.min.js"></script>
{{loadJS('mapping-helper/dist/mapping_helper_vue.umd.min.js')}}
<link rel="stylesheet" href="{{$objHtmlPage->CacheBuster($app_path_js.'mapping-helper/dist/mapping_helper_vue.css')}}">

<script>
(function(window,document,Vue) {
    window.addEventListener('DOMContentLoaded', function (event) {
        var mapping_helper_instance = new Vue(mapping_helper_vue).$mount('#mapping-helper')
    })
}(window,document,Vue))
</script>
@endif
