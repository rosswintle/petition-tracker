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
        body {
            margin: 0;
            padding: 0;
            width: 100%;
            font-weight: 100;
            font-size: 24px;
            font-family: 'Lato';
            text-align: center;
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

    </style>
</head>
<body>
    @yield('body')
</body>
</html>