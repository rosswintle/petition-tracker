<!DOCTYPE html>
<html>
    <head>
        <title>Petition Tracker</title>

        <link href="https://fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">

        <style>
            h1 {
                margin-top: 40px;
            }
            h1 a,
            h1 a:active,
            h1 a:hover,
            h1 a:focus {
                color: #333;
            }
            body {
                margin: 0;
                padding: 0;
                width: 100%;
                font-weight: 100;
                font-family: 'Lato';
            }

        </style>
    </head>
    <body>
        <header>
            <h1>
                <a href="/">
                    Petition Tracker
                </a>
            </h1>
        </header>
        <div class="container">
            {{ form }}
        </div>
    </body>
</html>
