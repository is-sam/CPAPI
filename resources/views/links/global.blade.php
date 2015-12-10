<!DOCTYPE html>
<html>
<head>
    <title>Laravel</title>
    <title>Link shortner</title>
    <link rel="stylesheet" href="https://bootswatch.com/lumen/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">

    <style>
        html, body {
            height: 100%;
        }

        body {
            margin: 0;
            padding: 0;
            width: 100%;
            display: table;
            font-weight: 100;
            font-family: 'Lato';
        }

        .container {
            text-align: center;
            display: table-cell;
            vertical-align: middle;
        }

        .content {
            text-align: center;
            display: inline-block;
            color: black;
        }

        .title {
            font-size: 45px;
        }
        input {
            color: black !important;
        }
    </style>
</head>
<body>
<header>

</header>
<div class="container">
    <div class="content">
        @yield('content')
    </div>
</div>

<footer>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.4/jquery.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
</footer>
</body>
</html>
