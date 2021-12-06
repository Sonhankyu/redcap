<?php
//$connect = mysqli_connect(
//        '127.0.0.1',
//        'pacs',
//        'pacs',
//        'pacsdb',
//            '3306'
//);
//
//if(mysqli_connect_errno()) {
//    echo 'Connect Failed : ' . mysqli_connect_error();
//}
//
//$sql = "SELECT pacsdb.files.filepath
//        FROM pacsdb.files AS files
//        INNER JOIN pacsdb.instance AS inst ON files.instance_fk = inst.pk
//        INNER  JOIN pacsdb.series AS series ON inst.series_fk = series.pk
//        WHERE series.series_iuid";
//
//$result = mysqli_query($connect, $sql);
//
//while ($row = mysqli_fetch_array($result)) {
//    $url_arr[] = '"./files/' . $row[0] . '"';
//}
//$url = implode(", ", $url_arr);

$connect = mysqli_connect(
    '127.0.0.1',
    'root',
    'qwer1234',
    'redcap',
    '3306'
);

if(mysqli_connect_errno()) {
    echo 'Connect Failed : ' . mysqli_connect_error();
}
// series UID 파라미터 받아옴
$url = explode(",", "'" . $_GET['url'] . "'");
$seriesUID = implode("', '", $url);

$sql = "SELECT pacsdb.files.filepath
        FROM pacsdb.files AS files
        INNER JOIN pacsdb.instance AS inst ON files.instance_fk = inst.pk
        INNER  JOIN pacsdb.series AS series ON inst.series_fk = series.pk
        WHERE series.series_iuid in ({$seriesUID})";

$result = mysqli_query($connect, $sql);

while ($row = mysqli_fetch_array($result)) {
    $url_arr[] = '"./files/' . $row[0] . '"';
}
$url = implode(", ", $url_arr);

?>
<!DOCTYPE html>
<html lang="en"
      xmlns="http://www.w3.org/1999/xhtml"
      xmlns:th="http://www.thymeleaf.org">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=0.8" />
    <meta name="theme-color" content="#000000" />
<!--    구글 광고라함-->
<!--    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-9578489609030407"></script>-->
<!--    <script type="text/javascript" src="/js/pagead2.js"></script>-->
    <script type="text/javascript" src="./js/iAidDicomLib.js"></script>
    <script type="text/javascript" src="./js/config.js"></script>
    <title>Smart viewer - lite</title>
    <link href="./css/main.d1200aa8.chunk.css" rel="stylesheet" />
  </head>
  <body class="appScrollbar noselect nodrag">
    <div id="root"></div>
    <div id="popup"></div>
    <script>
      !(function (e) {
        function r(r) {
          for (
            var n, i, l = r[0], f = r[1], a = r[2], c = 0, s = [];
            c < l.length;
            c++
          )
            (i = l[c]),
              Object.prototype.hasOwnProperty.call(o, i) &&
                o[i] &&
                s.push(o[i][0]),
              (o[i] = 0);
          for (n in f)
            Object.prototype.hasOwnProperty.call(f, n) && (e[n] = f[n]);
          for (p && p(r); s.length; ) s.shift()();
          return u.push.apply(u, a || []), t();
        }
        function t() {
          for (var e, r = 0; r < u.length; r++) {
            for (var t = u[r], n = !0, l = 1; l < t.length; l++) {
              var f = t[l];
              0 !== o[f] && (n = !1);
            }
            n && (u.splice(r--, 1), (e = i((i.s = t[0]))));
          }
          return e;
        }
        var n = {},
          o = { 1: 0 },
          u = [];
        function i(r) {
          if (n[r]) return n[r].exports;
          var t = (n[r] = { i: r, l: !1, exports: {} });
          return e[r].call(t.exports, t, t.exports, i), (t.l = !0), t.exports;
        }
        (i.m = e),
          (i.c = n),
          (i.d = function (e, r, t) {
            i.o(e, r) ||
              Object.defineProperty(e, r, { enumerable: !0, get: t });
          }),
          (i.r = function (e) {
            "undefined" != typeof Symbol &&
              Symbol.toStringTag &&
              Object.defineProperty(e, Symbol.toStringTag, { value: "Module" }),
              Object.defineProperty(e, "__esModule", { value: !0 });
          }),
          (i.t = function (e, r) {
            if ((1 & r && (e = i(e)), 8 & r)) return e;
            if (4 & r && "object" == typeof e && e && e.__esModule) return e;
            var t = Object.create(null);
            if (
              (i.r(t),
              Object.defineProperty(t, "default", { enumerable: !0, value: e }),
              2 & r && "string" != typeof e)
            )
              for (var n in e)
                i.d(
                  t,
                  n,
                  function (r) {
                    return e[r];
                  }.bind(null, n)
                );
            return t;
          }),
          (i.n = function (e) {
            var r =
              e && e.__esModule
                ? function () {
                    return e.default;
                  }
                : function () {
                    return e;
                  };
            return i.d(r, "a", r), r;
          }),
          (i.o = function (e, r) {
            return Object.prototype.hasOwnProperty.call(e, r);
          }),
          (i.p = "/");
        var l = (this.webpackJsonpviewer = this.webpackJsonpviewer || []),
          f = l.push.bind(l);
        (l.push = r), (l = l.slice());
        for (var a = 0; a < l.length; a++) r(l[a]);
        var p = f;
        t();
      })([]);
    </script>
    <script src="./js/2.de694815.chunk.js"></script>
    <script src="./js/main.0b46a42f.chunk.js"></script>
  </body>
  <script th:inline="javascript" type="text/javascript">
    var config = { splitSeriesOn: false },
      setupEvent = document.createEvent("CustomEvent");
    setupEvent.initCustomEvent("SETUP_CONFIG", !0, !0, config);

    var urls = [<?=$url?>],

    // for(var i = 40; i<100; i++){
    //   urls.push("/Sample_Image_Korea/16000000" + i + ".dcm");
    // }
    request_get_wado_event = document.createEvent("CustomEvent");
    request_get_wado_event.initCustomEvent("REQUEST_WADO-URI", !0, !0, urls),
      window.dispatchEvent(request_get_wado_event),
      window.dispatchEvent(setupEvent);
  </script>

</html>
