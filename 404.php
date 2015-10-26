<!DOCTYPE html>
<html>
<head>
    <base href="http://bykovvasily.ru/" />
    <title>404 :(</title>

    <link rel="stylesheet" type="text/css" href="css/style.css" />
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />

    <meta charset="utf-8" />
    <!-- js -->
    <script>
        function redirect() {
            var redirectTimeOut = 6,
                vizualIndex = document.getElementById('time_till_redirect');

            setTimeout(function() {
                document.location.href = '/';
            }, redirectTimeOut * 1000);

            var changeVizualIndex = setInterval(function() {
                if (redirectTimeOut == 0 ) {
                    clearInterval(changeVizualIndex);
                    return false;
                }

                vizualIndex.innerHTML = redirectTimeOut - 1;

                redirectTimeOut--;
            }, 1000);
        }
    </script>
</head>
<body onload="redirect()">
    <section class="splash_screen">
        <h1><strong>404</strong><br />This url wrong. Page/photo has moved or deleted.<br />You'll be redirected on start <a href="/">photostream</a>, after <span id="time_till_redirect">5</span> seconds</h1>
    </section>
</body>
</html>