@php
$objHtmlPage = new HtmlPage();
@endphp
<script src="{{$app_path_js}}vue.min.js"></script>
{{loadJS('fhir-stats/dist/fhir_stats_vue.umd.min.js')}}
<link rel="stylesheet" href="{{$objHtmlPage->CacheBuster($app_path_js.'fhir-stats/dist/fhir_stats_vue.css')}}">

<div id="fhir-stats-app"></div>
<script>
	(function(Vue) {
		window.addEventListener('DOMContentLoaded', function(event) {
			new Vue(fhir_stats_vue).$mount('#fhir-stats-app')
		})
	})(Vue)
</script>