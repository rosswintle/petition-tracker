<!DOCTYPE html>
<html>
<head>
    <title>Petition Tracker - @yield('title')</title>

    <link href="https://fonts.googleapis.com/css?family=Lato:100,400" rel="stylesheet" type="text/css">

    <style>
        h1 {
            text-align: center;
            margin-top: 40px;
            margin-bottom: 80px;
            font-size: 48px;
        }
        h1 a,
        h1 a:active,
        h1 a:hover,
        h1 a:focus {
            color: #333;
            text-decoration: none;
        }
        h2 {
            text-align: center;
            margin-top: 40px;
            margin-bottom: 34px;
            font-size: 34px;
        }
        body {
            margin: 0;
            padding: 0;
            width: 100%;
            font-weight: 100;
            font-size: 24px;
            font-family: 'Lato';
        }
        .container {
            width: 100%;
            padding: 0 20px;
            max-width: 700px;
            margin: 0 auto;
        }
        .centered {
            text-align: center;
        }
        a {
            text-decoration: none;
        }
        form, input {
            font-size: 24px;
        }
        input[type=submit] {
            font-size: 24px;
            font-weight: 100;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-color: transparent;
            box-shadow: none;
            border: 1px solid #888;
            padding: 8px 18px;
        }
        table {
            margin: 0 auto;
        }
        td {
            text-align: center;
            padding: 4px 8px;
        }
        footer {
            margin-top: 30px;
            background-color: #D9D9D9;
        }
        footer .container {
            width: 100%;
            max-width: 700px;
            padding: 30px 0;
        }
        footer a {
            background-color: #CCC;
            transition: background-color 0.5s, color 0.5s;
            display: inline-block;
            padding: 0.1rem 0.5rem;
        }
        footer a:active,
        footer a:hover,
        footer a:focus {
            background-color: #888;
            color: #DDD;
        }
    </style>
</head>
<body>
    <div style="background-color: indianred; padding: 1rem;">
        Hi! My little petition tracker got some heavy traffic this morning and is temporarily broken. I'm working
        to fix it! Hope to be back soon.<br>
        Thanks for your patience. If you like this tool then send me a tweet <a href="https://twitter.com/magicroundabout">@magicroundabout</a> of support!
    </div>
    @yield('body')

    <footer>
        <div class="container centered">
            <p>An <a href="https://oikos.digital/">Oikos Digital</a> Project</p>
            <p>This site uses <a href="https://blog.kownter.com/about-kownter/">Kownter</a> for analytics.
                This does not use cookies and does not collect any personal data.
                <a href="https://blog.kownter.com/about-kownter/">Find out more</a>.</p>
            <p>Contains public sector information licensed under the <a href="https://www.nationalarchives.gov.uk/doc/open-government-licence/version/3/">Open Government Licence v3.0.</a></p>
        </div>
    </footer>

    @if ( env('KOWNTER_APP_URL') )
        <script>
            function kownterHttpGetAsync(theUrl, callback)
            {
                var xmlHttp = new XMLHttpRequest();
                xmlHttp.onreadystatechange = function() {
                    if (xmlHttp.readyState == 4 && xmlHttp.status == 200)
                        callback(xmlHttp.responseText);
                }
                xmlHttp.open("GET", theUrl, true); // true for asynchronous
                xmlHttp.send(null);
            }
            kownterHttpGetAsync( '{{ env('KOWNTER_APP_URL') }}/track?referrer=' + encodeURIComponent( document.referrer ), function() { return true; } );
        </script>
    @endif

    @yield('footer-scripts')
</body>
</html>