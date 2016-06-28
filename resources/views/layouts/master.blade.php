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
    </style>
</head>
<body>
    @yield('body')

    <footer>
        <div class="container centered">
            <p>An <a href="https://oikos.digital">Oikos Digital</a> Project</p>
            <p>I'm using cookies for Analytics. You need to know.</p>
            <p>Contains public sector information licensed under the <a href="https://www.nationalarchives.gov.uk/doc/open-government-licence/version/3/">Open Government Licence v3.0.</a></p>
        </div>
    </footer>

    <script>
        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
                    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
                m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

        ga('create', 'UA-12614570-20', 'auto');
        ga('send', 'pageview');

    </script>

    @yield('footer-scripts')
</body>
</html>